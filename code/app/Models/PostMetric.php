<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMetric extends Model
{
    protected $guarded = [];

    /**
     * Get the post that owns the metrics
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class, 'post_id');
    }
}
