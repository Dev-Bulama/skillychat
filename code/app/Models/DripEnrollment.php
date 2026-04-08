<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripEnrollment extends Model
{
    protected $guarded = [];
    protected $casts   = ['next_send_at' => 'datetime', 'enrolled_at' => 'datetime', 'completed_at' => 'datetime'];

    public function sequence() { return $this->belongsTo(DripSequence::class, 'sequence_id'); }
    public function user()     { return $this->belongsTo(User::class); }
    public function lead()     { return $this->belongsTo(CrmLead::class, 'crm_lead_id'); }
    public function logs()     { return $this->hasMany(DripEnrollmentLog::class, 'enrollment_id')->latest(); }

    public function isActive(): bool    { return $this->status === 'active'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
}
