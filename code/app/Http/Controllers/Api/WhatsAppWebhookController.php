<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta webhook verification (GET).
     * Meta sends: hub.mode, hub.verify_token, hub.challenge
     */
    public function verify(Request $request, string $verifyToken): Response|JsonResponse
    {
        $account = WhatsAppAccount::where('verify_token', $verifyToken)->first();

        if (!$account) {
            return response('Forbidden', 403);
        }

        $mode      = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token     = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response()->json(['error' => 'Verification failed'], 403);
    }

    /**
     * Receive inbound WhatsApp messages (POST).
     */
    public function receive(Request $request, string $verifyToken): JsonResponse
    {
        $account = WhatsAppAccount::where('verify_token', $verifyToken)->first();

        if (!$account) {
            return response()->json(['error' => 'Unknown account'], 404);
        }

        try {
            $payload = $request->all();
            WhatsAppService::processWebhook($payload, $account);
        } catch (\Throwable $e) {
            \Log::error('WhatsApp webhook error: ' . $e->getMessage(), [
                'account_id' => $account->id,
                'payload'    => $request->all(),
            ]);
        }

        // Always return 200 to Meta so it doesn't retry
        return response()->json(['status' => 'ok']);
    }
}
