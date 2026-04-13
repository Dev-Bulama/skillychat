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
     * Platform-level webhook verification (GET).
     * Admin configures ONE URL: /webhook/whatsapp/platform
     * Verify token is stored in site_settings('whatsapp_platform_verify_token').
     */
    public function verifyPlatform(Request $request): Response|JsonResponse
    {
        $storedToken = site_settings('whatsapp_platform_verify_token', '');
        $mode        = $request->query('hub_mode')         ?? $request->query('hub.mode');
        $token       = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge   = $request->query('hub_challenge')    ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && !empty($storedToken) && $token === $storedToken) {
            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response()->json(['error' => 'Verification failed'], 403);
    }

    /**
     * Platform-level webhook receive (POST).
     * Routes incoming messages to the correct user by matching phone_number_id
     * from the payload metadata against WhatsAppAccount records.
     * Users do NOT need to configure anything in Meta — this handles everyone.
     */
    public function receivePlatform(Request $request): JsonResponse
    {
        try {
            $payload   = $request->all();
            $entry     = $payload['entry'][0] ?? null;
            $changes   = $entry['changes'][0] ?? null;
            $value     = $changes['value'] ?? null;

            if (!$value) {
                return response()->json(['status' => 'ok']);
            }

            // Identify which WhatsApp account this is for via phone_number_id
            $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

            if ($phoneNumberId) {
                $account = WhatsAppAccount::where('phone_number_id', $phoneNumberId)
                    ->where('status', 1)
                    ->first();

                if ($account) {
                    WhatsAppService::processWebhook($payload, $account);
                    return response()->json(['status' => 'ok']);
                }
            }

            // Fallback: try matching by display_phone_number
            $displayPhone = $value['metadata']['display_phone_number'] ?? null;
            if ($displayPhone) {
                $normalized = preg_replace('/\D/', '', $displayPhone);
                $account = WhatsAppAccount::where('status', 1)->get()->first(function ($acc) use ($normalized) {
                    return preg_replace('/\D/', '', $acc->phone_number ?? '') === $normalized;
                });

                if ($account) {
                    WhatsAppService::processWebhook($payload, $account);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('WhatsApp platform webhook error: ' . $e->getMessage(), [
                'payload' => $request->all(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

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
