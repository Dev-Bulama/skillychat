<?php

namespace App\Http\Controllers;

use App\Models\Chatbot;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Send a message to the chatbot
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chatbot_id' => 'required|string|exists:chatbots,uid',
            'visitor_id' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'visitor_name' => 'nullable|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'page_url' => 'nullable|url|max:500',
            'referrer' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $chatbot = Chatbot::where('uid', $request->input('chatbot_id'))
                ->active()
                ->firstOrFail();

            $visitorData = [
                'name' => $request->input('visitor_name'),
                'email' => $request->input('visitor_email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'page_url' => $request->input('page_url'),
                'referrer' => $request->input('referrer'),
            ];

            $conversation = $this->chatbotService->getOrCreateConversation(
                $chatbot,
                $request->input('visitor_id'),
                $visitorData
            );

            $message = $this->chatbotService->processMessage(
                $chatbot,
                $conversation,
                $request->input('message')
            );

            $aiResponse = ChatbotMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'ai')
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->uid,
                'message' => $aiResponse ? [
                    'id' => $aiResponse->uid,
                    'message' => $aiResponse->message,
                    'sender_type' => $aiResponse->sender_type,
                    'created_at' => $aiResponse->created_at->toIso8601String(),
                ] : null,
                'status' => $conversation->status,
                'human_takeover' => $conversation->status === 'human_requested' || $conversation->status === 'human_active',
            ]);
        } catch (Exception $e) {
            Log::error('ChatController::sendMessage error: ' . $e->getMessage(), [
                'chatbot_id' => $request->input('chatbot_id'),
                'visitor_id' => $request->input('visitor_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process message. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get conversation messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chatbot_id' => 'required|string|exists:chatbots,uid',
            'visitor_id' => 'required|string|max:255',
            'last_message_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $chatbot = Chatbot::where('uid', $request->input('chatbot_id'))
                ->active()
                ->firstOrFail();

            $conversation = ChatbotConversation::where('chatbot_id', $chatbot->id)
                ->where('visitor_id', $request->input('visitor_id'))
                ->latest('last_message_at')
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => true,
                    'messages' => [],
                    'conversation_id' => null,
                ]);
            }

            $query = ChatbotMessage::where('conversation_id', $conversation->id)
                ->where('is_internal_note', false)
                ->orderBy('created_at', 'asc');

            if ($request->input('last_message_id')) {
                $lastMessage = ChatbotMessage::where('uid', $request->input('last_message_id'))->first();
                if ($lastMessage) {
                    $query->where('created_at', '>', $lastMessage->created_at);
                }
            }

            $messages = $query->get()->map(function ($message) {
                return [
                    'id' => $message->uid,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'message_type' => $message->message_type,
                    'file_path' => $message->file_path ? Storage::url($message->file_path) : null,
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->uid,
                'messages' => $messages,
                'status' => $conversation->status,
                'human_takeover' => $conversation->status === 'human_requested' || $conversation->status === 'human_active',
            ]);
        } catch (Exception $e) {
            Log::error('ChatController::getMessages error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Upload image to chatbot
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chatbot_id' => 'required|string|exists:chatbots,uid',
            'visitor_id' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'prompt' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $chatbot = Chatbot::where('uid', $request->input('chatbot_id'))
                ->active()
                ->firstOrFail();

            if (!$chatbot->canHandleImages()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image support is not enabled for this chatbot.',
                ], 403);
            }

            $conversation = $this->chatbotService->getOrCreateConversation(
                $chatbot,
                $request->input('visitor_id'),
                ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]
            );

            $message = $this->chatbotService->processImageMessage(
                $chatbot,
                $conversation,
                $request->file('image'),
                $request->input('prompt', 'What is in this image?')
            );

            $aiResponse = ChatbotMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'ai')
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->uid,
                'message' => $aiResponse ? [
                    'id' => $aiResponse->uid,
                    'message' => $aiResponse->message,
                    'sender_type' => $aiResponse->sender_type,
                    'created_at' => $aiResponse->created_at->toIso8601String(),
                ] : null,
            ]);
        } catch (Exception $e) {
            Log::error('ChatController::uploadImage error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process image.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Rate conversation satisfaction
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rateConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string|exists:chatbot_conversations,uid',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = ChatbotConversation::where('uid', $request->input('conversation_id'))
                ->firstOrFail();

            $conversation->rateSatisfaction($request->input('rating'));

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
            ]);
        } catch (Exception $e) {
            Log::error('ChatController::rateConversation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save rating.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get chatbot configuration
     *
     * @param string $chatbotId
     * @return JsonResponse
     */
    public function getConfig(string $chatbotId): JsonResponse
    {
        try {
            $chatbot = Chatbot::where('uid', $chatbotId)
                ->active()
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'config' => [
                    'name' => $chatbot->name,
                    'welcome_message' => $chatbot->welcome_message,
                    'offline_message' => $chatbot->offline_message,
                    'primary_color' => $chatbot->primary_color,
                    'widget_position' => $chatbot->widget_position,
                    'emoji_support' => $chatbot->emoji_support,
                    'voice_support' => $chatbot->voice_support,
                    'image_support' => $chatbot->image_support,
                    'language' => $chatbot->language,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chatbot not found or inactive.',
            ], 404);
        }
    }

    /**
     * Request human takeover
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requestHuman(Request $request): JsonResponse
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
                ->firstOrFail();

            if (!$conversation->chatbot->canUseHumanTakeover()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Human takeover is not enabled for this chatbot.',
                ], 403);
            }

            $conversation->requestHumanTakeover();

            return response()->json([
                'success' => true,
                'message' => 'A human agent will be with you shortly.',
                'status' => 'human_requested',
            ]);
        } catch (Exception $e) {
            Log::error('ChatController::requestHuman error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to request human agent.',
            ], 500);
        }
    }
}
