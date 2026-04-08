<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppAccount extends Model
{
    protected $table = 'whatsapp_accounts';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->uid = $m->uid ?? (string) Str::uuid());
    }

    public function user() { return $this->belongsTo(User::class); }
    public function messages() { return $this->hasMany(WhatsAppMessage::class, 'account_id'); }
    public function chatbot() { return $this->belongsTo(Chatbot::class); }

    public function isActive(): bool { return $this->status == 1; }
    public function isConfigured(): bool
    {
        return !empty($this->phone_number_id) && !empty($this->access_token) && !empty($this->verify_token);
    }
}
