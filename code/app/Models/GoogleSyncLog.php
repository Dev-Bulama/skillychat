<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSyncLog extends Model
{
    protected $table = 'google_sync_logs';
    public    $timestamps = false;
    protected $guarded    = [];

    /**
     * Quick helper to record a log entry.
     */
    public static function record(
        int    $userId,
        string $action,
        string $status       = 'success',
        ?int   $locationId   = null,
        ?int   $responseCode = null,
        ?string $notes       = null
    ): void {
        static::create([
            'user_id'       => $userId,
            'location_id'   => $locationId,
            'action'        => $action,
            'status'        => $status,
            'response_code' => $responseCode,
            'notes'         => $notes,
            'created_at'    => now(),
        ]);
    }
}
