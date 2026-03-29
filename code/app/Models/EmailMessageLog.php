<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailMessageLog extends Model
{
    protected $fillable = ['email_campaign_id', 'sms_contact_id', 'email', 'status', 'message_id', 'error', 'sent_at'];
    protected $casts    = ['sent_at' => 'datetime'];

    public function campaign(): BelongsTo { return $this->belongsTo(EmailCampaign::class, 'email_campaign_id'); }
    public function contact(): BelongsTo  { return $this->belongsTo(SmsContact::class, 'sms_contact_id'); }
}
