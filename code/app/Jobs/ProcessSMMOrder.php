<?php

namespace App\Jobs;

use App\Enums\SMMOrderStatus;
use App\Models\SMMOrder;
use App\Services\SMM\SMMOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSMMOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public readonly int $orderId) {}

    public function handle(SMMOrderService $service): void
    {
        $order = SMMOrder::with('service.provider')->find($this->orderId);

        if (!$order || !$order->isPending()) {
            return;
        }

        // Throws on provider failure so the job framework can retry
        $service->sendToProvider($order);
    }

    /**
     * Called by Laravel after all retries are exhausted.
     * Mark order FAILED and auto-refund the user's wallet.
     */
    public function failed(\Throwable $exception): void
    {
        $order = SMMOrder::with('user')->find($this->orderId);

        if (!$order) {
            return;
        }

        // Only act if still pending (not already handled)
        if ($order->isPending()) {
            $order->update([
                'status'  => SMMOrderStatus::FAILED->value,
                'remarks' => 'Provider API failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
            ]);

            try {
                app(SMMOrderService::class)->autoRefund($order->fresh());
                Log::info('[SMM] Auto-refunded order after provider failure', ['order_id' => $this->orderId]);
            } catch (\Throwable $e) {
                Log::error('[SMM] Auto-refund failed in job::failed()', [
                    'order_id' => $this->orderId,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}
