<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('google_email')->nullable();
            $table->string('google_sub')->nullable()->index();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expiry')->nullable();
            $table->enum('status', ['connected', 'disconnected', 'expired'])->default('connected');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('google_business_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('google_account_id');
            $table->string('account_name')->nullable();
            $table->string('location_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website', 500)->nullable();
            $table->json('address_json')->nullable();
            $table->json('hours_json')->nullable();
            $table->text('description')->nullable();
            $table->json('categories_json')->nullable();
            $table->tinyInteger('is_verified')->default(0);
            $table->tinyInteger('is_selected')->default(0);
            $table->dateTime('last_sync_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');
        });

        Schema::create('google_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('review_name')->unique();
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_photo', 500)->nullable();
            $table->tinyInteger('star_rating')->default(1);
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->dateTime('update_time')->nullable();
            $table->dateTime('reply_time')->nullable();
            $table->text('ai_draft')->nullable();
            $table->enum('status', ['pending', 'replied', 'ignored'])->default('pending');
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('google_business_locations')->onDelete('cascade');
        });

        Schema::create('google_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('post_name')->nullable();
            $table->string('topic_type', 50)->default('STANDARD');
            $table->text('summary')->nullable();
            $table->string('call_to_action_type', 50)->nullable();
            $table->string('call_to_action_url', 500)->nullable();
            $table->string('state', 50)->default('LIVE');
            $table->dateTime('create_time')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('google_business_locations')->onDelete('cascade');
        });

        Schema::create('google_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('action', 100);
            $table->enum('status', ['success', 'error'])->default('success');
            $table->smallInteger('response_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sync_logs');
        Schema::dropIfExists('google_posts');
        Schema::dropIfExists('google_reviews');
        Schema::dropIfExists('google_business_locations');
        Schema::dropIfExists('google_accounts');
    }
};
