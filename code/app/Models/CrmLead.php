<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrmLead extends Model
{
    protected $guarded = [];

    protected $casts = ['follow_up_at' => 'datetime', 'last_contacted_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function user()       { return $this->belongsTo(User::class); }
    public function pipeline()   { return $this->belongsTo(CrmPipeline::class, 'pipeline_id'); }
    public function stage()      { return $this->belongsTo(CrmStage::class, 'stage_id'); }
    public function activities() { return $this->hasMany(CrmLeadActivity::class, 'lead_id')->latest(); }
    public function enrollments(){ return $this->hasMany(DripEnrollment::class, 'crm_lead_id'); }
    public function messages()   { return $this->hasMany(WhatsAppMessage::class, 'crm_lead_id'); }

    public function tagsArray(): array
    {
        return $this->tags ? array_filter(array_map('trim', explode(',', $this->tags))) : [];
    }

    public function isWon(): bool   { return $this->status === 'won'; }
    public function isLost(): bool  { return $this->status === 'lost'; }
}
