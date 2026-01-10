<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Chatbot;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\ChatbotUsageLog;
use App\Models\User;
use App\Traits\ModelAction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;
use Closure;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    use ModelAction;

    /**
     * Constructor with permissions middleware
     *
     * @return void
     */
    public function __construct()
    {
        // Check permissions middleware
        $this->middleware(['permissions:view_content'])->only(['statistics', 'list', 'show', 'conversations', 'conversationDetails', 'analytics']);
        $this->middleware(['permissions:update_content'])->only(['updateStatus', 'bulk']);
    }

    /**
     * Chatbot statistics dashboard
     *
     * @return View
     */
    public function statistics(): View
    {
        $statistics = [
            'total_chatbots' => Chatbot::count(),
            'active_chatbots' => Chatbot::where('status', StatusEnum::true->status())->count(),
            'total_conversations' => ChatbotConversation::count(),
            'active_conversations' => ChatbotConversation::whereIn('status', ['ai_active', 'human_active'])->count(),
            'total_messages' => ChatbotMessage::count(),
            'total_users_with_chatbots' => Chatbot::distinct('user_id')->count('user_id'),
        ];

        // Get recent activity
        $recent_chatbots = Chatbot::with('user')
            ->latest()
            ->take(10)
            ->get();

        $recent_conversations = ChatbotConversation::with(['chatbot', 'chatbot.user'])
            ->latest()
            ->take(10)
            ->get();

        // Get chatbot usage by provider
        $usage_by_provider = Chatbot::select('ai_provider', DB::raw('count(*) as count'))
            ->groupBy('ai_provider')
            ->get();

        // Get conversations by status
        $conversations_by_status = ChatbotConversation::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $title = translate('AI Chatbot Statistics');

        return view('admin.chatbot.statistics', compact(
            'title',
            'statistics',
            'recent_chatbots',
            'recent_conversations',
            'usage_by_provider',
            'conversations_by_status'
        ));
    }

    /**
     * List all chatbots
     *
     * @param Request $request
     * @return View
     */
    public function list(Request $request): View
    {
        $title = translate('All AI Chatbots');

        $chatbots = Chatbot::with('user')
            ->filter($request)
            ->date($request)
            ->latest()
            ->paginate(paginateNumber())
            ->appends($request->all());

        return view('admin.chatbot.list', compact('title', 'chatbots'));
    }

    /**
     * Show chatbot details
     *
     * @param string $uid
     * @return View
     */
    public function show(string $uid): View
    {
        $chatbot = Chatbot::with(['user', 'trainingData', 'conversations', 'agents'])
            ->where('uid', $uid)
            ->firstOrFail();

        $title = translate('Chatbot Details') . ' - ' . $chatbot->name;

        // Get statistics for this chatbot
        $statistics = [
            'total_conversations' => $chatbot->conversations()->count(),
            'active_conversations' => $chatbot->conversations()->whereIn('status', ['ai_active', 'human_active'])->count(),
            'total_messages' => ChatbotMessage::whereHas('conversation', function ($query) use ($chatbot) {
                $query->where('chatbot_id', $chatbot->id);
            })->count(),
            'total_training_data' => $chatbot->trainingData()->count(),
            'total_agents' => $chatbot->agents()->count(),
        ];

        // Get recent conversations
        $recent_conversations = $chatbot->conversations()
            ->with('assignedAgent')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.chatbot.show', compact('title', 'chatbot', 'statistics', 'recent_conversations'));
    }

    /**
     * List all conversations
     *
     * @param Request $request
     * @return View
     */
    public function conversations(Request $request): View
    {
        $title = translate('All Chatbot Conversations');

        $conversations = ChatbotConversation::with(['chatbot', 'chatbot.user', 'assignedAgent'])
            ->filter($request)
            ->date($request)
            ->latest()
            ->paginate(paginateNumber())
            ->appends($request->all());

        // Get filter options
        $chatbots = Chatbot::select('id', 'name', 'uid')->get();
        $statuses = ['ai_active', 'human_requested', 'human_active', 'resolved', 'closed'];

        return view('admin.chatbot.conversations', compact('title', 'conversations', 'chatbots', 'statuses'));
    }

    /**
     * Show conversation details
     *
     * @param string $uid
     * @return View
     */
    public function conversationDetails(string $uid): View
    {
        $conversation = ChatbotConversation::with(['chatbot', 'chatbot.user', 'assignedAgent', 'messages', 'messages.agent'])
            ->where('uid', $uid)
            ->firstOrFail();

        $title = translate('Conversation Details') . ' - ' . $conversation->chatbot->name;

        return view('admin.chatbot.conversation_details', compact('title', 'conversation'));
    }

    /**
     * Chatbot analytics
     *
     * @param Request $request
     * @return View
     */
    public function analytics(Request $request): View
    {
        $title = translate('AI Chatbot Analytics');

        // Get date range (default to last 30 days)
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Messages per day
        $messages_per_day = ChatbotMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Conversations per day
        $conversations_per_day = ChatbotConversation::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performing chatbots
        $top_chatbots = Chatbot::withCount(['conversations' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
            ->orderBy('conversations_count', 'desc')
            ->take(10)
            ->get();

        // Human takeover statistics
        $takeover_stats = [
            'total_takeovers' => ChatbotConversation::whereNotNull('taken_over_at')
                ->whereBetween('taken_over_at', [$startDate, $endDate])
                ->count(),
            'avg_takeover_time' => ChatbotConversation::whereNotNull('taken_over_at')
                ->whereBetween('taken_over_at', [$startDate, $endDate])
                ->avg(DB::raw('TIMESTAMPDIFF(SECOND, created_at, taken_over_at)')),
        ];

        return view('admin.chatbot.analytics', compact(
            'title',
            'messages_per_day',
            'conversations_per_day',
            'top_chatbots',
            'takeover_stats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Update chatbot status
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|exists:chatbots,id',
            'status' => 'required|in:0,1',
        ]);

        $chatbot = Chatbot::findOrFail($request->id);
        $chatbot->update([
            'status' => $request->status
        ]);

        $message = translate('Chatbot status updated successfully');

        return back()->with('success', $message);
    }

    /**
     * Bulk action on chatbots
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulk(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:chatbots,id',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        $chatbots = Chatbot::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'activate':
                $chatbots->update(['status' => StatusEnum::true->status()]);
                $message = translate('Chatbots activated successfully');
                break;
            case 'deactivate':
                $chatbots->update(['status' => StatusEnum::false->status()]);
                $message = translate('Chatbots deactivated successfully');
                break;
            case 'delete':
                $chatbots->delete();
                $message = translate('Chatbots deleted successfully');
                break;
        }

        return back()->with('success', $message);
    }
}
