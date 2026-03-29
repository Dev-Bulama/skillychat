<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KudiSmsService implements SmsServiceInterface
{
    public function __construct(private array $credentials) {}

    public function send(string $to, string $message, ?string $senderId = null): array
    {
        $apiKey  = $this->credentials['api_key']   ?? '';
        $from    = $senderId ?? ($this->credentials['sender_id'] ?? '');

        try {
            $response = Http::timeout(15)->post('https://account.kudisms.net/api/', [
                'username' => $this->credentials['username'] ?? '',
                'password' => $this->credentials['password'] ?? '',
                'message'  => $message,
                'sender'   => $from,
                'mobiles'  => $to,
            ]);

            $body = $response->body();

            if ($response->successful() && str_starts_with(trim($body), '1')) {
                return ['success' => true, 'message_id' => trim($body), 'raw' => $body];
            }

            return ['success' => false, 'message_id' => null, 'raw' => $body, 'error' => $body];
        } catch (\Throwable $e) {
            Log::error('KudiSmsService::send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['success' => false, 'message_id' => null, 'raw' => null, 'error' => $e->getMessage()];
        }
    }
}
