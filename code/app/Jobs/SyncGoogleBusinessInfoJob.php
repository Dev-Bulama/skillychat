<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use App\Models\GoogleLocation;
use App\Models\GoogleSyncLog;
use App\Services\Google\GoogleBusinessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGoogleBusinessInfoJob implements ShouldQueue
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
            Log::warning("SyncGoogleBusinessInfoJob: Location {$this->locationId} not found.");
            return;
        }

        $account = GoogleAccount::find($location->google_account_id);

        if (! $account) {
            Log::warning("SyncGoogleBusinessInfoJob: No GoogleAccount for location {$this->locationId}.");
            return;
        }

        try {
            $service = new GoogleBusinessService();
            $result  = $service->getLocation($account, $location->location_name);

            if (! $result['success']) {
                GoogleSyncLog::record(
                    $location->user_id,
                    'sync_business_info',
                    'error',
                    $location->id,
                    null,
                    $result['error']
                );
                return;
            }

            $loc = $result['data'];

            $location->update([
                'display_name'    => $loc['title'] ?? $location->display_name,
                'phone'           => $loc['phoneNumbers']['primaryPhone'] ?? $location->phone,
                'website'         => $loc['websiteUri'] ?? $location->website,
                'address_json'    => $loc['storefrontAddress'] ?? $location->address_json,
                'hours_json'      => $loc['regularHours'] ?? $location->hours_json,
                'description'     => $loc['profile']['description'] ?? $location->description,
                'categories_json' => $loc['categories'] ?? $location->categories_json,
                'is_verified'     => isset($loc['metadata']['isVerified']) ? (int) $loc['metadata']['isVerified'] : $location->is_verified,
                'last_sync_at'    => now(),
            ]);

            GoogleSyncLog::record(
                $location->user_id,
                'sync_business_info',
                'success',
                $location->id,
                200,
                'Refreshed location data'
            );
        } catch (\Throwable $e) {
            Log::error("SyncGoogleBusinessInfoJob failed for location {$this->locationId}: " . $e->getMessage());

            GoogleSyncLog::record(
                $location->user_id,
                'sync_business_info',
                'error',
                $location->id,
                null,
                $e->getMessage()
            );
        }
    }
}
