<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DripStep extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function sequence() { return $this->belongsTo(DripSequence::class, 'sequence_id'); }
    public function logs()     { return $this->hasMany(DripEnrollmentLog::class, 'step_id'); }

    public function delayDescription(): string
    {
        $parts = [];
        if ($this->delay_days)  $parts[] = $this->delay_days . ' day(s)';
        if ($this->delay_hours) $parts[] = $this->delay_hours . ' hour(s)';
        return $parts ? 'Wait ' . implode(' ', $parts) . ' then send' : 'Send immediately';
    }
}
