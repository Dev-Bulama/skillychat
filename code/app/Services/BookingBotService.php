<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\BookingBotSession;
use App\Models\User;
use App\Models\WhatsAppAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Handles multi-step booking bot conversation flow over WhatsApp.
 *
 * Steps:
 *  ask_service → ask_date → ask_time → ask_name → ask_email → confirm
 *
 * Triggered by keywords: book, appointment, schedule, reserve, booking
 */
class BookingBotService
{
    private const TRIGGER_KEYWORDS = ['book', 'appointment', 'schedule', 'reserve', 'booking', 'appoint'];

    /**
     * Check if the incoming message should trigger the booking bot.
     */
    public static function isTrigger(string $message, WhatsAppAccount $account): bool
    {
        // Only handle if user has at least one active service
        if (!AppointmentService::where('user_id', $account->user_id)->where('is_active', 1)->exists()) {
            return false;
        }

        $lower = strtolower(trim($message));
        foreach (self::TRIGGER_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) return true;
        }
        return false;
    }

    /**
     * Check if this phone number has an active booking session.
     */
    public static function hasActiveSession(string $phone, WhatsAppAccount $account): bool
    {
        $session = BookingBotSession::where('user_id', $account->user_id)
            ->where('phone_number', $phone)
            ->first();

        if (!$session) return false;
        if ($session->isExpired()) {
            $session->delete();
            return false;
        }
        return true;
    }

    /**
     * Start a new booking session and return the first question.
     */
    public static function startSession(string $phone, WhatsAppAccount $account): string
    {
        // Clear any old session
        BookingBotSession::where('user_id', $account->user_id)
            ->where('phone_number', $phone)
            ->delete();

        BookingBotSession::create([
            'user_id'     => $account->user_id,
            'phone_number'=> $phone,
            'step'        => 'ask_service',
            'data'        => [],
            'expires_at'  => now()->addMinutes(30),
        ]);

        return self::askService($account->user_id);
    }

    /**
     * Continue an existing booking session with the user's reply.
     * Returns the next bot message.
     */
    public static function handleReply(string $phone, string $message, WhatsAppAccount $account): string
    {
        $session = BookingBotSession::where('user_id', $account->user_id)
            ->where('phone_number', $phone)
            ->first();

        if (!$session || $session->isExpired()) {
            return self::startSession($phone, $account);
        }

        $message = trim($message);

        // Allow cancel at any step
        if (strtolower($message) === 'cancel' || strtolower($message) === 'stop') {
            $session->delete();
            return 'Your booking has been cancelled. Type "book" anytime to start again.';
        }

        return match ($session->step) {
            'ask_service' => self::processService($session, $message),
            'ask_date'    => self::processDate($session, $message),
            'ask_time'    => self::processTime($session, $message),
            'ask_name'    => self::processName($session, $message, $phone, $account),
            'ask_email'   => self::processEmail($session, $message, $phone, $account),
            default       => self::startSession($phone, $account),
        };
    }

    // ── Step handlers ────────────────────────────────────────────────────────

    private static function askService(int $userId): string
    {
        $services = AppointmentService::where('user_id', $userId)->where('is_active', 1)->get();
        $list = $services->map(fn($s, $i) => ($i + 1) . '. ' . $s->name .
            ($s->duration_minutes ? " ({$s->duration_minutes} min)" : '') .
            ($s->price !== null ? ' — ' . $s->formattedPrice() : '')
        )->join("\n");

        return "Hi! I can help you book an appointment.\n\nAvailable services:\n{$list}\n\nReply with the number of the service you'd like (e.g. 1), or type *cancel* to stop.";
    }

    private static function processService(BookingBotSession $session, string $input): string
    {
        $services = AppointmentService::where('user_id', $session->user_id)
            ->where('is_active', 1)
            ->get();

        $idx = (int) $input - 1;
        if ($idx < 0 || $idx >= $services->count()) {
            return "Please reply with a number between 1 and {$services->count()}.";
        }

        $service = $services->get($idx);
        $session->update(['step' => 'ask_date']);
        $session->setData('service_id', $service->id);
        $session->setData('service_name', $service->name);

        return "Great choice: *{$service->name}*!\n\nWhat date would you prefer? Reply in format *DD/MM/YYYY* (e.g. " . now()->addDay()->format('d/m/Y') . ')';
    }

    private static function processDate(BookingBotSession $session, string $input): string
    {
        try {
            $date = Carbon::createFromFormat('d/m/Y', $input);
            if ($date->isPast() && !$date->isToday()) {
                return "That date has already passed. Please enter a future date in format DD/MM/YYYY.";
            }
        } catch (\Exception $e) {
            return "I didn't understand that date. Please reply in format *DD/MM/YYYY* (e.g. " . now()->addDay()->format('d/m/Y') . ')';
        }

        $session->update(['step' => 'ask_time']);
        $session->setData('date', $date->format('Y-m-d'));
        $session->setData('date_display', $date->format('l, F j, Y'));

        return "What time would you prefer on {$date->format('l, F j')}?\nReply in format *HH:MM* (e.g. 10:00 or 14:30)";
    }

    private static function processTime(BookingBotSession $session, string $input): string
    {
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $input, $m)) {
            return "I didn't understand that time. Please reply in format *HH:MM* (e.g. 10:00 or 14:30)";
        }

        $session->update(['step' => 'ask_name']);
        $session->setData('time', $input);

        // Format for display
        try {
            $display = Carbon::createFromFormat('H:i', $input)->format('g:i A');
            $session->setData('time_display', $display);
        } catch (\Exception) {
            $session->setData('time_display', $input);
        }

        return "Got it! What is your name?";
    }

    private static function processName(BookingBotSession $session, string $input, string $phone, WhatsAppAccount $account): string
    {
        if (strlen($input) < 2 || strlen($input) > 100) {
            return "Please enter your full name (2-100 characters).";
        }

        $session->update(['step' => 'ask_email']);
        $session->setData('name', $input);

        return "Nice to meet you, {$input}!\n\nWhat is your email address? (Reply *skip* to skip)";
    }

    private static function processEmail(BookingBotSession $session, string $input, string $phone, WhatsAppAccount $account): string
    {
        $email = null;
        if (strtolower($input) !== 'skip') {
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return "That doesn't look like a valid email. Please try again or reply *skip* to skip.";
            }
            $email = $input;
        }

        // All data collected — create the appointment
        try {
            $data = $session->data;
            $dateTime = Carbon::parse($data['date'] . ' ' . $data['time']);

            $appointment = Appointment::create([
                'user_id'        => $session->user_id,
                'service_id'     => $data['service_id'] ?? null,
                'contact_name'   => $data['name'],
                'contact_email'  => $email,
                'contact_phone'  => $phone,
                'whatsapp_number'=> $phone,
                'appointment_at' => $dateTime,
                'status'         => 'confirmed',
                'booked_via'     => 'whatsapp',
            ]);

            $session->delete();

            // Queue confirmation + reminder jobs
            \App\Jobs\SendAppointmentConfirmationJob::dispatch($appointment->id);

            $serviceName = $data['service_name'] ?? 'Appointment';
            $dateDisplay = $data['date_display'] ?? $dateTime->format('l, F j, Y');
            $timeDisplay = $data['time_display'] ?? $data['time'];

            $summary = "✅ *Booking Confirmed!*\n\n"
                . "📋 *Service:* {$serviceName}\n"
                . "📅 *Date:* {$dateDisplay}\n"
                . "🕐 *Time:* {$timeDisplay}\n"
                . "👤 *Name:* {$data['name']}\n"
                . ($email ? "📧 *Email:* {$email}\n" : "")
                . "\nYou'll receive a reminder 24 hours before your appointment. "
                . "To cancel, reply *cancel appointment {$appointment->uid}*.";

            return $summary;

        } catch (\Throwable $e) {
            Log::error('BookingBot: failed to create appointment', ['error' => $e->getMessage()]);
            $session->delete();
            return "Sorry, something went wrong creating your booking. Please try again or contact us directly.";
        }
    }
}
