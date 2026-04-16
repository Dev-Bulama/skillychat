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
     * Step 1 of Embedded Signup: exchange Meta code for token, return phone list.
     */
    public function embeddedSignup(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $appId     = site_settings('whatsapp_default_app_id');
        $appSecret = site_settings('whatsapp_app_secret');

        if (!$appId || !$appSecret) {
            return response()->json(['success' => false, 'error' => 'Admin has not configured App Secret yet. Please contact support.']);
        }

        // Exchange code → short-lived token
        $tokenRes = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/oauth/access_token', [
            'client_id'     => $appId,
            'client_secret' => $appSecret,
            'code'          => $request->code,
        ]);

        if ($tokenRes->failed()) {
            return response()->json(['success' => false, 'error' => 'Token exchange failed: ' . $tokenRes->json('error.message', $tokenRes->body())]);
        }

        $token = $tokenRes->json('access_token');

        // Extend to long-lived user token (60 days)
        $longRes = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $appId,
            'client_secret'     => $appSecret,
            'fb_exchange_token' => $token,
        ]);
        if ($longRes->successful() && $longRes->json('access_token')) {
            $token = $longRes->json('access_token');
        }

        // Fetch all WABAs accessible to this user
        $wabaRes = \Illuminate\Support\Facades\Http::withToken($token)
            ->get('https://graph.facebook.com/v19.0/me/whatsapp_business_accounts');

        if ($wabaRes->failed() || empty($wabaRes->json('data'))) {
            return response()->json(['success' => false, 'error' => 'No WhatsApp Business Account found on this Facebook account.']);
        }

        $phones = [];
        foreach ($wabaRes->json('data') as $waba) {
            $wabaId   = $waba['id'];
            $wabaName = $waba['name'] ?? 'Business Account';

            // Get phone numbers for this WABA
            $phoneRes = \Illuminate\Support\Facades\Http::withToken($token)
                ->get("https://graph.facebook.com/v19.0/{$wabaId}/phone_numbers", [
                    'fields' => 'id,display_phone_number,verified_name,quality_rating,status',
                ]);

            foreach ($phoneRes->json('data', []) as $p) {
                $phones[] = [
                    'phone_number_id' => $p['id'],
                    'phone_number'    => $p['display_phone_number'],
                    'verified_name'   => $p['verified_name'] ?? '',
                    'quality'         => $p['quality_rating'] ?? 'UNKNOWN',
                    'waba_id'         => $wabaId,
                    'waba_name'       => $wabaName,
                ];
            }

            // Subscribe platform webhook to this WABA
            \Illuminate\Support\Facades\Http::withToken($token)
                ->post("https://graph.facebook.com/v19.0/{$wabaId}/subscribed_apps");
        }

        if (empty($phones)) {
            return response()->json(['success' => false, 'error' => 'No verified phone numbers found in your WhatsApp Business Account.']);
        }

        // Stash token in session for step 2
        session(['wa_embedded_token' => $token]);

        return response()->json(['success' => true, 'phones' => $phones]);
    }

    /**
     * Step 2 of Embedded Signup: user selected a phone — create the account.
     */
    public function embeddedComplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone_number_id'  => 'required|string',
            'phone_number'     => 'required|string',
            'waba_id'          => 'required|string',
            'welcome_message'  => 'nullable|string',
            'fallback_message' => 'nullable|string',
            'ai_enabled'       => 'boolean',
            'chatbot_id'       => 'nullable|exists:chatbots,id',
        ]);

        $token = session('wa_embedded_token');
        if (!$token) {
            return response()->json(['success' => false, 'error' => 'Session expired. Please start again.']);
        }

        // Upsert — reconnect if same phone_number_id already exists for this user
        $account = WhatsAppAccount::where('user_id', $this->user->id)
            ->where('phone_number_id', $validated['phone_number_id'])
            ->first();

        $data = [
            'user_id'          => $this->user->id,
            'name'             => $validated['name'],
            'phone_number'     => $validated['phone_number'],
            'phone_number_id'  => $validated['phone_number_id'],
            'waba_id'          => $validated['waba_id'],
            'access_token'     => $token,
            'verify_token'     => $account?->verify_token ?? Str::random(32),
            'welcome_message'  => $validated['welcome_message'] ?? site_settings('whatsapp_default_welcome_message', ''),
            'fallback_message' => $validated['fallback_message'] ?? site_settings('whatsapp_default_fallback_message', ''),
            'ai_enabled'       => $validated['ai_enabled'] ?? false,
            'chatbot_id'       => $validated['chatbot_id'] ?? null,
            'status'           => 1,
        ];

        if ($account) {
            $account->update($data);
        } else {
            WhatsAppAccount::create($data);
        }

        session()->forget('wa_embedded_token');

        return response()->json(['success' => true, 'redirect' => route('user.whatsapp.index')]);
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
