<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class SmsProvider extends Model
{
    protected $fillable = ['user_id', 'name', 'provider', 'credentials', 'is_default', 'is_active'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaigns()
    {
        return $this->hasMany(SmsCampaign::class);
    }

    /** Store credentials encrypted */
    public function setCredentialsAttribute(array|string $value): void
    {
        $json = is_array($value) ? json_encode($value) : $value;
        $this->attributes['credentials'] = Crypt::encryptString($json);
    }

    /** Return credentials as array */
    public function getCredentialsAttribute(string $value): array
    {
        try {
            return json_decode(Crypt::decryptString($value), true) ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function getCredential(string $key, mixed $default = null): mixed
    {
        return $this->credentials[$key] ?? $default;
    }
}
