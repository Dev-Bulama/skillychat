<?php

namespace App\Services\SMM;

use App\Enums\SMMOrderStatus;
use App\Enums\StatusEnum;
use App\Models\SMMOrder;
use App\Models\SMMOrderLog;
use App\Models\SMMService;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SMMOrderService
{
    /**
     * Place a new SMM order for a user.
     * Deducts from wallet, creates order, dispatches to provider.
     */
    public function placeOrder(User $user, SMMService $service, string $targetLink, int $quantity): SMMOrder
    {
        $charge = $service->calculateCharge($quantity);

        if ((float) $user->balance < $charge) {
            throw new \RuntimeException('Insufficient wallet balance.');
        }

        DB::beginTransaction();
        try {
            // Deduct wallet
            $user->balance = bcsub((string) $user->balance, (string) $charge, 4);
            $user->save();

            // Transaction log (minus)
            Transaction::create([
                'user_id'      => $user->id,
                'trx_code'     => strtoupper('SMM' . uniqid()),
                'trx_type'     => Transaction::$MINUS,
                'amount'       => $charge,
                'remarks'      => 'smm_boost_order',
                'post_balance' => $user->balance,
                'currency_id'  => optional($user->currency)->id,
            ]);

            $order = SMMOrder::create([
                'user_id'         => $user->id,
                'service_id'      => $service->id,
                'platform'        => $service->platform,
                'service_type'    => $service->service_type,
                'target_link'     => $targetLink,
                'quantity'        => $quantity,
                'charge'          => $charge,
                'provider_cost'   => 0,
                'currency_symbol' => '$',
                'status'          => SMMOrderStatus::PENDING->value,
            ]);

            DB::commit();

            // Dispatch to provider asynchronously (or sync if queue not configured)
            dispatch(new \App\Jobs\ProcessSMMOrder($order->id));

            return $order;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Send a pending order to its provider.
     */
    public function sendToProvider(SMMOrder $order): void
    {
        $order->load('service.provider');
        $service  = $order->service;
        $provider = $service->provider;

        if (!$provider || !$provider->isActive()) {
            $this->markFailed($order, 'Provider is inactive or missing.');
            return;
        }

        $driver = SMMProviderFactory::make($provider);

        try {
            $result = $driver->placeOrder(
                $service->provider_service_id,
                $order->target_link,
                $order->quantity
            );

            $order->update([
                'provider_order_id'  => $result['provider_order_id'],
                'status'             => SMMOrderStatus::PROCESSING->value,
                'sent_to_provider_at'=> now(),
            ]);

            SMMOrderLog::create([
                'order_id'         => $order->id,
                'action'           => 'place_order',
                'request_payload'  => [
                    'service'  => $service->provider_service_id,
                    'link'     => $order->target_link,
                    'quantity' => $order->quantity,
                ],
                'response_payload' => $result,
                'success'          => true,
                'http_status'      => '200',
            ]);
        } catch (\Throwable $e) {
            SMMOrderLog::create([
                'order_id'         => $order->id,
                'action'           => 'place_order',
                'request_payload'  => [],
                'response_payload' => ['error' => $e->getMessage()],
                'success'          => false,
                'http_status'      => '500',
            ]);
            $this->markFailed($order, $e->getMessage());
        }
    }

    /**
     * Sync status of all processing orders with provider.
     */
    public function syncProcessingOrders(): void
    {
        SMMOrder::processing()
            ->with('service.provider')
            ->whereNotNull('provider_order_id')
            ->chunk(50, function ($orders) {
                foreach ($orders as $order) {
                    $this->syncOrderStatus($order);
                }
            });
    }

    public function syncOrderStatus(SMMOrder $order): void
    {
        $provider = $order->service->provider ?? null;

        if (!$provider || !$provider->isActive()) {
            return;
        }

        $driver = SMMProviderFactory::make($provider);

        try {
            $result = $driver->checkStatus($order->provider_order_id);

            SMMOrderLog::create([
                'order_id'         => $order->id,
                'action'           => 'check_status',
                'request_payload'  => ['order' => $order->provider_order_id],
                'response_payload' => $result,
                'success'          => true,
                'http_status'      => '200',
            ]);

            $providerStatus = strtolower($result['status'] ?? '');

            $statusMap = [
                'completed' => SMMOrderStatus::COMPLETED->value,
                'complete'  => SMMOrderStatus::COMPLETED->value,
                'partial'   => SMMOrderStatus::COMPLETED->value,
                'cancelled' => SMMOrderStatus::FAILED->value,
                'canceled'  => SMMOrderStatus::FAILED->value,
                'error'     => SMMOrderStatus::FAILED->value,
                'failed'    => SMMOrderStatus::FAILED->value,
            ];

            if (isset($statusMap[$providerStatus])) {
                $newStatus = $statusMap[$providerStatus];
                $updates = [
                    'status'      => $newStatus,
                    'start_count' => $result['start_count'] ?? $order->start_count,
                    'remains'     => $result['remains'] ?? $order->remains,
                ];
                if ($newStatus === SMMOrderStatus::COMPLETED->value) {
                    $updates['completed_at'] = now();
                }
                $order->update($updates);
            } else {
                // Still in progress — update counters
                $order->update([
                    'start_count' => $result['start_count'] ?? $order->start_count,
                    'remains'     => $result['remains'] ?? $order->remains,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[SMM] syncOrderStatus failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Refund a failed/pending order back to user wallet.
     */
    public function refundOrder(SMMOrder $order, ?int $adminId = null): void
    {
        if (!$order->canBeRefunded()) {
            throw new \RuntimeException('This order cannot be refunded.');
        }

        DB::beginTransaction();
        try {
            $user = $order->user;
            $user->balance = bcadd((string) $user->balance, (string) $order->charge, 4);
            $user->save();

            Transaction::create([
                'user_id'      => $user->id,
                'trx_code'     => strtoupper('SMRFD' . uniqid()),
                'trx_type'     => Transaction::$PLUS,
                'amount'       => $order->charge,
                'remarks'      => 'smm_boost_refund',
                'post_balance' => $user->balance,
                'currency_id'  => optional($user->currency)->id,
            ]);

            $order->update([
                'status'      => SMMOrderStatus::REFUNDED->value,
                'refunded_by' => $adminId,
                'remarks'     => 'Refunded by admin.',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function markFailed(SMMOrder $order, string $reason): void
    {
        $order->update([
            'status'  => SMMOrderStatus::FAILED->value,
            'remarks' => $reason,
        ]);
    }
}
