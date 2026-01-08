<?php

namespace App\Http\Controllers;

use App\Models\Chatbot;
use App\Models\ChatbotAgent;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use App\Traits\ModelAction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LiveAgentController extends Controller
{
    use ModelAction;

    protected $user;
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;

        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    /**
     * Live agent dashboard
     *
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request): View
    {
        $chatbotId = $request->input('chatbot_id');

        $chatbotsQuery = Chatbot::where('user_id', $this->user->id);

        if ($chatbotId) {
            $chatbotsQuery->where('id', $chatbotId);
        }

        $chatbots = $chatbotsQuery->get();
        $chatbotIds = $chatbots->pluck('id')->toArray();

        $agent = ChatbotAgent::whereIn('chatbot_id', $chatbotIds)
            ->where('user_id', $this->user->id)
            ->first();

        $activeConversations = ChatbotConversation::whereIn('chatbot_id', $chatbotIds)
            ->whereIn('status', ['human_requested', 'human_active'])
            ->with(['chatbot', 'assignedAgent'])
            ->withCount('messages')
            ->latest('last_message_at')
            ->paginate(20)
            ->appends(request()->all());

        $stats = [
            'pending' => ChatbotConversation::whereIn('chatbot_id', $chatbotIds)
                ->where('status', 'human_requested')
                ->count(),
            'active' => ChatbotConversation::whereIn('chatbot_id', $chatbotIds)
                ->where('status', 'human_active')
                ->count(),
            'resolved_today' => ChatbotConversation::whereIn('chatbot_id', $chatbotIds)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
        ];

        return view('user.chatbot.live-agent', [
            'meta_data' => $this->metaData(['title' => translate("Live Agent Dashboard")]),
            'conversations' => $activeConversations,
            'chatbots' => $chatbots,
            'selectedChatbot' => $chatbotId,
            'agent' => $agent,
            'stats' => $stats,
        ]);
    }

    /**
     * Get conversation details and messages (AJAX)
     *
     * @param string $uid
     * @return JsonResponse
     */
    public function getConversation(string $uid): JsonResponse
    {
        try {
            $conversation = ChatbotConversation::where('uid', $uid)
                ->whereHas('chatbot', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->with(['chatbot', 'assignedAgent'])
                ->firstOrFail();

            $messages = ChatbotMessage::where('conversation_id', $conversation->id)
                ->with('agent')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->uid,
                        'message' => $message->message,
                        'sender_type' => $message->sender_type,
                        'message_type' => $message->message_type,
                        'is_internal_note' => $message->is_internal_note,
                        'agent_name' => $message->agent ? $message->agent->name : null,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                        'formatted_time' => $message->created_at->diffForHumans(),
                    ];
                });

            return response()->json([
                'success' => true,
                'conversation' => [
                    'uid' => $conversation->uid,
                    'visitor_name' => $conversation->visitor_name ?? 'Anonymous',
                    'visitor_email' => $conversation->visitor_email,
                    'status' => $conversation->status,
                    'chatbot_name' => $conversation->chatbot->name,
                    'assigned_agent' => $conversation->assignedAgent ? $conversation->assignedAgent->name : null,
                    'last_message_at' => $conversation->last_message_at?->diffForHumans(),
                ],
                'messages' => $messages,
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::getConversation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }
    }

    /**
     * Claim/take over a conversation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function claimConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string|exists:chatbot_conversations,uid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = ChatbotConversation::where('uid', $request->input('conversation_id'))
                ->whereHas('chatbot', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->firstOrFail();

            if ($conversation->status === 'human_active' && $conversation->assigned_agent_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This conversation is already being handled by another agent.',
                ], 409);
            }

            $agent = ChatbotAgent::where('chatbot_id', $conversation->chatbot_id)
                ->where('user_id', $this->user->id)
                ->firstOrFail();

            $this->chatbotService->assignToAgent($conversation, $agent);

            if ($agent->status !== 'online') {
                $agent->setOnline();
            }

            return response()->json([
                'success' => true,
                'message' => 'Conversation claimed successfully.',
                'conversation_status' => 'human_active',
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::claimConversation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to claim conversation.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Send message as agent
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string|exists:chatbot_conversations,uid',
            'message' => 'required|string|max:5000',
            'is_internal_note' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = ChatbotConversation::where('uid', $request->input('conversation_id'))
                ->whereHas('chatbot', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->with('chatbot')
                ->firstOrFail();

            $agent = ChatbotAgent::where('chatbot_id', $conversation->chatbot_id)
                ->where('user_id', $this->user->id)
                ->firstOrFail();

            if ($conversation->status !== 'human_active') {
                $this->chatbotService->assignToAgent($conversation, $agent);
            }

            $message = $this->chatbotService->sendAgentMessage(
                $conversation,
                $agent,
                $request->input('message'),
                $request->boolean('is_internal_note', false)
            );

            $agent->updateActivity();

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->uid,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'is_internal_note' => $message->is_internal_note,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::sendMessage error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Resolve conversation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resolveConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string|exists:chatbot_conversations,uid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = ChatbotConversation::where('uid', $request->input('conversation_id'))
                ->whereHas('chatbot', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->firstOrFail();

            $conversation->resolve();

            return response()->json([
                'success' => true,
                'message' => 'Conversation resolved successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::resolveConversation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conversation.',
            ], 500);
        }
    }

    /**
     * Resume AI for conversation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resumeAI(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string|exists:chatbot_conversations,uid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = ChatbotConversation::where('uid', $request->input('conversation_id'))
                ->whereHas('chatbot', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->firstOrFail();

            $conversation->resumeAI();

            return response()->json([
                'success' => true,
                'message' => 'AI resumed for this conversation.',
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::resumeAI error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resume AI.',
            ], 500);
        }
    }

    /**
     * Set agent status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chatbot_id' => 'required|integer|exists:chatbots,id',
            'status' => 'required|string|in:online,offline,away,busy',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $chatbot = Chatbot::where('id', $request->input('chatbot_id'))
                ->where('user_id', $this->user->id)
                ->firstOrFail();

            $agent = ChatbotAgent::where('chatbot_id', $chatbot->id)
                ->where('user_id', $this->user->id)
                ->firstOrFail();

            $status = $request->input('status');

            match ($status) {
                'online' => $agent->setOnline(),
                'offline' => $agent->setOffline(),
                'away' => $agent->setAway(),
                'busy' => $agent->setBusy(),
                default => null,
            };

            return response()->json([
                'success' => true,
                'message' => "Status updated to {$status}.",
                'status' => $status,
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::setStatus error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }

    /**
     * Get new messages for polling (AJAX)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pollMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string|exists:chatbot_conversations,uid',
            'last_message_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = ChatbotConversation::where('uid', $request->input('conversation_id'))
                ->whereHas('chatbot', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->firstOrFail();

            $query = ChatbotMessage::where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'asc');

            if ($request->input('last_message_id')) {
                $lastMessage = ChatbotMessage::where('uid', $request->input('last_message_id'))->first();
                if ($lastMessage) {
                    $query->where('created_at', '>', $lastMessage->created_at);
                }
            }

            $newMessages = $query->with('agent')->get()->map(function ($message) {
                return [
                    'id' => $message->uid,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'is_internal_note' => $message->is_internal_note,
                    'agent_name' => $message->agent ? $message->agent->name : null,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'formatted_time' => $message->created_at->diffForHumans(),
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $newMessages,
                'conversation_status' => $conversation->status,
            ]);
        } catch (Exception $e) {
            Log::error('LiveAgentController::pollMessages error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages.',
            ], 500);
        }
    }

    /**
     * Get pending conversations count for notifications
     *
     * @return JsonResponse
     */
    public function getPendingCount(): JsonResponse
    {
        try {
            $chatbots = Chatbot::where('user_id', $this->user->id)->pluck('id');

            $pendingCount = ChatbotConversation::whereIn('chatbot_id', $chatbots)
                ->where('status', 'human_requested')
                ->count();

            return response()->json([
                'success' => true,
                'pending_count' => $pendingCount,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pending count.',
            ], 500);
        }
    }
}
