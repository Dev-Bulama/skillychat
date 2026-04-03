<?php

namespace App\Services\SMM;

interface SMMProviderInterface
{
    /**
     * Fetch the full service list from the provider.
     * Returns array of services with provider_service_id, name, type, min, max, rate.
     */
    public function getServices(): array;

    /**
     * Place an order with the provider.
     * Returns ['provider_order_id' => string, 'status' => string].
     */
    public function placeOrder(string $providerServiceId, string $link, int $quantity): array;

    /**
     * Check status of a previously placed order.
     * Returns ['status' => string, 'start_count' => int, 'remains' => int].
     */
    public function checkStatus(string $providerOrderId): array;

    /**
     * Cancel an order (if supported by the provider).
     */
    public function cancelOrder(string $providerOrderId): bool;

    /**
     * Test API connectivity.
     * Returns ['success' => bool, 'message' => string].
     */
    public function testConnection(): array;
}
