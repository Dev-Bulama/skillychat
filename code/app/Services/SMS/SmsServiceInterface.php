<?php

namespace App\Services\SMS;

interface SmsServiceInterface
{
    /**
     * Send a single SMS message.
     *
     * @param  string $to      Recipient phone number (E.164 or local format per provider)
     * @param  string $message Message body
     * @param  string|null $senderId Optional sender ID / from number
     * @return array{success: bool, message_id: string|null, raw: mixed}
     */
    public function send(string $to, string $message, ?string $senderId = null): array;
}
