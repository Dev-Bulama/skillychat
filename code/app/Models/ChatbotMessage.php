<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatbotMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'conversation_id',
        'sender_type',
        'agent_id',
        'message',
        'message_type',
        'file_path',
        'file_type',
        'file_size',
        'attachments',
        'ai_confidence',
        'ai_provider',
        'ai_tokens_used',
        'ai_cost',
        'response_time',
        'is_internal_note',
        'is_read',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'ai_confidence' => 'decimal:2',
        'ai_cost' => 'decimal:6',
        'response_time' => 'decimal:2',
        'ai_tokens_used' => 'integer',
        'file_size' => 'integer',
        'is_internal_note' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
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
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(ChatbotAgent::class);
    }

    // Scopes
    public function scopeByConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    public function scopeFromVisitor($query)
    {
        return $query->where('sender_type', 'visitor');
    }

    public function scopeFromAI($query)
    {
        return $query->where('sender_type', 'ai');
    }

    public function scopeFromAgent($query)
    {
        return $query->where('sender_type', 'agent');
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal_note', true);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Helper Methods
    public function isFromVisitor(): bool
    {
        return $this->sender_type === 'visitor';
    }

    public function isFromAI(): bool
    {
        return $this->sender_type === 'ai';
    }

    public function isFromAgent(): bool
    {
        return $this->sender_type === 'agent';
    }

    public function isInternalNote(): bool
    {
        return $this->is_internal_note;
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function hasLowConfidence(float $threshold = 0.7): bool
    {
        return $this->ai_confidence && $this->ai_confidence < $threshold;
    }
}
