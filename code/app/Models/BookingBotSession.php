<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingBotSession extends Model
{
    protected $table = 'booking_bot_sessions';
    protected $guarded = [];
    protected $casts = ['data' => 'array', 'expires_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getData(string $key, mixed $default = null): mixed
    {
        return ($this->data ?? [])[$key] ?? $default;
    }

    public function setData(string $key, mixed $value): void
    {
        $data = $this->data ?? [];
        $data[$key] = $value;
        $this->update(['data' => $data, 'expires_at' => now()->addMinutes(30)]);
    }
}
