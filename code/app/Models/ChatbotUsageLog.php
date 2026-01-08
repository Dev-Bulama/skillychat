<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'conversation_id',
        'user_id',
        'ai_provider',
        'tokens_used',
        'cost',
        'messages_count',
        'usage_date',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'messages_count' => 'integer',
        'usage_date' => 'date',
    ];

    // Relationships
    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('usage_date', $date);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('usage_date', [$startDate, $endDate]);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('ai_provider', $provider);
    }

    // Helper Methods
    public function getTotalCost(): float
    {
        return (float) $this->cost;
    }
}
