<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('max_chatbots')->default(0)->after('is_feature');
            $table->integer('max_messages_per_month')->default(0)->after('max_chatbots');
            $table->integer('max_agents_per_chatbot')->default(0)->after('max_messages_per_month');
            $table->integer('training_data_size_mb')->default(0)->after('max_agents_per_chatbot');
            $table->boolean('chatbot_voice_enabled')->default(false)->after('training_data_size_mb');
            $table->boolean('chatbot_image_enabled')->default(false)->after('chatbot_voice_enabled');
            $table->boolean('chatbot_human_takeover_enabled')->default(false)->after('chatbot_image_enabled');
            $table->boolean('chatbot_analytics_enabled')->default(false)->after('chatbot_human_takeover_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'max_chatbots',
                'max_messages_per_month',
                'max_agents_per_chatbot',
                'training_data_size_mb',
                'chatbot_voice_enabled',
                'chatbot_image_enabled',
                'chatbot_human_takeover_enabled',
                'chatbot_analytics_enabled',
            ]);
        });
    }
};
