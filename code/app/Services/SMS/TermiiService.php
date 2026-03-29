<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TermiiService implements SmsServiceInterface
{
    public function __construct(private array $credentials) {}

    public function send(string $to, string $message, ?string $senderId = null): array
    {
        $apiKey   = $this->credentials['api_key'] ?? '';
        $from     = $senderId ?? ($this->credentials['sender_id'] ?? 'N-Alert');

        try {
            $channel = $this->credentials['channel'] ?? 'generic';

            $response = Http::timeout(15)->post('https://api.ng.termii.com/api/sms/send', [
                'to'         => $to,
                'from'       => $from,
                'sms'        => $message,
                'type'       => 'plain',
                'api_key'    => $apiKey,
                'channel'    => $channel,
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['message_id'])) {
                return ['success' => true, 'message_id' => $body['message_id'], 'raw' => $body];
            }

            return ['success' => false, 'message_id' => null, 'raw' => $body, 'error' => $body['message'] ?? 'Unknown error'];
        } catch (\Throwable $e) {
            Log::error('TermiiService::send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['success' => false, 'message_id' => null, 'raw' => null, 'error' => $e->getMessage()];
        }
    }
}
