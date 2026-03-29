<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SMS Providers (per user, BYOP)
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');              // Human label
            $table->string('provider');          // termii | twilio | kudisms | custom
            $table->text('credentials');         // JSON, encrypted
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Contact groups
        Schema::create('sms_contact_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('contact_count')->default(0);
            $table->timestamps();
        });

        // Contacts
        Schema::create('sms_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sms_contact_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('is_subscribed')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'phone']);
        });

        // SMS Campaigns
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sms_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sender_id')->nullable();
            $table->text('message');             // supports {name}, {phone} etc.
            $table->string('status')->default('draft'); // draft|queued|processing|completed|failed|paused
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        // Campaign <-> Contact Groups pivot
        Schema::create('sms_campaign_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sms_contact_group_id')->constrained()->cascadeOnDelete();
        });

        // Per-message send log
        Schema::create('sms_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sms_contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->string('status')->default('pending'); // pending|sent|failed
            $table->text('response')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_message_logs');
        Schema::dropIfExists('sms_campaign_groups');
        Schema::dropIfExists('sms_campaigns');
        Schema::dropIfExists('sms_contacts');
        Schema::dropIfExists('sms_contact_groups');
        Schema::dropIfExists('sms_providers');
    }
};
