<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ChatbotApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chatbot_id',
        'provider',
        'key_name',
        'api_key',
        'api_secret',
        'is_default',
        'status',
        'last_verified_at',
        'total_requests',
        'failed_requests',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_default' => 'boolean',
        'last_verified_at' => 'datetime',
        'total_requests' => 'integer',
        'failed_requests' => 'integer',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Mutators
    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    public function getApiKeyAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function setApiSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['api_secret'] = Crypt::encryptString($value);
        }
    }

    public function getApiSecretAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'active',
            'last_verified_at' => now(),
        ]);
    }

    public function markAsInvalid(): void
    {
        $this->update(['status' => 'invalid']);
    }

    public function incrementRequests(): void
    {
        $this->increment('total_requests');
    }

    public function incrementFailedRequests(): void
    {
        $this->increment('failed_requests');
    }

    public function getFailureRate(): float
    {
        if ($this->total_requests == 0) {
            return 0;
        }
        return round(($this->failed_requests / $this->total_requests) * 100, 2);
    }

    public function getMaskedKey(): string
    {
        $key = $this->api_key;
        if (!$key) {
            return '****';
        }
        return substr($key, 0, 8) . '...' . substr($key, -4);
    }
}
