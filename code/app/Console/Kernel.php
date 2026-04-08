<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync SMM order statuses every 10 minutes
        $schedule->job(new \App\Jobs\SyncSMMOrderStatuses())->everyTenMinutes();

        // Expire subscriptions whose expired_at date has passed (runs just after midnight)
        $schedule->job(new \App\Jobs\ExpireSubscriptions())->dailyAt('00:05');

        // Send renewal reminder emails 7, 3, and 1 day before expiry (runs at 8 AM)
        $schedule->job(new \App\Jobs\SendSubscriptionReminders())->dailyAt('08:00');

        // Process drip sequence steps every 5 minutes
        $schedule->job(new \App\Jobs\ProcessDripSequences())->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
