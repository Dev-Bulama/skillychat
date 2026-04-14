<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Appointment Services (what can be booked) ─────────────────────────
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');                        // "30-min Consultation"
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->decimal('price', 10, 2)->nullable();   // null = free
            $table->string('currency', 10)->default('USD');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

        // ── Appointments ───────────────────────────────────────────────────────
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('appointment_services')->nullOnDelete();
            $table->foreignId('crm_lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->string('contact_name');
            $table->string('contact_email')->nullable()->index();
            $table->string('contact_phone')->nullable()->index();
            $table->string('whatsapp_number')->nullable()->index(); // for bot-booked appointments
            $table->dateTime('appointment_at');               // booked date + time (UTC)
            $table->string('timezone', 50)->default('UTC');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])
                  ->default('pending');
            $table->tinyInteger('reminder_24h_sent')->default(0);
            $table->tinyInteger('reminder_1h_sent')->default(0);
            $table->tinyInteger('confirmation_sent')->default(0);
            $table->string('booked_via', 30)->default('dashboard'); // dashboard|whatsapp|web
            $table->string('cancellation_token', 64)->nullable()->unique(); // for cancel link in email
            $table->timestamps();
        });

        // ── Booking Bot Sessions (tracks multi-step conversation state) ────────
        Schema::create('booking_bot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('phone_number', 30)->index(); // caller's phone
            $table->string('step', 50)->default('ask_service'); // current step in flow
            $table->json('data')->nullable();                    // accumulated data: service, date, time, name, email
            $table->timestamp('expires_at')->nullable();         // abandon after 30 mins of inactivity
            $table->timestamps();

            $table->unique(['user_id', 'phone_number']);
        });

        // ── Working Hours ─────────────────────────────────────────────────────
        Schema::create('appointment_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Sun, 1=Mon … 6=Sat
            $table->tinyInteger('is_open')->default(1);
            $table->time('open_time')->default('09:00:00');
            $table->time('close_time')->default('17:00:00');
            $table->unique(['user_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_bot_sessions');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('appointment_working_hours');
        Schema::dropIfExists('appointment_services');
    }
};
