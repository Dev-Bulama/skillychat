<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── WhatsApp Accounts ─────────────────────────────────────────────────
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone_number')->nullable();          // display number e.g. +2348012345678
            $table->string('phone_number_id')->nullable();       // Meta phone number ID
            $table->string('waba_id')->nullable();               // WhatsApp Business Account ID
            $table->text('access_token')->nullable();            // Meta permanent / long-lived token
            $table->string('verify_token')->nullable();          // Our webhook verify token
            $table->string('app_id')->nullable();                // Meta App ID
            $table->string('app_secret')->nullable();            // Meta App Secret (stored encrypted)
            $table->text('welcome_message')->nullable();
            $table->text('fallback_message')->nullable();
            $table->tinyInteger('status')->default(1);           // 1=active 0=inactive
            $table->tinyInteger('ai_enabled')->default(0);       // route messages to AI engine
            $table->foreignId('chatbot_id')->nullable()->constrained('chatbots')->nullOnDelete();
            $table->timestamps();
        });

        // ── WhatsApp Messages ─────────────────────────────────────────────────
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('account_id')->constrained('whatsapp_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('wa_message_id')->nullable()->index(); // Meta message ID
            $table->string('from_number');
            $table->string('to_number');
            $table->string('contact_name')->nullable();
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('type', ['text', 'image', 'audio', 'document', 'interactive', 'template'])->default('text');
            $table->text('content');
            $table->string('media_url')->nullable();
            $table->enum('status', ['received', 'sent', 'delivered', 'read', 'failed'])->default('received');
            $table->unsignedBigInteger('crm_lead_id')->nullable()->index(); // FK added after crm_leads is created
            $table->timestamps();
        });

        // ── CRM Pipelines ─────────────────────────────────────────────────────
        Schema::create('crm_pipelines', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('is_default')->default(0);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // ── CRM Stages ────────────────────────────────────────────────────────
        Schema::create('crm_stages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('pipeline_id')->constrained('crm_pipelines')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->default('#6c757d');
            $table->integer('position')->default(0);
            $table->tinyInteger('is_won')->default(0);   // marks a "won/converted" stage
            $table->tinyInteger('is_lost')->default(0);  // marks a "lost" stage
            $table->timestamps();
        });

        // ── CRM Leads ─────────────────────────────────────────────────────────
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pipeline_id')->constrained('crm_pipelines')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('crm_stages')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->decimal('deal_value', 15, 2)->default(0);
            $table->enum('source', ['manual', 'chatbot', 'whatsapp', 'form', 'import', 'api'])->default('manual');
            $table->string('tags')->nullable();          // comma-separated tags
            $table->text('notes')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['active', 'won', 'lost', 'archived'])->default('active');
            $table->foreignId('chatbot_conversation_id')->nullable()->constrained('chatbot_conversations')->nullOnDelete();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamps();
        });

        // ── CRM Lead Activities ───────────────────────────────────────────────
        Schema::create('crm_lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['note', 'email', 'sms', 'whatsapp', 'call', 'stage_change', 'drip_enrolled', 'system'])->default('note');
            $table->string('title')->nullable();
            $table->text('description');
            $table->string('meta')->nullable();          // JSON extra data
            $table->timestamps();
        });

        // ── Drip Sequences ────────────────────────────────────────────────────
        Schema::create('drip_sequences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', [
                'manual',           // admin manually enrolls contact
                'lead_created',     // new CRM lead added
                'tag_added',        // tag applied to lead
                'chatbot_end',      // chatbot conversation ends
                'whatsapp_optin',   // user messages WA account
            ])->default('manual');
            $table->string('trigger_value')->nullable(); // e.g. the specific tag name
            $table->enum('status', ['draft', 'active', 'paused'])->default('draft');
            $table->integer('total_enrolled')->default(0);
            $table->integer('total_completed')->default(0);
            $table->timestamps();
        });

        // ── Drip Steps ────────────────────────────────────────────────────────
        Schema::create('drip_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('sequence_id')->constrained('drip_sequences')->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->enum('channel', ['email', 'sms', 'whatsapp'])->default('email');
            $table->string('subject')->nullable();       // email subject / WA template name
            $table->text('message');                     // body of message
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // ── Drip Enrollments ─────────────────────────────────────────────────
        Schema::create('drip_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('drip_sequences')->cascadeOnDelete();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable()->index();
            $table->string('contact_phone')->nullable();
            $table->foreignId('crm_lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->integer('current_step')->default(0);  // index of last completed step
            $table->enum('status', ['active', 'completed', 'unsubscribed', 'failed'])->default('active');
            $table->timestamp('next_send_at')->nullable()->index();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // ── Drip Enrollment Logs ──────────────────────────────────────────────
        Schema::create('drip_enrollment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('drip_enrollments')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('drip_steps')->cascadeOnDelete();
            $table->enum('status', ['sent', 'failed', 'skipped'])->default('sent');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_enrollment_logs');
        Schema::dropIfExists('drip_enrollments');
        Schema::dropIfExists('drip_steps');
        Schema::dropIfExists('drip_sequences');
        Schema::dropIfExists('crm_lead_activities');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_stages');
        Schema::dropIfExists('crm_pipelines');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_accounts');
    }
};
