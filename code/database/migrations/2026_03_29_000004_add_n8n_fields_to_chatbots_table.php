<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->boolean('n8n_enabled')->default(false)->after('last_active_at');
            $table->text('n8n_webhook_url')->nullable()->after('n8n_enabled');
            $table->text('n8n_api_key')->nullable()->after('n8n_webhook_url'); // stored encrypted
            $table->json('n8n_extra_headers')->nullable()->after('n8n_api_key');
            $table->json('n8n_events')->nullable()->after('n8n_extra_headers'); // ['new_message','new_lead','campaign_sent']
        });
    }

    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn(['n8n_enabled', 'n8n_webhook_url', 'n8n_api_key', 'n8n_extra_headers', 'n8n_events']);
        });
    }
};
