<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Find every RUNNING subscription whose expired_at is in the past,
     * mark it EXPIRED, and fire the expiry notification email.
     */
    public function handle(): void
    {
        Subscription::with(['user', 'package'])
            ->running()
            ->whereNotNull('expired_at')
            ->whereDate('expired_at', '<', today())
            ->each(function (Subscription $subscription) {

                $subscription->update([
                    'status' => SubscriptionStatus::EXPIRED->value,
                ]);

                // Notify the user by email
                if ($subscription->user && $subscription->user->email) {
                    $userObj = (object) [
                        'email' => $subscription->user->email,
                        'name'  => $subscription->user->name,
                    ];

                    $codes = [
                        'name' => $subscription->package?->title ?? 'Subscription',
                        'time' => now()->format('d M Y H:i'),
                    ];

                    try {
                        SendMailJob::dispatch($userObj, 'SUBSCRIPTION_EXPIRED', $codes);
                    } catch (\Throwable $e) {
                        // Skip if mail template is missing or mail config is not set
                    }
                }
            });
    }
}
