<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSubscriptionReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Send reminder emails at 7, 3, and 1 day(s) before a subscription expires.
     */
    public function handle(): void
    {
        foreach ([7, 3, 1] as $daysLeft) {
            $targetDate = today()->addDays($daysLeft)->toDateString();

            Subscription::with(['user', 'package'])
                ->running()
                ->whereNotNull('expired_at')
                ->whereDate('expired_at', $targetDate)
                ->each(function (Subscription $subscription) use ($daysLeft) {

                    if (!$subscription->user || !$subscription->user->email) {
                        return;
                    }

                    $userObj = (object) [
                        'email' => $subscription->user->email,
                        'name'  => $subscription->user->name,
                    ];

                    $codes = [
                        'plan_name' => $subscription->package?->title ?? 'your plan',
                        'status'    => "Expiring in {$daysLeft} day(s) — on " . $subscription->expired_at,
                        'time'      => $subscription->expired_at,
                    ];

                    try {
                        SendMailJob::dispatch($userObj, 'SUBSCRIPTION_REMINDER', $codes);
                    } catch (\Throwable $e) {
                        // Template may not exist yet — skip silently
                    }
                });
        }
    }
}
