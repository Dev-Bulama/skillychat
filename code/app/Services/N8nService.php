<?php

namespace App\Services;

use App\Models\Chatbot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nService
{
    /**
     * Fire an n8n webhook event for a chatbot.
     *
     * @param  Chatbot $chatbot
     * @param  string  $event   'new_message' | 'new_lead' | 'campaign_sent'
     * @param  array   $payload Additional payload data
     */
    public function fire(Chatbot $chatbot, string $event, array $payload = []): void
    {
        if (!$chatbot->n8n_enabled || empty($chatbot->n8n_webhook_url)) {
            return;
        }

        // Check if this event is subscribed to
        $events = $chatbot->n8n_events ?? [];
        if (!empty($events) && !in_array($event, $events)) {
            return;
        }

        $body = array_merge([
            'event'      => $event,
            'chatbot_id' => $chatbot->uid,
            'chatbot_name' => $chatbot->name,
            'timestamp'  => now()->toIso8601String(),
        ], $payload);

        try {
            $request = Http::timeout(10);

            // Add API key header if configured
            if ($chatbot->n8n_api_key) {
                $request = $request->withToken($chatbot->n8n_api_key);
            }

            // Add extra headers if configured
            $extraHeaders = $chatbot->n8n_extra_headers ?? [];
            if (!empty($extraHeaders)) {
                $request = $request->withHeaders($extraHeaders);
            }

            $response = $request->post($chatbot->n8n_webhook_url, $body);

            if (!$response->successful()) {
                Log::warning('N8nService: webhook returned non-2xx', [
                    'chatbot_id' => $chatbot->uid,
                    'event'      => $event,
                    'status'     => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            // Never let n8n errors break main flow
            Log::error('N8nService::fire error', [
                'chatbot_id' => $chatbot->uid,
                'event'      => $event,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
