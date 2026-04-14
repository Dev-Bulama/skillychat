<?php

use App\Models\FeatureFlag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $flags = [
        [
            'name'        => 'appointments',
            'label'       => 'Appointment Booking',
            'description' => 'Allow users to manage appointments. Includes booking bot integration for WhatsApp — contacts type "book" to start the automated booking flow.',
            'is_enabled'  => true,
        ],
        [
            'name'        => 'google_business_profile',
            'label'       => 'Google Business Profile',
            'description' => 'Connect and manage Google Business Profile listings — business info, reviews, posts, and insights.',
            'is_enabled'  => true,
        ],
    ];

    public function up(): void
    {
        foreach ($this->flags as $flag) {
            FeatureFlag::firstOrCreate(['name' => $flag['name']], $flag);
        }
    }

    public function down(): void
    {
        FeatureFlag::whereIn('name', array_column($this->flags, 'name'))->delete();
    }
};
