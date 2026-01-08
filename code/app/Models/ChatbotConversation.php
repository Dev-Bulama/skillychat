<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'chatbot_id',
        'visitor_id',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'visitor_ip',
        'visitor_location',
        'visitor_user_agent',
        'current_page_url',
        'referrer_url',
        'status',
        'assigned_agent_id',
        'taken_over_at',
        'resolved_at',
        'last_message_at',
        'total_messages',
        'avg_ai_confidence',
        'sentiment',
        'satisfaction_rated',
        'satisfaction_score',
        'metadata',
        'internal_notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'taken_over_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_message_at' => 'datetime',
        'total_messages' => 'integer',
        'avg_ai_confidence' => 'decimal:2',
        'satisfaction_rated' => 'boolean',
        'satisfaction_score' => 'integer',
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

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(ChatbotAgent::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id');
    }

    // Scopes
    public function scopeAiActive($query)
    {
        return $query->where('status', 'ai_active');
    }

    public function scopeHumanRequested($query)
    {
        return $query->where('status', 'human_requested');
    }

    public function scopeHumanActive($query)
    {
        return $query->where('status', 'human_active');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }

    public function scopeAssignedTo($query, $agentId)
    {
        return $query->where('assigned_agent_id', $agentId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_agent_id');
    }

    // Helper Methods
    public function isAiActive(): bool
    {
        return $this->status === 'ai_active';
    }

    public function isHumanActive(): bool
    {
        return $this->status === 'human_active';
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function requestHumanTakeover(): void
    {
        $this->update(['status' => 'human_requested']);
    }

    public function assignToAgent(ChatbotAgent $agent): void
    {
        $this->update([
            'assigned_agent_id' => $agent->id,
            'status' => 'human_active',
            'taken_over_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function resumeAI(): void
    {
        $this->update([
            'status' => 'ai_active',
            'assigned_agent_id' => null,
        ]);
    }

    public function addMessage(array $messageData): ChatbotMessage
    {
        $message = $this->messages()->create($messageData);

        $this->increment('total_messages');
        $this->update(['last_message_at' => now()]);

        return $message;
    }

    public function rateSatisfaction(int $score): void
    {
        $this->update([
            'satisfaction_rated' => true,
            'satisfaction_score' => $score,
        ]);
    }
}
