<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppMessage extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function account() { return $this->belongsTo(WhatsAppAccount::class, 'account_id'); }
    public function user()    { return $this->belongsTo(User::class); }
    public function lead()    { return $this->belongsTo(CrmLead::class, 'crm_lead_id'); }

    public function isInbound(): bool  { return $this->direction === 'inbound'; }
    public function isOutbound(): bool { return $this->direction === 'outbound'; }
}
