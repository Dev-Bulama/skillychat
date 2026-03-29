<?php

namespace App\Services\Email;

interface EmailServiceInterface
{
    /**
     * Send a single email.
     *
     * @return array{success: bool, message_id: string|null, raw: mixed, error: string|null}
     */
    public function send(
        string $to,
        string $subject,
        string $body,
        string $contentType = 'html',
        ?string $fromName  = null,
        ?string $fromEmail = null,
        ?string $replyTo   = null
    ): array;
}
