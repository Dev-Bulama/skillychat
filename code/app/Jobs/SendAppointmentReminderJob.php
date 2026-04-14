<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $appointmentId) {}

    public function handle(): void
    {
        $appointment = Appointment::with(['service', 'user'])->find($this->appointmentId);

        if (!$appointment || $appointment->status === 'cancelled' || $appointment->reminder_24h_sent) {
            return;
        }

        $this->sendWhatsAppReminder($appointment);
        $this->sendEmailReminder($appointment);

        $appointment->update(['reminder_24h_sent' => 1]);
    }

    private function sendWhatsAppReminder(Appointment $appointment): void
    {
        if (!$appointment->whatsapp_number) return;

        try {
            // Find the user's primary WhatsApp account
            $account = WhatsAppAccount::where('user_id', $appointment->user_id)
                ->where('status', 1)
                ->first();

            if (!$account) return;

            $service  = $appointment->service?->name ?? 'Appointment';
            $dateTime = $appointment->appointment_at->format('l, F j \a\t g:i A');

            $msg = "⏰ *Reminder: Appointment Tomorrow*\n\n"
                . "Hi {$appointment->contact_name}!\n\n"
                . "This is a reminder that you have an appointment scheduled:\n\n"
                . "📋 *Service:* {$service}\n"
                . "📅 *Date & Time:* {$dateTime}\n\n"
                . "To cancel, reply *cancel appointment {$appointment->uid}*.";

            (new WhatsAppService($account))->sendText($appointment->whatsapp_number, $msg);
        } catch (\Throwable $e) {
            Log::warning('Appointment WhatsApp reminder failed: ' . $e->getMessage());
        }
    }

    private function sendEmailReminder(Appointment $appointment): void
    {
        if (!$appointment->contact_email) return;

        try {
            Mail::send([], [], function ($mail) use ($appointment) {
                $service  = $appointment->service?->name ?? 'Appointment';
                $dateTime = $appointment->appointment_at->format('l, F j, Y \a\t g:i A');
                $appName  = config('app.name');

                $body = "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;'>
                        <h2 style='color:#f59e0b;'>⏰ Appointment Reminder</h2>
                        <p>Hi <strong>{$appointment->contact_name}</strong>,</p>
                        <p>Just a reminder — your appointment is <strong>tomorrow</strong>:</p>
                        <div style='background:#fffbeb;border-left:4px solid #f59e0b;padding:16px;border-radius:8px;margin:20px 0;'>
                            <p style='margin:4px 0;'><strong>Service:</strong> {$service}</p>
                            <p style='margin:4px 0;'><strong>Date & Time:</strong> {$dateTime}</p>
                        </div>
                        <p style='margin-top:20px;'>
                            <a href='" . url("/appointments/cancel/{$appointment->cancellation_token}") . "'
                                style='color:#dc2626;font-size:13px;'>Cancel appointment</a>
                        </p>
                        <p style='color:#6b7280;font-size:12px;'>Sent by {$appName}</p>
                    </div>
                ";

                $mail->to($appointment->contact_email, $appointment->contact_name)
                     ->subject("Reminder: {$service} — {$dateTime}")
                     ->html($body);
            });
        } catch (\Throwable $e) {
            Log::warning('Appointment email reminder failed: ' . $e->getMessage());
        }
    }
}
