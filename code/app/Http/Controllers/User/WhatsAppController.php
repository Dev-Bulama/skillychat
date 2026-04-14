<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    /**
     * List user's WhatsApp accounts.
     *
     * @return View
     */
    public function index(): View
    {
        $accounts = WhatsAppAccount::where('user_id', $this->user->id)
            ->with('messages')
            ->latest()
            ->get();

        return view('user.whatsapp.index', [
            'meta_data' => $this->metaData(['title' => 'WhatsApp Accounts']),
            'accounts'  => $accounts,
        ]);
    }

    /**
     * Show the create form.
     *
     * @return View
     */
    public function create(): View
    {
        $chatbots = \App\Models\Chatbot::where('user_id', $this->user->id)
            ->active()
            ->get();

        return view('user.whatsapp.create', [
            'meta_data' => $this->metaData(['title' => 'Add WhatsApp Account']),
            'chatbots'  => $chatbots,
        ]);
    }

    /**
     * Store a new WhatsApp account.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone_number'     => 'required|string|max:50',
            'phone_number_id'  => 'required|string|max:255',
            'waba_id'          => 'nullable|string|max:255',
            'access_token'     => 'nullable|string',
            'app_id'           => 'nullable|string|max:255',
            'verify_token'     => 'nullable|string|max:255',
            'welcome_message'  => 'nullable|string',
            'fallback_message' => 'nullable|string',
            'ai_enabled'       => 'boolean',
            'chatbot_id'       => 'nullable|exists:chatbots,id',
        ]);

        $validated['verify_token'] = $request->verify_token ?: Str::random(32);

        WhatsAppAccount::create(array_merge($validated, [
            'user_id' => $this->user->id,
            'status'  => 1,
        ]));

        return redirect()->route('user.whatsapp.index')
            ->with(response_status('WhatsApp account added.'));
    }

    /**
     * Show the edit form for an account.
     *
     * @param string $uid
     * @return View
     */
    public function edit(string $uid): View
    {
        $account = WhatsAppAccount::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $chatbots = \App\Models\Chatbot::where('user_id', $this->user->id)
            ->active()
            ->get();

        return view('user.whatsapp.edit', [
            'meta_data' => $this->metaData(['title' => 'Edit WhatsApp Account']),
            'account'   => $account,
            'chatbots'  => $chatbots,
        ]);
    }

    /**
     * Update an existing WhatsApp account.
     *
     * @param Request $request
     * @param string  $uid
     * @return RedirectResponse
     */
    public function update(Request $request, string $uid): RedirectResponse
    {
        $account = WhatsAppAccount::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone_number'     => 'required|string|max:50',
            'phone_number_id'  => 'required|string|max:255',
            'waba_id'          => 'nullable|string|max:255',
            'access_token'     => 'nullable|string',
            'app_id'           => 'nullable|string|max:255',
            'verify_token'     => 'nullable|string|max:255',
            'welcome_message'  => 'nullable|string',
            'fallback_message' => 'nullable|string',
            'ai_enabled'       => 'boolean',
            'chatbot_id'       => 'nullable|exists:chatbots,id',
        ]);

        $account->update($validated);

        return back()->with(response_status('WhatsApp account updated.'));
    }

    /**
     * Delete an account.
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function destroy(string $uid): RedirectResponse
    {
        $account = WhatsAppAccount::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $account->delete();

        return redirect()->route('user.whatsapp.index')
            ->with(response_status('WhatsApp account deleted.'));
    }

    /**
     * Test the WhatsApp connection for an account.
     *
     * @param string $uid
     * @return JsonResponse
     */
    public function test(string $uid): JsonResponse
    {
        $account = WhatsAppAccount::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        if (empty($account->access_token) || empty($account->phone_number_id)) {
            return response()->json([
                'status'  => false,
                'message' => 'Account is not fully configured. Please provide access token and phone number ID.',
            ]);
        }

        try {
            $result = (new WhatsAppService($account))->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the conversation inbox for an account.
     *
     * @param string $uid
     * @return View
     */
    public function messages(string $uid): View
    {
        $account = WhatsAppAccount::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $contacts = WhatsAppMessage::where('account_id', $account->id)
            ->select('from_number', 'contact_name')
            ->groupBy('from_number', 'contact_name')
            ->latest()
            ->get();

        $selectedNumber = request('contact');

        $thread = $selectedNumber
            ? WhatsAppMessage::where('account_id', $account->id)
                ->where(function ($q) use ($selectedNumber) {
                    $q->where('from_number', $selectedNumber)
                      ->orWhere('to_number', $selectedNumber);
                })
                ->oldest()
                ->get()
            : collect();

        return view('user.whatsapp.messages', [
            'meta_data'      => $this->metaData(['title' => 'WhatsApp Messages']),
            'account'        => $account,
            'contacts'       => $contacts,
            'selectedNumber' => $selectedNumber,
            'thread'         => $thread,
        ]);
    }

    /**
     * Send a WhatsApp text message.
     *
     * @param Request $request
     * @param string  $uid
     * @return JsonResponse
     */
    public function sendMessage(Request $request, string $uid): JsonResponse
    {
        $validated = $request->validate([
            'to'      => 'required|string',
            'message' => 'required|string',
        ]);

        $account = WhatsAppAccount::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        try {
            $result = (new WhatsAppService($account))->sendText($validated['to'], $validated['message']);

            WhatsAppMessage::create([
                'account_id'    => $account->id,
                'user_id'       => $this->user->id,
                'wa_message_id' => $result['data']['messages'][0]['id'] ?? null,
                'from_number'   => $account->phone_number,
                'to_number'     => $validated['to'],
                'direction'     => 'outbound',
                'type'          => 'text',
                'content'       => $validated['message'],
                'status'        => $result['success'] ? 'sent' : 'failed',
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the setup / documentation guide.
     *
     * @return View
     */
    public function documentation(): View
    {
        return view('user.whatsapp.documentation', [
            'meta_data' => $this->metaData(['title' => 'WhatsApp Setup Guide']),
        ]);
    }
}
