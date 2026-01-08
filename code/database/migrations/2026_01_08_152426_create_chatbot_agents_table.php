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
        Schema::create('chatbot_agents', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('chatbot_id')->constrained('chatbots')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->enum('role', ['admin', 'agent', 'viewer'])->default('agent');
            $table->enum('status', ['online', 'offline', 'busy', 'away'])->default('offline');
            $table->timestamp('last_active_at')->nullable();
            $table->integer('total_conversations_handled')->default(0);
            $table->decimal('avg_resolution_time', 8, 2)->default(0);
            $table->decimal('satisfaction_rating', 3, 2)->nullable();
            $table->boolean('can_takeover')->default(true);
            $table->boolean('auto_assign')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->index(['chatbot_id', 'status']);
            $table->index(['chatbot_id', 'role']);
            $table->unique(['chatbot_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_agents');
    }
};
