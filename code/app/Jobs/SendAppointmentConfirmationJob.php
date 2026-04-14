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

class SendAppointmentConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $appointmentId) {}

    public function handle(): void
    {
        $appointment = Appointment::with(['service', 'user'])->find($this->appointmentId);
        if (!$appointment || $appointment->confirmation_sent) return;

        $this->sendEmail($appointment);
        $this->scheduleReminder($appointment);

        $appointment->update(['confirmation_sent' => 1]);
    }

    private function sendEmail(Appointment $appointment): void
    {
        if (!$appointment->contact_email) return;

        try {
            Mail::send([], [], function ($mail) use ($appointment) {
                $service  = $appointment->service?->name ?? 'Appointment';
                $dateTime = $appointment->appointment_at->format('l, F j, Y \a\t g:i A');
                $user     = $appointment->user;
                $appName  = config('app.name');

                $body = "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;'>
                        <h2 style='color:#16a34a;'>✅ Appointment Confirmed</h2>
                        <p>Hi <strong>{$appointment->contact_name}</strong>,</p>
                        <p>Your appointment has been confirmed. Here are the details:</p>
                        <div style='background:#f0fdf4;border-left:4px solid #16a34a;padding:16px;border-radius:8px;margin:20px 0;'>
                            <p style='margin:4px 0;'><strong>Service:</strong> {$service}</p>
                            <p style='margin:4px 0;'><strong>Date & Time:</strong> {$dateTime}</p>
                            <p style='margin:4px 0;'><strong>With:</strong> {$user->name}</p>
                        </div>
                        <p>You will receive a reminder 24 hours before your appointment.</p>
                        <p style='margin-top:20px;'>
                            <a href='" . url("/appointments/cancel/{$appointment->cancellation_token}") . "'
                                style='color:#dc2626;font-size:13px;'>Cancel appointment</a>
                        </p>
                        <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                        <p style='color:#6b7280;font-size:12px;'>This email was sent by {$appName}.</p>
                    </div>
                ";

                $mail->to($appointment->contact_email, $appointment->contact_name)
                     ->subject("Appointment Confirmed — {$dateTime}")
                     ->html($body);
            });
        } catch (\Throwable $e) {
            Log::warning('Appointment confirmation email failed: ' . $e->getMessage());
        }
    }

    private function scheduleReminder(Appointment $appointment): void
    {
        $reminderAt = $appointment->appointment_at->subHours(24);
        if ($reminderAt->isFuture()) {
            SendAppointmentReminderJob::dispatch($appointment->id)->delay($reminderAt);
        }
    }
}
