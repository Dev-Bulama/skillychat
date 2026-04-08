<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrmPipeline extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function user()   { return $this->belongsTo(User::class); }
    public function stages() { return $this->hasMany(CrmStage::class, 'pipeline_id')->orderBy('position'); }
    public function leads()  { return $this->hasMany(CrmLead::class, 'pipeline_id'); }
}
