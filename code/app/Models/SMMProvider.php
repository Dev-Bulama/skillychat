<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SMMProvider extends Model
{
    use HasFactory, Filterable;

    protected $table = 'smm_providers';
    protected $guarded = [];

    protected $casts = [
        'extra_config' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            $model->uid = Str::uuid();
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name . '-' . Str::random(5));
            }
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(SMMService::class, 'provider_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnum::true->status());
    }

    public function isActive(): bool
    {
        return $this->status === StatusEnum::true->status();
    }
}
