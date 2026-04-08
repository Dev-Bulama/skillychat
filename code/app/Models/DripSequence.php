<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DripSequence extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function user()        { return $this->belongsTo(User::class); }
    public function steps()       { return $this->hasMany(DripStep::class, 'sequence_id')->orderBy('position'); }
    public function enrollments() { return $this->hasMany(DripEnrollment::class, 'sequence_id'); }

    public function isActive(): bool { return $this->status === 'active'; }
    public function isDraft(): bool  { return $this->status === 'draft'; }

    public function scopeActive($q)  { return $q->where('status', 'active'); }
}
