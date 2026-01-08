<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Chatbot;
use App\Models\ChatbotAgent;
use App\Models\ChatbotConversation;
use App\Models\ChatbotTrainingData;
use App\Models\ChatbotUsageLog;
use App\Services\ChatbotService;
use App\Traits\ModelAction;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ChatbotController extends Controller
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
     * List user's chatbots
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $chatbots = Chatbot::where('user_id', $this->user->id)
            ->with(['trainingData', 'conversations', 'agents'])
            ->withCount(['conversations', 'trainingData'])
            ->latest()
            ->paginate(paginateNumber())
            ->appends(request()->all());

        return view('user.chatbot.index', [
            'meta_data' => $this->metaData(['title' => translate("My Chatbots")]),
            'chatbots' => $chatbots,
        ]);
    }

    /**
     * Show create chatbot form
     *
     * @return View
     */
    public function create(): View
    {
        return view('user.chatbot.create', [
            'meta_data' => $this->metaData(['title' => translate("Create Chatbot")]),
        ]);
    }

    /**
     * Store new chatbot
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'domain' => 'nullable|url|max:255',
            'language' => 'nullable|string|max:10',
            'tone' => 'nullable|string|in:professional,friendly,casual,formal',
            'welcome_message' => 'nullable|string|max:500',
            'offline_message' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:20',
            'widget_position' => 'nullable|string|in:bottom-right,bottom-left,top-right,top-left',
            'emoji_support' => 'nullable|boolean',
            'voice_support' => 'nullable|boolean',
            'image_support' => 'nullable|boolean',
            'human_takeover_enabled' => 'nullable|boolean',
            'ai_provider' => 'nullable|string|in:openai,gemini,claude',
            'ai_confidence_threshold' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $chatbot = $this->chatbotService->createChatbot($this->user, $request->all());

            return redirect()->route('user.chatbot.index')
                ->with(response_status('Chatbot created successfully'));
        } catch (Exception $e) {
            return back()->with(response_status($e->getMessage(), 'error'))->withInput();
        }
    }

    /**
     * Show edit chatbot form
     *
     * @param string $uid
     * @return View
     */
    public function edit(string $uid): View
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        return view('user.chatbot.edit', [
            'meta_data' => $this->metaData(['title' => translate("Edit Chatbot")]),
            'chatbot' => $chatbot,
        ]);
    }

    /**
     * Update chatbot
     *
     * @param Request $request
     * @param string $uid
     * @return RedirectResponse
     */
    public function update(Request $request, string $uid): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'domain' => 'nullable|url|max:255',
            'language' => 'nullable|string|max:10',
            'tone' => 'nullable|string|in:professional,friendly,casual,formal',
            'welcome_message' => 'nullable|string|max:500',
            'offline_message' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:20',
            'widget_position' => 'nullable|string|in:bottom-right,bottom-left,top-right,top-left',
            'emoji_support' => 'nullable|boolean',
            'voice_support' => 'nullable|boolean',
            'image_support' => 'nullable|boolean',
            'human_takeover_enabled' => 'nullable|boolean',
            'ai_provider' => 'nullable|string|in:openai,gemini,claude',
            'ai_confidence_threshold' => 'nullable|numeric|min:0|max:1',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        try {
            $this->chatbotService->updateChatbot($chatbot, $request->all());

            return back()->with(response_status('Chatbot updated successfully'));
        } catch (Exception $e) {
            return back()->with(response_status($e->getMessage(), 'error'))->withInput();
        }
    }

    /**
     * Delete chatbot
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function destroy(string $uid): RedirectResponse
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        try {
            $this->chatbotService->deleteChatbot($chatbot);

            return redirect()->route('user.chatbot.index')
                ->with(response_status('Chatbot deleted successfully'));
        } catch (Exception $e) {
            return back()->with(response_status($e->getMessage(), 'error'));
        }
    }

    /**
     * Manage training data
     *
     * @param string $uid
     * @return View
     */
    public function training(string $uid): View
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $trainingData = ChatbotTrainingData::where('chatbot_id', $chatbot->id)
            ->latest()
            ->paginate(paginateNumber())
            ->appends(request()->all());

        return view('user.chatbot.training', [
            'meta_data' => $this->metaData(['title' => translate("Training Data - {$chatbot->name}")]),
            'chatbot' => $chatbot,
            'trainingData' => $trainingData,
        ]);
    }

    /**
     * Store training data
     *
     * @param Request $request
     * @param string $uid
     * @return RedirectResponse
     */
    public function storeTraining(Request $request, string $uid): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:text,file,url,faq',
            'title' => 'nullable|string|max:255',
            'content' => 'required_if:type,text,faq|nullable|string|max:10000',
            'source_url' => 'required_if:type,url|nullable|url|max:500',
            'file' => 'required_if:type,file|nullable|file|mimes:txt,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        try {
            if ($request->input('type') === 'file' && $request->hasFile('file')) {
                $this->chatbotService->uploadTrainingFile($chatbot, $request->file('file'));
            } else {
                $this->chatbotService->addTrainingData($chatbot, $request->input('type'), $request->all());
            }

            return back()->with(response_status('Training data added successfully'));
        } catch (Exception $e) {
            return back()->with(response_status($e->getMessage(), 'error'))->withInput();
        }
    }

    /**
     * Delete training data
     *
     * @param string $uid
     * @param int $id
     * @return RedirectResponse
     */
    public function destroyTraining(string $uid, int $id): RedirectResponse
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $training = ChatbotTrainingData::where('chatbot_id', $chatbot->id)
            ->where('id', $id)
            ->firstOrFail();

        $training->delete();

        return back()->with(response_status('Training data deleted successfully'));
    }

    /**
     * Manage agents
     *
     * @param string $uid
     * @return View
     */
    public function agents(string $uid): View
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $agents = ChatbotAgent::where('chatbot_id', $chatbot->id)
            ->with('user')
            ->latest()
            ->paginate(paginateNumber())
            ->appends(request()->all());

        return view('user.chatbot.agents', [
            'meta_data' => $this->metaData(['title' => translate("Agents - {$chatbot->name}")]),
            'chatbot' => $chatbot,
            'agents' => $agents,
        ]);
    }

    /**
     * Store agent
     *
     * @param Request $request
     * @param string $uid
     * @return RedirectResponse
     */
    public function storeAgent(Request $request, string $uid): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string|in:admin,agent,viewer',
            'can_takeover' => 'nullable|boolean',
            'auto_assign' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        ChatbotAgent::create([
            'chatbot_id' => $chatbot->id,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'can_takeover' => $request->boolean('can_takeover', true),
            'auto_assign' => $request->boolean('auto_assign', false),
            'status' => 'offline',
        ]);

        return back()->with(response_status('Agent added successfully'));
    }

    /**
     * Delete agent
     *
     * @param string $uid
     * @param int $id
     * @return RedirectResponse
     */
    public function destroyAgent(string $uid, int $id): RedirectResponse
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $agent = ChatbotAgent::where('chatbot_id', $chatbot->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($agent->role === 'admin') {
            return back()->with(response_status('Cannot delete admin agent', 'error'));
        }

        $agent->delete();

        return back()->with(response_status('Agent deleted successfully'));
    }

    /**
     * View analytics
     *
     * @param string $uid
     * @param Request $request
     * @return View
     */
    public function analytics(string $uid, Request $request): View
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $dateRange = $request->input('date_range', 'last_7_days');
        $startDate = match ($dateRange) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'last_7_days' => now()->subDays(7),
            'last_30_days' => now()->subDays(30),
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->subDays(7),
        };

        $totalConversations = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalMessages = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->where('created_at', '>=', $startDate)
            ->sum('total_messages');

        $humanTakeoverCount = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['human_requested', 'human_active', 'resolved'])
            ->whereNotNull('assigned_agent_id')
            ->count();

        $avgSatisfaction = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->where('created_at', '>=', $startDate)
            ->where('satisfaction_rated', true)
            ->avg('satisfaction_score');

        $usageLogs = ChatbotUsageLog::where('chatbot_id', $chatbot->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(usage_date) as date, SUM(tokens_used) as total_tokens, SUM(cost) as total_cost, COUNT(*) as message_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentConversations = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->with('assignedAgent')
            ->latest('last_message_at')
            ->limit(10)
            ->get();

        return view('user.chatbot.analytics', [
            'meta_data' => $this->metaData(['title' => translate("Analytics - {$chatbot->name}")]),
            'chatbot' => $chatbot,
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'humanTakeoverCount' => $humanTakeoverCount,
            'avgSatisfaction' => round($avgSatisfaction ?? 0, 2),
            'usageLogs' => $usageLogs,
            'recentConversations' => $recentConversations,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Get embed code
     *
     * @param string $uid
     * @return View
     */
    public function embedCode(string $uid): View
    {
        $chatbot = Chatbot::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        return view('user.chatbot.embed', [
            'meta_data' => $this->metaData(['title' => translate("Embed Code - {$chatbot->name}")]),
            'chatbot' => $chatbot,
            'embedCode' => $chatbot->getEmbedCode(),
        ]);
    }
}
