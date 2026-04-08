<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppAccount;
use App\Models\WhatsAppMessage;
use App\Models\CrmLead;
use App\Models\CrmPipeline;
use App\Models\CrmStage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private const GRAPH_URL = 'https://graph.facebook.com/v19.0';

    public function __construct(private WhatsAppAccount $account) {}

    // ── Send a plain text message ──────────────────────────────────────────
    public function sendText(string $to, string $message): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['preview_url' => false, 'body' => $message],
        ]);
    }

    // ── Send a template message ────────────────────────────────────────────
    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => $languageCode],
                'components' => $components,
            ],
        ]);
    }

    // ── Mark a message as read ─────────────────────────────────────────────
    public function markRead(string $messageId): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'status'            => 'read',
            'message_id'        => $messageId,
        ]);
    }

    // ── Test connection ────────────────────────────────────────────────────
    public function testConnection(): array
    {
        try {
            $response = Http::withToken($this->account->access_token)
                ->timeout(10)
                ->get(self::GRAPH_URL . "/{$this->account->phone_number_id}", ['fields' => 'id,display_phone_number,verified_name']);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            return ['success' => false, 'message' => $response->json('error.message', 'Connection failed')];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Get message templates ──────────────────────────────────────────────
    public function getTemplates(): array
    {
        try {
            $response = Http::withToken($this->account->access_token)
                ->get(self::GRAPH_URL . "/{$this->account->waba_id}/message_templates", ['limit' => 100]);
            return $response->successful() ? $response->json('data', []) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    // ── Process inbound webhook payload ───────────────────────────────────
    public static function processWebhook(array $payload, WhatsAppAccount $account): void
    {
        $entry    = $payload['entry'][0] ?? null;
        $changes  = $entry['changes'][0] ?? null;
        $value    = $changes['value'] ?? null;

        if (!$value) return;

        // Handle incoming messages
        foreach ($value['messages'] ?? [] as $msg) {
            self::storeInboundMessage($msg, $value['contacts'][0] ?? [], $account);
        }

        // Handle status updates
        foreach ($value['statuses'] ?? [] as $status) {
            WhatsAppMessage::where('wa_message_id', $status['id'])
                ->update(['status' => $status['status']]);
        }
    }

    private static function storeInboundMessage(array $msg, array $contact, WhatsAppAccount $account): void
    {
        $from        = $msg['from'];
        $contactName = $contact['profile']['name'] ?? $from;
        $content     = match($msg['type']) {
            'text'     => $msg['text']['body'] ?? '',
            'image'    => '[Image]',
            'audio'    => '[Voice Note]',
            'document' => '[Document: ' . ($msg['document']['filename'] ?? '') . ']',
            default    => '[' . strtoupper($msg['type']) . ']',
        };

        // Auto-create CRM lead if not already tracked
        $lead = CrmLead::where('user_id', $account->user_id)
            ->where('phone', $from)
            ->first();

        if (!$lead) {
            $pipeline = CrmPipeline::where('user_id', $account->user_id)->where('is_default', 1)->first()
                     ?? CrmPipeline::where('user_id', $account->user_id)->first();

            if ($pipeline) {
                $stage = CrmStage::where('pipeline_id', $pipeline->id)->orderBy('position')->first();
                if ($stage) {
                    $lead = CrmLead::create([
                        'user_id'     => $account->user_id,
                        'pipeline_id' => $pipeline->id,
                        'stage_id'    => $stage->id,
                        'name'        => $contactName,
                        'phone'       => $from,
                        'source'      => 'whatsapp',
                    ]);
                }
            }
        }

        WhatsAppMessage::create([
            'account_id'   => $account->id,
            'user_id'      => $account->user_id,
            'wa_message_id'=> $msg['id'],
            'from_number'  => $from,
            'to_number'    => $account->phone_number,
            'contact_name' => $contactName,
            'direction'    => 'inbound',
            'type'         => $msg['type'],
            'content'      => $content,
            'status'       => 'received',
            'crm_lead_id'  => $lead?->id,
        ]);

        // Auto-reply: send welcome message if this is first contact
        $prevCount = WhatsAppMessage::where('account_id', $account->id)
            ->where('from_number', $from)->where('direction', 'inbound')->count();

        if ($prevCount === 1 && $account->welcome_message) {
            (new self($account))->sendText($from, $account->welcome_message);
        } elseif ($account->fallback_message) {
            (new self($account))->sendText($from, $account->fallback_message);
        }
    }

    // ── Internal HTTP helper ───────────────────────────────────────────────
    private function send(array $payload): array
    {
        try {
            $response = Http::withToken($this->account->access_token)
                ->timeout(15)
                ->post(self::GRAPH_URL . "/{$this->account->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            $error = $response->json('error.message', 'Send failed');
            Log::warning('WhatsApp send failed', ['error' => $error, 'account' => $this->account->id]);
            return ['success' => false, 'message' => $error];
        } catch (\Throwable $e) {
            Log::error('WhatsApp exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
