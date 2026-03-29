<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Log;

/**
 * Native PHP mail() driver — uses the server's sendmail/MTA.
 * No external API key needed; relies on correct server mail config.
 */
class PhpMailService implements EmailServiceInterface
{
    protected array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function send(
        string $to,
        string $subject,
        string $body,
        string $contentType = 'text/html',
        ?string $fromName  = null,
        ?string $fromEmail = null,
        ?string $replyTo   = null
    ): array {
        try {
            $from      = $fromEmail ?? ($this->credentials['from_email'] ?? ini_get('sendmail_from') ?: 'noreply@localhost');
            $name      = $fromName  ?? ($this->credentials['from_name']  ?? '');
            $fromHeader = $name ? "{$name} <{$from}>" : $from;

            $headers   = "MIME-Version: 1.0\r\n";
            $headers  .= "Content-Type: {$contentType}; charset=UTF-8\r\n";
            $headers  .= "From: {$fromHeader}\r\n";

            if ($replyTo) {
                $headers .= "Reply-To: {$replyTo}\r\n";
            }

            $headers .= "X-Mailer: PHP/" . phpversion();

            $result = mail($to, $subject, $body, $headers);

            if (!$result) {
                return ['success' => false, 'message' => 'mail() returned false. Check server mail configuration.'];
            }

            return ['success' => true, 'message' => 'Email sent via PHP mail().'];
        } catch (\Throwable $e) {
            Log::error('PhpMailService::send error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
