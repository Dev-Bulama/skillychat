<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $guarded = [];
    protected $casts = ['appointment_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            $m->uid = $m->uid ?? (string) Str::uuid();
            $m->cancellation_token = $m->cancellation_token ?? Str::random(64);
        });
    }

    public function user()    { return $this->belongsTo(User::class); }
    public function service() { return $this->belongsTo(AppointmentService::class, 'service_id'); }
    public function lead()    { return $this->belongsTo(CrmLead::class, 'crm_lead_id'); }

    public function isPending():   bool { return $this->status === 'pending'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'confirmed'  => 'bg-success',
            'cancelled'  => 'bg-danger',
            'completed'  => 'bg-secondary',
            'no_show'    => 'bg-warning text-dark',
            default      => 'bg-info text-dark',
        };
    }
}
