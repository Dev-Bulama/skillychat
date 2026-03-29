<?php

namespace App\Services\Email;

use App\Models\EmailProvider;

class EmailManager
{
    public function driver(EmailProvider $provider): EmailServiceInterface
    {
        $credentials = $provider->credentials;

        return match ($provider->provider) {
            'sendgrid' => new SendGridService($credentials),
            'mailgun'  => new MailgunService($credentials),
            'smtp'     => new SmtpEmailService($credentials),
            default    => throw new \InvalidArgumentException("Unknown email provider: {$provider->provider}"),
        };
    }

    public static function supportedProviders(): array
    {
        return [
            'sendgrid' => [
                'label'  => 'SendGrid',
                'fields' => [
                    ['key' => 'api_key',    'label' => 'API Key',       'type' => 'password', 'required' => true],
                    ['key' => 'from_email', 'label' => 'From Email',    'type' => 'email',    'required' => true],
                    ['key' => 'from_name',  'label' => 'From Name',     'type' => 'text',     'required' => false],
                ],
            ],
            'mailgun' => [
                'label'  => 'Mailgun',
                'fields' => [
                    ['key' => 'api_key',    'label' => 'API Key',       'type' => 'password', 'required' => true],
                    ['key' => 'domain',     'label' => 'Domain',        'type' => 'text',     'required' => true],
                    ['key' => 'region',     'label' => 'Region (us/eu)','type' => 'text',     'required' => false],
                    ['key' => 'from_email', 'label' => 'From Email',    'type' => 'email',    'required' => false],
                    ['key' => 'from_name',  'label' => 'From Name',     'type' => 'text',     'required' => false],
                ],
            ],
            'smtp' => [
                'label'  => 'SMTP',
                'fields' => [
                    ['key' => 'host',       'label' => 'SMTP Host',     'type' => 'text',     'required' => true],
                    ['key' => 'port',       'label' => 'Port',          'type' => 'number',   'required' => true],
                    ['key' => 'username',   'label' => 'Username',      'type' => 'text',     'required' => true],
                    ['key' => 'password',   'label' => 'Password',      'type' => 'password', 'required' => true],
                    ['key' => 'encryption', 'label' => 'Encryption (tls/ssl)', 'type' => 'text', 'required' => false],
                    ['key' => 'from_email', 'label' => 'From Email',    'type' => 'email',    'required' => false],
                    ['key' => 'from_name',  'label' => 'From Name',     'type' => 'text',     'required' => false],
                ],
            ],
        ];
    }
}
