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
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('chatbot_id')->constrained('chatbots')->onDelete('cascade');
            $table->string('visitor_id')->index();
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_ip')->nullable();
            $table->string('visitor_location')->nullable();
            $table->text('visitor_user_agent')->nullable();
            $table->string('current_page_url')->nullable();
            $table->string('referrer_url')->nullable();
            $table->enum('status', ['ai_active', 'human_requested', 'human_active', 'resolved', 'closed'])->default('ai_active');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('chatbot_agents')->onDelete('set null');
            $table->timestamp('taken_over_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('total_messages')->default(0);
            $table->decimal('avg_ai_confidence', 3, 2)->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->boolean('satisfaction_rated')->default(false);
            $table->integer('satisfaction_score')->nullable();
            $table->json('metadata')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['chatbot_id', 'status']);
            $table->index(['assigned_agent_id', 'status']);
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};
