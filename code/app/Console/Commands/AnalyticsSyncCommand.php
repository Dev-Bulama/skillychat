<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyticsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:sync {--account= : Specific account ID to sync} {--days=7 : Number of days to look back for posts}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize analytics data from social media platforms';

    /**
     * Supported platforms with their service classes
     */
    protected array $supportedPlatforms = [
        'facebook' => \App\Http\Services\Account\facebook\Account::class,
        'instagram' => \App\Http\Services\Account\instagram\Account::class,
        'twitter' => \App\Http\Services\Account\twitter\Account::class,
        'tiktok' => \App\Http\Services\Account\tiktok\Account::class,
        'youtube' => \App\Http\Services\Account\youtube\Account::class,
        'linkedin' => \App\Http\Services\Account\linkedin\Account::class,
        'threads' => \App\Http\Services\Account\threads\Account::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);

        $this->info('Starting analytics sync...');
        $this->newLine();

        try {
            $days = (int) $this->option('days');
            $accountId = $this->option('account');

            // Get active social media accounts
            $query = SocialAccount::with('platform')
                ->where('status', '1')
                ->whereNotNull('token');

            if ($accountId) {
                $query->where('id', $accountId);
            }

            $accounts = $query->get();

            if ($accounts->isEmpty()) {
                $this->warn('No active accounts found to sync.');
                return Command::SUCCESS;
            }

            $this->info("Found {$accounts->count()} active account(s) to sync");
            $this->newLine();

            $stats = [
                'accounts_processed' => 0,
                'posts_synced' => 0,
                'posts_failed' => 0,
            ];

            foreach ($accounts as $account) {
                $this->syncAccountAnalytics($account, $days, $stats);
            }

            // Update last sync timestamp
            DB::table('settings')
                ->updateOrInsert(
                    ['key' => 'last_analytics_sync'],
                    ['value' => now()->toISOString(), 'updated_at' => now()]
                );

            $executionTime = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('=== Analytics Sync Summary ===');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Accounts Processed', $stats['accounts_processed']],
                    ['Posts Synced', $stats['posts_synced']],
                    ['Posts Failed', $stats['posts_failed']],
                    ['Execution Time', "{$executionTime}s"],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error syncing analytics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync analytics data for a specific account
     */
    private function syncAccountAnalytics(SocialAccount $account, int $days, array &$stats): void
    {
        $platformSlug = strtolower($account->platform->slug ?? '');

        $this->line("Processing: {$account->name} ({$platformSlug})");

        // Check if platform is supported
        if (!isset($this->supportedPlatforms[$platformSlug])) {
            $this->warn("  - Platform '{$platformSlug}' not supported for analytics");
            return;
        }

        // Instantiate the platform service
        $serviceClass = $this->supportedPlatforms[$platformSlug];
        $service = new $serviceClass();

        // Get recent posts for this account
        $recentPosts = SocialPost::with(['account', 'platform'])
            ->where('account_id', $account->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 1) // Success status
            ->whereNotNull('platform_post_id')
            ->get();

        if ($recentPosts->isEmpty()) {
            $this->line("  - No recent posts found (last {$days} days)");
            $stats['accounts_processed']++;
            return;
        }

        $this->line("  - Found {$recentPosts->count()} post(s) to sync");

        // Check if service has getInsight method
        if (!method_exists($service, 'getInsight')) {
            $this->warn("  - Platform '{$platformSlug}' service missing getInsight method");
            return;
        }

        foreach ($recentPosts as $post) {
            try {
                $response = $service->getInsight($post, $account);

                if ($response['status'] && !empty($response['metrics'])) {
                    $metrics = $response['metrics'];

                    DB::table('post_metrics')
                        ->updateOrInsert(
                            ['post_id' => $post->id],
                            [
                                'impressions' => $metrics['impressions'] ?? 0,
                                'engagements' => $metrics['engagements'] ?? 0,
                                'reactions' => $metrics['reactions'] ?? 0,
                                'comments' => $metrics['comments'] ?? 0,
                                'shares' => $metrics['shares'] ?? 0,
                                'likes' => $metrics['likes'] ?? 0,
                                'reach' => $metrics['reach'] ?? 0,
                                'updated_at' => now(),
                            ]
                        );

                    $this->line("    ✓ Post #{$post->id}: impressions={$metrics['impressions']}, engagements={$metrics['engagements']}");
                    $stats['posts_synced']++;

                } else {
                    $message = $response['message'] ?? 'Unknown error';
                    $this->warn("    ✗ Post #{$post->id}: {$message}");
                    $stats['posts_failed']++;
                }

                // Longer delay for rate-limited APIs (especially X Free tier)
                if ($platformSlug === 'twitter') {
                    usleep(1000000); // 1 second delay for X
                } else {
                    usleep(200000); // 200ms delay for others
                }

            } catch (\Exception $e) {
                $this->error("    ✗ Post #{$post->id}: " . $e->getMessage());
                $stats['posts_failed']++;
            }

            // Check if we hit rate limit and should stop for this platform
            if (isset($response['rate_limited']) && $response['rate_limited']) {
                $this->warn("  ⚠ Rate limited by {$platformSlug} API. Skipping remaining posts.");
                break;
            }
        }

        $stats['accounts_processed']++;
    }
}
