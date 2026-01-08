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
        Schema::create('chatbot_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('chatbots')->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained('chatbot_conversations')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('ai_provider')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->integer('messages_count')->default(0);
            $table->date('usage_date');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['chatbot_id', 'usage_date']);
            $table->index(['user_id', 'usage_date']);
            $table->index('usage_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_usage_logs');
    }
};
