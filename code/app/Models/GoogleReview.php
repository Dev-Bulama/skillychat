<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleReview extends Model
{
    protected $table = 'google_reviews';
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(GoogleLocation::class, 'location_id');
    }

    /**
     * Return a human-readable star label.
     */
    public function starLabel(): string
    {
        $map = [
            1 => 'ONE',
            2 => 'TWO',
            3 => 'THREE',
            4 => 'FOUR',
            5 => 'FIVE',
        ];

        return $map[$this->star_rating] ?? 'ONE';
    }

    /**
     * Numeric star value from Google string enum (STAR_RATING_UNSPECIFIED, ONE … FIVE).
     */
    public static function starFromString(string $rating): int
    {
        $map = [
            'ONE'   => 1,
            'TWO'   => 2,
            'THREE' => 3,
            'FOUR'  => 4,
            'FIVE'  => 5,
        ];

        return $map[$rating] ?? 1;
    }
}
