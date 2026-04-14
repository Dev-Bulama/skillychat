<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppointmentService extends Model
{
    protected $table = 'appointment_services';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function user()         { return $this->belongsTo(User::class); }
    public function appointments() { return $this->hasMany(Appointment::class, 'service_id'); }

    public function formattedPrice(): string
    {
        if ($this->price === null) return 'Free';
        return $this->currency . ' ' . number_format($this->price, 2);
    }
}
