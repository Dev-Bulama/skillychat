<?php

namespace App\Jobs;

use App\Models\SMMOrder;
use App\Services\SMM\SMMOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        $service->sendToProvider($order);
    }
}
