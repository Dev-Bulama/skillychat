<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLeadActivity extends Model
{
    protected $guarded = [];

    public function lead() { return $this->belongsTo(CrmLead::class, 'lead_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
