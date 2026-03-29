<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridService implements EmailServiceInterface
{
    public function __construct(private array $credentials) {}

    public function send(
        string $to,
        string $subject,
        string $body,
        string $contentType = 'html',
        ?string $fromName   = null,
        ?string $fromEmail  = null,
        ?string $replyTo    = null
    ): array {
        $apiKey    = $this->credentials['api_key'] ?? '';
        $fromEmail = $fromEmail ?? ($this->credentials['from_email'] ?? '');
        $fromName  = $fromName  ?? ($this->credentials['from_name']  ?? '');

        try {
            $payload = [
                'personalizations' => [['to' => [['email' => $to]]]],
                'from'    => ['email' => $fromEmail, 'name' => $fromName],
                'subject' => $subject,
                'content' => [['type' => $contentType === 'html' ? 'text/html' : 'text/plain', 'value' => $body]],
            ];

            if ($replyTo) {
                $payload['reply_to'] = ['email' => $replyTo];
            }

            $response = Http::timeout(15)
                ->withToken($apiKey)
                ->post('https://api.sendgrid.com/v3/mail/send', $payload);

            if ($response->successful()) {
                return ['success' => true, 'message_id' => $response->header('X-Message-Id'), 'raw' => null, 'error' => null];
            }

            return ['success' => false, 'message_id' => null, 'raw' => $response->json(), 'error' => $response->json()['errors'][0]['message'] ?? 'SendGrid error'];
        } catch (\Throwable $e) {
            Log::error('SendGridService::send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['success' => false, 'message_id' => null, 'raw' => null, 'error' => $e->getMessage()];
        }
    }
}
