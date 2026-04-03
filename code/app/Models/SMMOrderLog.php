<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SMMOrderLog extends Model
{
    use HasFactory;

    protected $table = 'smm_order_logs';
    protected $guarded = [];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'success'          => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SMMOrder::class, 'order_id');
    }
}
