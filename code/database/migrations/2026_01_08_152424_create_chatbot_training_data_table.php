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
        Schema::create('chatbot_training_data', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('chatbot_id')->constrained('chatbots')->onDelete('cascade');
            $table->enum('type', ['text', 'file', 'url', 'faq', 'conversation'])->default('text');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('source_url')->nullable();
            $table->json('metadata')->nullable();
            $table->text('embedding_vector')->nullable();
            $table->integer('chunk_index')->default(0);
            $table->boolean('is_processed')->default(false);
            $table->enum('status', ['active', 'inactive', 'processing', 'failed'])->default('active');
            $table->timestamps();
            $table->index(['chatbot_id', 'type', 'status']);
            $table->index('is_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_training_data');
    }
};
