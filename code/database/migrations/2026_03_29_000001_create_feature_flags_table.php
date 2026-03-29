<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Seed default flags
        DB::table('feature_flags')->insert([
            ['name' => 'social_media', 'label' => 'Social Media Features', 'description' => 'Enable/disable social media post and account features for all users.', 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'bulk_sms',     'label' => 'Bulk SMS',             'description' => 'Enable/disable bulk SMS campaign features for all users.',           'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'bulk_email',   'label' => 'Bulk Email',           'description' => 'Enable/disable bulk email campaign features for all users.',         'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'n8n_automation','label' => 'n8n Automation',      'description' => 'Enable/disable n8n webhook automation per chatbot.',                 'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ai_chatbot',   'label' => 'AI Chatbot',           'description' => 'Enable/disable AI chatbot features for all users.',                  'is_enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
