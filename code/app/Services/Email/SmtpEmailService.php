<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class SmtpEmailService implements EmailServiceInterface
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
        try {
            $host       = $this->credentials['host']       ?? '';
            $port       = $this->credentials['port']       ?? 587;
            $username   = $this->credentials['username']   ?? '';
            $password   = $this->credentials['password']   ?? '';
            $encryption = $this->credentials['encryption'] ?? 'tls';
            $fromEmail  = $fromEmail ?? ($this->credentials['from_email'] ?? $username);
            $fromName   = $fromName  ?? ($this->credentials['from_name']  ?? '');

            $dsn       = "{$encryption}://{$username}:{$password}@{$host}:{$port}";
            $transport = Transport::fromDsn($dsn);
            $mailer    = new Mailer($transport);

            $email = (new Email())
                ->from("{$fromName} <{$fromEmail}>")
                ->to($to)
                ->subject($subject);

            if ($contentType === 'html') {
                $email->html($body);
            } else {
                $email->text($body);
            }

            if ($replyTo) $email->replyTo($replyTo);

            $mailer->send($email);

            return ['success' => true, 'message_id' => null, 'raw' => null, 'error' => null];
        } catch (\Throwable $e) {
            Log::error('SmtpEmailService::send error', ['error' => $e->getMessage(), 'to' => $to]);
            return ['success' => false, 'message_id' => null, 'raw' => null, 'error' => $e->getMessage()];
        }
    }
}
