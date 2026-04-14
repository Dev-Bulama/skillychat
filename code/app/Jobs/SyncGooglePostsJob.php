<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use App\Models\GoogleLocation;
use App\Models\GooglePost;
use App\Models\GoogleSyncLog;
use App\Services\Google\GoogleBusinessService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGooglePostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $locationId;

    public function __construct(int $locationId)
    {
        $this->locationId = $locationId;
    }

    public function handle(): void
    {
        $location = GoogleLocation::find($this->locationId);

        if (! $location) {
            Log::warning("SyncGooglePostsJob: Location {$this->locationId} not found.");
            return;
        }

        $account = GoogleAccount::find($location->google_account_id);

        if (! $account) {
            Log::warning("SyncGooglePostsJob: No GoogleAccount for location {$this->locationId}.");
            return;
        }

        try {
            $service = new GoogleBusinessService();
            $result  = $service->getPosts($account, $location->location_name);

            if (! $result['success']) {
                GoogleSyncLog::record(
                    $location->user_id,
                    'sync_posts',
                    'error',
                    $location->id,
                    null,
                    $result['error']
                );
                return;
            }

            $synced = 0;
            foreach ($result['data'] as $p) {
                $postName = $p['name'] ?? null;
                if (! $postName) continue;

                GooglePost::updateOrCreate(
                    ['post_name' => $postName],
                    [
                        'location_id'         => $location->id,
                        'user_id'             => $location->user_id,
                        'topic_type'          => $p['topicType'] ?? 'STANDARD',
                        'summary'             => $p['summary'] ?? null,
                        'call_to_action_type' => $p['callToAction']['actionType'] ?? null,
                        'call_to_action_url'  => $p['callToAction']['url'] ?? null,
                        'state'               => $p['state'] ?? 'LIVE',
                        'create_time'         => isset($p['createTime']) ? Carbon::parse($p['createTime']) : null,
                    ]
                );
                $synced++;
            }

            GoogleSyncLog::record(
                $location->user_id,
                'sync_posts',
                'success',
                $location->id,
                200,
                "Synced {$synced} posts"
            );
        } catch (\Throwable $e) {
            Log::error("SyncGooglePostsJob failed for location {$this->locationId}: " . $e->getMessage());

            GoogleSyncLog::record(
                $location->user_id,
                'sync_posts',
                'error',
                $location->id,
                null,
                $e->getMessage()
            );
        }
    }
}
