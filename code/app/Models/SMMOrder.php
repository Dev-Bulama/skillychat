<?php

namespace App\Models;

use App\Enums\SMMOrderStatus;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SMMOrder extends Model
{
    use HasFactory, Filterable;

    protected $table = 'smm_orders';
    protected $guarded = [];

    protected $casts = [
        'charge'        => 'decimal:4',
        'provider_cost' => 'decimal:4',
        'sent_to_provider_at' => 'datetime',
        'completed_at'        => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            $model->uid = Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SMMService::class, 'service_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SMMOrderLog::class, 'order_id');
    }

    public function statusEnum(): SMMOrderStatus
    {
        return SMMOrderStatus::from($this->status);
    }

    public function scopePending($query)
    {
        return $query->where('status', SMMOrderStatus::PENDING->value);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', SMMOrderStatus::PROCESSING->value);
    }

    public function isPending(): bool
    {
        return $this->status === SMMOrderStatus::PENDING->value;
    }

    public function isProcessing(): bool
    {
        return $this->status === SMMOrderStatus::PROCESSING->value;
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, [
            SMMOrderStatus::FAILED->value,
            SMMOrderStatus::PENDING->value,
        ]);
    }
}
