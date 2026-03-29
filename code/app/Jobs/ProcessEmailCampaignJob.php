<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailMessageLog;
use App\Models\SmsContact;
use App\Services\Email\EmailManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 3600;

    public function __construct(private int $campaignId) {}

    public function handle(EmailManager $emailManager): void
    {
        $campaign = EmailCampaign::with(['provider', 'groups'])->find($this->campaignId);

        if (!$campaign || !in_array($campaign->status, ['queued', 'processing'])) {
            return;
        }

        $campaign->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $driver = $emailManager->driver($campaign->provider);

            // Collect contacts with email addresses
            $contacts = SmsContact::whereHas('group', function ($q) use ($campaign) {
                $q->whereIn('sms_contact_groups.id', $campaign->groups->pluck('id'));
            })
            ->where('user_id', $campaign->user_id)
            ->where('is_subscribed', true)
            ->whereNotNull('email')
            ->get();

            $campaign->update(['total_recipients' => $contacts->count()]);

            $sent   = 0;
            $failed = 0;

            foreach ($contacts as $contact) {
                ['subject' => $subject, 'body' => $body] = $campaign->personalize($contact);

                $result = $driver->send(
                    $contact->email,
                    $subject,
                    $body,
                    $campaign->content_type,
                    $campaign->from_name,
                    $campaign->from_email,
                    $campaign->reply_to
                );

                EmailMessageLog::create([
                    'email_campaign_id' => $campaign->id,
                    'sms_contact_id'    => $contact->id,
                    'email'             => $contact->email,
                    'status'            => $result['success'] ? 'sent' : 'failed',
                    'message_id'        => $result['message_id'],
                    'error'             => $result['error'],
                    'sent_at'           => $result['success'] ? now() : null,
                ]);

                $result['success'] ? $sent++ : $failed++;

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
            Log::error('ProcessEmailCampaignJob failed', ['id' => $this->campaignId, 'error' => $e->getMessage()]);
            $campaign->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        EmailCampaign::where('id', $this->campaignId)->update([
            'status'         => 'failed',
            'failure_reason' => $e->getMessage(),
        ]);
    }
}
