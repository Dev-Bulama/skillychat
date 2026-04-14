<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GooglePost extends Model
{
    protected $table = 'google_posts';
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(GoogleLocation::class, 'location_id');
    }
}
