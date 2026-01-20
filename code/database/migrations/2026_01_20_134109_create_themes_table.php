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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('version', 20)->default('1.0.0');
            $table->string('author', 100)->nullable();
            $table->string('author_url', 255)->nullable();
            $table->string('preview_image', 255)->nullable();
            $table->json('screenshots')->nullable();
            $table->json('config')->nullable(); // Theme configuration (colors, fonts, etc.)
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false); // System themes cannot be deleted
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index(['status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
