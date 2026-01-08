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
        Schema::create('chatbot_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chatbot_id')->nullable()->constrained('chatbots')->onDelete('cascade');
            $table->enum('provider', ['openai', 'gemini', 'claude', 'custom'])->default('openai');
            $table->string('key_name')->nullable();
            $table->text('api_key');
            $table->text('api_secret')->nullable();
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive', 'invalid'])->default('active');
            $table->timestamp('last_verified_at')->nullable();
            $table->integer('total_requests')->default(0);
            $table->integer('failed_requests')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'provider', 'status']);
            $table->index(['chatbot_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_api_keys');
    }
};
