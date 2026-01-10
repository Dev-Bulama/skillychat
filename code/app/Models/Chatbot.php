<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Chatbot extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'uid',
        'user_id',
        'admin_id',
        'name',
        'description',
        'domain',
        'language',
        'tone',
        'welcome_message',
        'offline_message',
        'primary_color',
        'widget_position',
        'status',
        'settings',
        'emoji_support',
        'voice_support',
        'image_support',
        'human_takeover_enabled',
        'ai_provider',
        'ai_confidence_threshold',
        'total_conversations',
        'total_messages',
        'avg_response_time',
        'satisfaction_rating',
        'last_active_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'emoji_support' => 'boolean',
        'voice_support' => 'boolean',
        'image_support' => 'boolean',
        'human_takeover_enabled' => 'boolean',
        'ai_confidence_threshold' => 'decimal:2',
        'avg_response_time' => 'decimal:2',
        'satisfaction_rating' => 'decimal:2',
        'last_active_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = Str::uuid();
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function trainingData(): HasMany
    {
        return $this->hasMany(ChatbotTrainingData::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(ChatbotAgent::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ChatbotUsageLog::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ChatbotApiKey::class);
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

    public function scopeForDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canHandleVoice(): bool
    {
        return $this->voice_support;
    }

    public function canHandleImages(): bool
    {
        return $this->image_support;
    }

    public function canUseHumanTakeover(): bool
    {
        return $this->human_takeover_enabled;
    }

    public function canUseVoice(): bool
    {
        return $this->voice_support;
    }

    public function getEmbedCode(): string
    {
        $widgetUrl = asset('widget.js');
        $apiUrl = url('/api/chatbot');
        return "<script src=\"{$widgetUrl}\" data-chatbot-id=\"{$this->uid}\" data-api-url=\"{$apiUrl}\"></script>";
    }

    public function updateStats(array $stats): void
    {
        $this->update($stats);
    }
}
