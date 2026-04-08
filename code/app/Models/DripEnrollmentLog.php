<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripEnrollmentLog extends Model
{
    protected $guarded = [];
    protected $casts   = ['sent_at' => 'datetime'];

    public function enrollment() { return $this->belongsTo(DripEnrollment::class, 'enrollment_id'); }
    public function step()       { return $this->belongsTo(DripStep::class, 'step_id'); }
}
