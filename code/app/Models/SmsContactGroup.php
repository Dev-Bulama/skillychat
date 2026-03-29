<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsContactGroup extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'contact_count'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function contacts(): HasMany { return $this->hasMany(SmsContact::class); }
    public function campaigns() { return $this->belongsToMany(SmsCampaign::class, 'sms_campaign_groups'); }
}
