<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('feature_flags')->where('name', 'google_business_profile')->exists();

        if (! $exists) {
            DB::table('feature_flags')->insert([
                'name'        => 'google_business_profile',
                'label'       => 'Google Business Profile',
                'description' => 'Enables the Google Business Profile integration — manage locations, reviews, posts and insights.',
                'is_enabled'  => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('feature_flags')->where('name', 'google_business_profile')->delete();
    }
};
