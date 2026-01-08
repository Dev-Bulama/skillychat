<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatbotTrainingData extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'chatbot_id',
        'type',
        'title',
        'content',
        'file_path',
        'file_type',
        'file_size',
        'source_url',
        'metadata',
        'embedding_vector',
        'chunk_index',
        'is_processed',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_processed' => 'boolean',
        'file_size' => 'integer',
        'chunk_index' => 'integer',
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }

    // Helper Methods
    public function isProcessed(): bool
    {
        return $this->is_processed;
    }

    public function markAsProcessed(): void
    {
        $this->update(['is_processed' => true, 'status' => 'active']);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function getFileSizeInMB(): float
    {
        return $this->file_size ? round($this->file_size / 1024 / 1024, 2) : 0;
    }
}
