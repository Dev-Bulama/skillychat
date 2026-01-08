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
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('conversation_id')->constrained('chatbot_conversations')->onDelete('cascade');
            $table->enum('sender_type', ['visitor', 'ai', 'agent'])->default('visitor');
            $table->foreignId('agent_id')->nullable()->constrained('chatbot_agents')->onDelete('set null');
            $table->longText('message');
            $table->enum('message_type', ['text', 'image', 'file', 'emoji', 'voice'])->default('text');
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->json('attachments')->nullable();
            $table->decimal('ai_confidence', 3, 2)->nullable();
            $table->string('ai_provider')->nullable();
            $table->integer('ai_tokens_used')->nullable();
            $table->decimal('ai_cost', 10, 6)->nullable();
            $table->decimal('response_time', 8, 2)->nullable();
            $table->boolean('is_internal_note')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'sender_type']);
            $table->index(['conversation_id', 'created_at']);
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
