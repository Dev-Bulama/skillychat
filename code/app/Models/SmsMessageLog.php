<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessageLog extends Model
{
    protected $fillable = ['sms_campaign_id', 'sms_contact_id', 'phone', 'status', 'response', 'error', 'sent_at'];
    protected $casts = ['sent_at' => 'datetime'];

    public function campaign(): BelongsTo { return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id'); }
    public function contact(): BelongsTo  { return $this->belongsTo(SmsContact::class, 'sms_contact_id'); }
}
