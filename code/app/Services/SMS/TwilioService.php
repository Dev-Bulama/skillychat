<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioService implements SmsServiceInterface
{
    public function __construct(private array $credentials) {}

    public function send(string $to, string $message, ?string $senderId = null): array
    {
        $accountSid = $this->credentials['account_sid'] ?? '';
        $authToken  = $this->credentials['auth_token']  ?? '';
        $from       = $senderId ?? ($this->credentials['from_number'] ?? '');

        try {
            $response = Http::timeout(15)
                ->withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'To'   => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['sid'])) {
                return ['success' => true, 'message_id' => $body['sid'], 'raw' => $body];
            }

            return ['success' => false, 'message_id' => null, 'raw' => $body, 'error' => $body['message'] ?? 'Unknown error'];
        } catch (\Throwable $e) {
            Log::error('TwilioService::send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['success' => false, 'message_id' => null, 'raw' => null, 'error' => $e->getMessage()];
        }
    }
}
