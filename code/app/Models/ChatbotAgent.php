<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatbotAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'chatbot_id',
        'user_id',
        'admin_id',
        'name',
        'email',
        'role',
        'status',
        'last_active_at',
        'total_conversations_handled',
        'avg_resolution_time',
        'satisfaction_rating',
        'can_takeover',
        'auto_assign',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_active_at' => 'datetime',
        'total_conversations_handled' => 'integer',
        'avg_resolution_time' => 'decimal:2',
        'satisfaction_rating' => 'decimal:2',
        'can_takeover' => 'boolean',
        'auto_assign' => 'boolean',
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
    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'agent_id');
    }

    // Scopes
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['online', 'away']);
    }

    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }

    public function scopeCanTakeover($query)
    {
        return $query->where('can_takeover', true);
    }

    public function scopeAutoAssign($query)
    {
        return $query->where('auto_assign', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Helper Methods
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function isAvailable(): bool
    {
        return in_array($this->status, ['online', 'away']);
    }

    public function canHandleConversations(): bool
    {
        return $this->can_takeover && $this->isAvailable();
    }

    public function setOnline(): void
    {
        $this->update([
            'status' => 'online',
            'last_active_at' => now(),
        ]);
    }

    public function setOffline(): void
    {
        $this->update(['status' => 'offline']);
    }

    public function setAway(): void
    {
        $this->update(['status' => 'away']);
    }

    public function setBusy(): void
    {
        $this->update(['status' => 'busy']);
    }

    public function updateActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    public function incrementHandled(): void
    {
        $this->increment('total_conversations_handled');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }
}
