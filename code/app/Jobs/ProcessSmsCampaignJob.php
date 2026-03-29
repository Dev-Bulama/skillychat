<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
use App\Models\SmsContact;
use App\Models\SmsMessageLog;
use App\Services\SMS\SmsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSmsCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 3600; // 1 hour max

    public function __construct(private int $campaignId) {}

    public function handle(SmsManager $smsManager): void
    {
        $campaign = SmsCampaign::with(['provider', 'groups.contacts'])->find($this->campaignId);

        if (!$campaign || !in_array($campaign->status, ['queued', 'processing'])) {
            return;
        }

        $campaign->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $driver = $smsManager->driver($campaign->provider);

            // Collect all subscribed contacts from all groups
            $contacts = SmsContact::whereHas('group', function ($q) use ($campaign) {
                $q->whereIn('sms_contact_groups.id', $campaign->groups->pluck('id'));
            })
            ->where('user_id', $campaign->user_id)
            ->where('is_subscribed', true)
            ->get();

            $campaign->update(['total_recipients' => $contacts->count()]);

            $sent   = 0;
            $failed = 0;

            foreach ($contacts as $contact) {
                $personalizedMessage = $campaign->personalize($contact);
                $result = $driver->send($contact->phone, $personalizedMessage, $campaign->sender_id);

                SmsMessageLog::create([
                    'sms_campaign_id' => $campaign->id,
                    'sms_contact_id'  => $contact->id,
                    'phone'           => $contact->phone,
                    'status'          => $result['success'] ? 'sent' : 'failed',
                    'response'        => isset($result['raw']) ? json_encode($result['raw']) : null,
                    'error'           => $result['error'] ?? null,
                    'sent_at'         => $result['success'] ? now() : null,
                ]);

                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                    Log::warning("SMS send failed for contact {$contact->id}", ['error' => $result['error'] ?? '']);
                }

                // Update counts periodically
                if (($sent + $failed) % 50 === 0) {
                    $campaign->update(['sent_count' => $sent, 'failed_count' => $failed]);
                }
            }

            $campaign->update([
                'status'       => 'completed',
                'sent_count'   => $sent,
                'failed_count' => $failed,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessSmsCampaignJob failed', ['campaign_id' => $this->campaignId, 'error' => $e->getMessage()]);
            $campaign->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        SmsCampaign::where('id', $this->campaignId)->update([
            'status'         => 'failed',
            'failure_reason' => $e->getMessage(),
        ]);
    }
}
