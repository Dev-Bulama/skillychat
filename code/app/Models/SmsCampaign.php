<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SmsCampaign extends Model
{
    protected $fillable = [
        'uid', 'user_id', 'sms_provider_id', 'name', 'sender_id', 'message',
        'status', 'total_recipients', 'sent_count', 'failed_count',
        'scheduled_at', 'started_at', 'completed_at', 'failure_reason',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->uid ??= Str::uuid());
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function provider(): BelongsTo { return $this->belongsTo(SmsProvider::class, 'sms_provider_id'); }
    public function groups() { return $this->belongsToMany(SmsContactGroup::class, 'sms_campaign_groups'); }
    public function logs(): HasMany { return $this->hasMany(SmsMessageLog::class); }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    /** Personalize message for a contact */
    public function personalize(SmsContact $contact): string
    {
        return str_replace(
            ['{name}', '{phone}', '{email}'],
            [$contact->name ?? '', $contact->phone, $contact->email ?? ''],
            $this->message
        );
    }
}
