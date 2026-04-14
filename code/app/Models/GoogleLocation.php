<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleLocation extends Model
{
    protected $table = 'google_business_locations';
    protected $guarded = [];

    protected $casts = [
        'address_json'    => 'array',
        'hours_json'      => 'array',
        'categories_json' => 'array',
        'is_verified'     => 'boolean',
        'is_selected'     => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function googleAccount()
    {
        return $this->belongsTo(GoogleAccount::class, 'google_account_id');
    }

    public function reviews()
    {
        return $this->hasMany(GoogleReview::class, 'location_id');
    }

    public function posts()
    {
        return $this->hasMany(GooglePost::class, 'location_id');
    }

    /**
     * Build a human-readable address string from the stored JSON.
     */
    public function getAddressStringAttribute(): string
    {
        $addr = $this->address_json;
        if (empty($addr)) {
            return '';
        }

        $parts = [];

        if (! empty($addr['addressLines'])) {
            $parts = array_merge($parts, (array) $addr['addressLines']);
        }

        if (! empty($addr['locality'])) {
            $parts[] = $addr['locality'];
        }

        if (! empty($addr['administrativeArea'])) {
            $parts[] = $addr['administrativeArea'];
        }

        if (! empty($addr['postalCode'])) {
            $parts[] = $addr['postalCode'];
        }

        if (! empty($addr['regionCode'])) {
            $parts[] = $addr['regionCode'];
        }

        return implode(', ', array_filter($parts));
    }
}
