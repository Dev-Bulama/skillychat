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
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('domain')->nullable();
            $table->string('language')->default('en');
            $table->string('tone')->default('professional');
            $table->text('welcome_message')->nullable();
            $table->text('offline_message')->nullable();
            $table->string('primary_color')->default('#0084ff');
            $table->string('widget_position')->default('bottom-right');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('settings')->nullable();
            $table->boolean('emoji_support')->default(true);
            $table->boolean('voice_support')->default(false);
            $table->boolean('image_support')->default(false);
            $table->boolean('human_takeover_enabled')->default(true);
            $table->string('ai_provider')->default('openai');
            $table->decimal('ai_confidence_threshold', 3, 2)->default(0.70);
            $table->integer('total_conversations')->default(0);
            $table->integer('total_messages')->default(0);
            $table->decimal('avg_response_time', 8, 2)->default(0);
            $table->decimal('satisfaction_rating', 3, 2)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbots');
    }
};
