<?php

namespace App\Jobs;

use App\Models\DripEnrollment;
use App\Models\DripEnrollmentLog;
use App\Models\DripStep;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDripSequences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find all active enrollments due for next send
        DripEnrollment::with(['sequence.steps', 'user'])
            ->where('status', 'active')
            ->where('next_send_at', '<=', now())
            ->each(function (DripEnrollment $enrollment) {
                try {
                    $this->processEnrollment($enrollment);
                } catch (\Throwable $e) {
                    Log::error('Drip enrollment error', [
                        'enrollment_id' => $enrollment->id,
                        'error'         => $e->getMessage(),
                    ]);
                }
            });
    }

    private function processEnrollment(DripEnrollment $enrollment): void
    {
        $steps      = $enrollment->sequence->steps->values();
        $nextIndex  = $enrollment->current_step; // 0-based index of next step to send

        if ($nextIndex >= $steps->count()) {
            // All steps done
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
            $enrollment->sequence->increment('total_completed');
            return;
        }

        $step   = $steps->get($nextIndex);
        $result = $this->sendStep($step, $enrollment);

        DripEnrollmentLog::create([
            'enrollment_id' => $enrollment->id,
            'step_id'       => $step->id,
            'status'        => $result['success'] ? 'sent' : 'failed',
            'response'      => $result['message'] ?? null,
            'sent_at'       => now(),
        ]);

        $nextStepIndex = $nextIndex + 1;

        if ($nextStepIndex >= $steps->count()) {
            // Finished
            $enrollment->update(['status' => 'completed', 'completed_at' => now(), 'current_step' => $nextStepIndex]);
            $enrollment->sequence->increment('total_completed');
        } else {
            // Schedule next step
            $nextStep    = $steps->get($nextStepIndex);
            $nextSendAt  = now()->addDays($nextStep->delay_days)->addHours($nextStep->delay_hours);
            $enrollment->update(['current_step' => $nextStepIndex, 'next_send_at' => $nextSendAt]);
        }
    }

    private function sendStep(DripStep $step, DripEnrollment $enrollment): array
    {
        $to      = $enrollment->contact_email;
        $phone   = $enrollment->contact_phone;
        $name    = $enrollment->contact_name ?? 'Friend';
        $message = str_replace(['{{name}}', '{{first_name}}'], [$name, explode(' ', $name)[0]], $step->message);
        $subject = str_replace(['{{name}}', '{{first_name}}'], [$name, explode(' ', $name)[0]], $step->subject ?? '');

        return match($step->channel) {
            'email'    => $this->sendEmail($to, $subject, $message, $step, $enrollment),
            'sms'      => $this->sendSms($phone ?? $to, $message, $enrollment),
            'whatsapp' => $this->sendWhatsApp($phone, $message, $enrollment),
            default    => ['success' => false, 'message' => 'Unknown channel'],
        };
    }

    private function sendEmail(string $email, string $subject, string $body, DripStep $step, DripEnrollment $enrollment): array
    {
        try {
            $user    = (object) ['email' => $email, 'name' => $enrollment->contact_name ?? $email];
            $codes   = ['message' => $body, 'email' => $email];
            SendMailJob::dispatchSync($user, 'CONTACT_REPLY', $codes);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendSms(string $phone, string $message, DripEnrollment $enrollment): array
    {
        try {
            SendSmsJob::dispatchSync($phone, $message, $enrollment->user_id);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendWhatsApp(?string $phone, string $message, DripEnrollment $enrollment): array
    {
        if (!$phone) return ['success' => false, 'message' => 'No phone number'];

        $account = WhatsAppAccount::where('user_id', $enrollment->user_id)
            ->where('status', 1)
            ->first();

        if (!$account) return ['success' => false, 'message' => 'No active WhatsApp account'];

        return (new WhatsAppService($account))->sendText($phone, $message);
    }
}
