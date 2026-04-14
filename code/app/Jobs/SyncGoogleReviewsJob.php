<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use App\Models\GoogleLocation;
use App\Models\GoogleReview;
use App\Models\GoogleSyncLog;
use App\Services\Google\GoogleBusinessService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGoogleReviewsJob implements ShouldQueue
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
            Log::warning("SyncGoogleReviewsJob: Location {$this->locationId} not found.");
            return;
        }

        $account = GoogleAccount::find($location->google_account_id);

        if (! $account) {
            Log::warning("SyncGoogleReviewsJob: No GoogleAccount for location {$this->locationId}.");
            return;
        }

        try {
            $service = new GoogleBusinessService();
            $result  = $service->getReviews($account, $location->location_name);

            if (! $result['success']) {
                GoogleSyncLog::record(
                    $location->user_id,
                    'sync_reviews',
                    'error',
                    $location->id,
                    null,
                    $result['error']
                );
                return;
            }

            $synced = 0;
            foreach ($result['data'] as $r) {
                $reviewName = $r['name'] ?? null;
                if (! $reviewName) continue;

                $rating    = GoogleReview::starFromString($r['starRating'] ?? 'ONE');
                $hasReply  = ! empty($r['reviewReply']['comment']);
                $status    = $hasReply ? 'replied' : 'pending';

                GoogleReview::updateOrCreate(
                    ['review_name' => $reviewName],
                    [
                        'location_id'    => $location->id,
                        'user_id'        => $location->user_id,
                        'reviewer_name'  => $r['reviewer']['displayName'] ?? null,
                        'reviewer_photo' => $r['reviewer']['profilePhotoUrl'] ?? null,
                        'star_rating'    => $rating,
                        'comment'        => $r['comment'] ?? null,
                        'reply'          => $hasReply ? $r['reviewReply']['comment'] : null,
                        'create_time'    => isset($r['createTime']) ? Carbon::parse($r['createTime']) : null,
                        'update_time'    => isset($r['updateTime']) ? Carbon::parse($r['updateTime']) : null,
                        'reply_time'     => $hasReply && isset($r['reviewReply']['updateTime'])
                            ? Carbon::parse($r['reviewReply']['updateTime'])
                            : null,
                        'status'         => $status,
                    ]
                );
                $synced++;
            }

            GoogleSyncLog::record(
                $location->user_id,
                'sync_reviews',
                'success',
                $location->id,
                200,
                "Synced {$synced} reviews"
            );
        } catch (\Throwable $e) {
            Log::error("SyncGoogleReviewsJob failed for location {$this->locationId}: " . $e->getMessage());

            GoogleSyncLog::record(
                $location->user_id,
                'sync_reviews',
                'error',
                $location->id,
                null,
                $e->getMessage()
            );
        }
    }
}
