<?php

namespace App\Services\SMM;

use App\Enums\SMMOrderStatus;
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
     * Deducts from wallet, creates order, then immediately sends to provider.
     * Does NOT use a queue — works on shared hosting with no worker.
     */
    public function placeOrder(User $user, SMMService $service, string $targetLink, int $quantity): SMMOrder
    {
        $charge = $service->calculateCharge($quantity);

        if ((float) $user->balance < $charge) {
            throw new \RuntimeException('Insufficient wallet balance.');
        }

        // ── 1. Wallet deduction + order creation (atomic) ──────────────────
        DB::beginTransaction();
        try {
            $user->balance = bcsub((string) $user->balance, (string) $charge, 4);
            $user->save();

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
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // ── 2. Send to provider immediately (outside transaction) ───────────
        // If this fails the order is marked FAILED and auto-refunded.
        // Admin can retry via the order detail page.
        try {
            $order->load('service.provider');
            $this->sendToProvider($order);
        } catch (\Throwable $e) {
            // sendToProvider() already wrote the log and marked FAILED.
            // Auto-refund the user so they are not charged for a failed order.
            try {
                $this->autoRefund($order->fresh());
            } catch (\Throwable $refundEx) {
                Log::error('[SMM] Auto-refund failed after provider error', [
                    'order_id' => $order->id,
                    'error'    => $refundEx->getMessage(),
                ]);
            }
        }

        return $order->fresh();
    }

    /**
     * Send a pending order to its provider.
     * ALWAYS writes an SMMOrderLog entry — whether success or failure.
     * Throws on failure so callers know what happened.
     */
    public function sendToProvider(SMMOrder $order): void
    {
        $order->loadMissing('service.provider');

        $service  = $order->service;
        $provider = $service->provider ?? null;

        $requestPayload = [
            'service'  => $service->provider_service_id ?? null,
            'link'     => $order->target_link,
            'quantity' => $order->quantity,
        ];

        // ── Pre-flight checks (always log even on config error) ─────────────
        if (!$provider) {
            $error = 'No provider configured for this service.';
            $this->writeLog($order, 'place_order', $requestPayload, ['error' => $error], false, '000');
            $this->markFailed($order, $error);
            throw new \RuntimeException($error);
        }

        if (!$provider->isActive()) {
            $error = 'Provider "' . $provider->name . '" is currently inactive.';
            $this->writeLog($order, 'place_order', $requestPayload, ['error' => $error], false, '000');
            $this->markFailed($order, $error);
            throw new \RuntimeException($error);
        }

        if (empty($provider->api_url) || empty($provider->api_key)) {
            $error = 'Provider "' . $provider->name . '" is missing API URL or API Key.';
            $this->writeLog($order, 'place_order', $requestPayload, ['error' => $error], false, '000');
            $this->markFailed($order, $error);
            throw new \RuntimeException($error);
        }

        // ── Call provider API ────────────────────────────────────────────────
        $driver = SMMProviderFactory::make($provider);

        try {
            $result = $driver->placeOrder(
                $service->provider_service_id,
                $order->target_link,
                $order->quantity
            );

            $order->update([
                'provider_order_id'   => $result['provider_order_id'],
                'status'              => SMMOrderStatus::PROCESSING->value,
                'sent_to_provider_at' => now(),
            ]);

            $this->writeLog($order, 'place_order', $requestPayload, $result, true, '200');
        } catch (\Throwable $e) {
            $this->writeLog($order, 'place_order', $requestPayload, ['error' => $e->getMessage()], false, '500');
            $this->markFailed($order, 'Provider API error: ' . $e->getMessage());
            throw $e;
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

            $this->writeLog($order, 'check_status', ['order' => $order->provider_order_id], $result, true, '200');

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
     * Refund a failed/pending order back to user wallet (admin-initiated).
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

    /**
     * Auto-refund after a failed provider call.
     */
    public function autoRefund(SMMOrder $order): void
    {
        DB::beginTransaction();
        try {
            $user = $order->fresh()->user;
            $user->balance = bcadd((string) $user->balance, (string) $order->charge, 4);
            $user->save();

            Transaction::create([
                'user_id'      => $user->id,
                'trx_code'     => strtoupper('SMRFD' . uniqid()),
                'trx_type'     => Transaction::$PLUS,
                'amount'       => $order->charge,
                'remarks'      => 'smm_boost_auto_refund',
                'post_balance' => $user->balance,
                'currency_id'  => optional($user->currency)->id,
            ]);

            $order->update(['status' => SMMOrderStatus::REFUNDED->value]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SMM] Auto-refund failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Reset a FAILED order to PENDING and immediately retry sending to provider.
     * Called from admin order detail page.
     */
    public function retryOrder(SMMOrder $order): void
    {
        if ($order->status !== SMMOrderStatus::FAILED->value) {
            throw new \RuntimeException('Only FAILED orders can be retried.');
        }

        $order->update([
            'status'              => SMMOrderStatus::PENDING->value,
            'remarks'             => null,
            'provider_order_id'   => null,
            'sent_to_provider_at' => null,
        ]);

        // Send immediately — no queue
        $order->load('service.provider');
        $this->sendToProvider($order);
    }

    public function markFailed(SMMOrder $order, string $reason): void
    {
        $order->update([
            'status'  => SMMOrderStatus::FAILED->value,
            'remarks' => $reason,
        ]);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function writeLog(SMMOrder $order, string $action, array $request, array $response, bool $success, string $httpStatus): void
    {
        try {
            SMMOrderLog::create([
                'order_id'         => $order->id,
                'action'           => $action,
                'request_payload'  => $request,
                'response_payload' => $response,
                'success'          => $success,
                'http_status'      => $httpStatus,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SMM] Failed to write order log', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
