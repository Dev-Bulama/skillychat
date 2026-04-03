<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SMMService extends Model
{
    use HasFactory, Filterable;

    protected $table = 'smm_services';
    protected $guarded = [];

    protected $casts = [
        'price_per_1000' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            $model->uid = Str::uuid();
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SMMProvider::class, 'provider_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(SMMOrder::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnum::true->status());
    }

    /**
     * Calculate charge for a given quantity.
     */
    public function calculateCharge(int $quantity): float
    {
        return round(($this->price_per_1000 / 1000) * $quantity, 4);
    }

    public function isActive(): bool
    {
        return $this->status === StatusEnum::true->status();
    }
}
