<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailgunService implements EmailServiceInterface
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
        $apiKey    = $this->credentials['api_key']   ?? '';
        $domain    = $this->credentials['domain']    ?? '';
        $fromEmail = $fromEmail ?? ($this->credentials['from_email'] ?? "noreply@{$domain}");
        $fromName  = $fromName  ?? ($this->credentials['from_name']  ?? '');
        $region    = $this->credentials['region']    ?? 'us';
        $baseUrl   = $region === 'eu' ? 'https://api.eu.mailgun.net' : 'https://api.mailgun.net';

        try {
            $formData = [
                'from'    => "{$fromName} <{$fromEmail}>",
                'to'      => $to,
                'subject' => $subject,
                ($contentType === 'html' ? 'html' : 'text') => $body,
            ];

            if ($replyTo) $formData['h:Reply-To'] = $replyTo;

            $response = Http::timeout(15)
                ->withBasicAuth('api', $apiKey)
                ->asForm()
                ->post("{$baseUrl}/v3/{$domain}/messages", $formData);

            $resBody = $response->json();

            if ($response->successful() && isset($resBody['id'])) {
                return ['success' => true, 'message_id' => $resBody['id'], 'raw' => $resBody, 'error' => null];
            }

            return ['success' => false, 'message_id' => null, 'raw' => $resBody, 'error' => $resBody['message'] ?? 'Mailgun error'];
        } catch (\Throwable $e) {
            Log::error('MailgunService::send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['success' => false, 'message_id' => null, 'raw' => null, 'error' => $e->getMessage()];
        }
    }
}
