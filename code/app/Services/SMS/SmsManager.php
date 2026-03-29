<?php

namespace App\Services\SMS;

use App\Models\SmsProvider;

class SmsManager
{
    public function driver(SmsProvider $provider): SmsServiceInterface
    {
        $credentials = $provider->credentials;

        return match ($provider->provider) {
            'termii'  => new TermiiService($credentials),
            'twilio'  => new TwilioService($credentials),
            'kudisms' => new KudiSmsService($credentials),
            default   => throw new \InvalidArgumentException("Unknown SMS provider: {$provider->provider}"),
        };
    }

    public static function supportedProviders(): array
    {
        return [
            'termii'  => [
                'label'  => 'Termii',
                'fields' => [
                    ['key' => 'api_key',   'label' => 'API Key',   'type' => 'password', 'required' => true],
                    ['key' => 'sender_id', 'label' => 'Sender ID', 'type' => 'text',     'required' => false],
                ],
            ],
            'twilio' => [
                'label'  => 'Twilio',
                'fields' => [
                    ['key' => 'account_sid',  'label' => 'Account SID',  'type' => 'text',     'required' => true],
                    ['key' => 'auth_token',   'label' => 'Auth Token',   'type' => 'password', 'required' => true],
                    ['key' => 'from_number',  'label' => 'From Number',  'type' => 'text',     'required' => true],
                ],
            ],
            'kudisms' => [
                'label'  => 'Kudi SMS',
                'fields' => [
                    ['key' => 'username',  'label' => 'Username',  'type' => 'text',     'required' => true],
                    ['key' => 'password',  'label' => 'Password',  'type' => 'password', 'required' => true],
                    ['key' => 'sender_id', 'label' => 'Sender ID', 'type' => 'text',     'required' => false],
                ],
            ],
        ];
    }
}
