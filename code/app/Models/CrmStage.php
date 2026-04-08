<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrmStage extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function pipeline() { return $this->belongsTo(CrmPipeline::class, 'pipeline_id'); }
    public function leads()    { return $this->hasMany(CrmLead::class, 'stage_id'); }
}
