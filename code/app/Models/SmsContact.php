<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsContact extends Model
{
    protected $fillable = ['user_id', 'sms_contact_group_id', 'name', 'phone', 'email', 'custom_fields', 'is_subscribed'];

    protected $casts = ['custom_fields' => 'array', 'is_subscribed' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function group(): BelongsTo { return $this->belongsTo(SmsContactGroup::class, 'sms_contact_group_id'); }
}
