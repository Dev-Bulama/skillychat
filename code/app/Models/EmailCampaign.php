<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailCampaign extends Model
{
    protected $fillable = [
        'uid', 'user_id', 'email_provider_id', 'name', 'subject',
        'from_name', 'from_email', 'reply_to', 'body', 'content_type',
        'status', 'total_recipients', 'sent_count', 'failed_count',
        'scheduled_at', 'started_at', 'completed_at', 'failure_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->uid ??= Str::uuid());
    }

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function provider(): BelongsTo { return $this->belongsTo(EmailProvider::class, 'email_provider_id'); }
    public function groups()              { return $this->belongsToMany(SmsContactGroup::class, 'email_campaign_groups', 'email_campaign_id', 'sms_contact_group_id'); }
    public function logs(): HasMany       { return $this->hasMany(EmailMessageLog::class); }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    /** Personalize body and subject for a contact */
    public function personalize(SmsContact $contact): array
    {
        $replacements = [
            '{name}'  => $contact->name ?? '',
            '{phone}' => $contact->phone,
            '{email}' => $contact->email ?? '',
        ];

        return [
            'subject' => str_replace(array_keys($replacements), array_values($replacements), $this->subject),
            'body'    => str_replace(array_keys($replacements), array_values($replacements), $this->body),
        ];
    }
}
