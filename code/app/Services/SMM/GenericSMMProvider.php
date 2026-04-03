<?php

namespace App\Services\SMM;

use App\Models\SMMProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic SMM API Provider
 *
 * Compatible with the standard SMM panel API used by most providers:
 * JustAnotherPanel, SMMWorld, SafeSMM, PeakSMM, ExoBooster, ExoSupplier, etc.
 *
 * Standard API specification:
 *   POST {api_url}?action=services&key={api_key}
 *   POST {api_url}?action=add&key=...&service=...&link=...&quantity=...
 *   POST {api_url}?action=status&key=...&order=...
 */
class GenericSMMProvider implements SMMProviderInterface
{
    protected string $apiUrl;
    protected string $apiKey;
    protected SMMProvider $provider;

    public function __construct(SMMProvider $provider)
    {
        $this->provider = $provider;
        $this->apiUrl   = rtrim($provider->api_url, '/');
        $this->apiKey   = $provider->api_key;
    }

    public function getServices(): array
    {
        $response = $this->post(['action' => 'services']);

        if (!is_array($response)) {
            return [];
        }

        // Some providers wrap the list in a 'data' or 'services' key
        if (isset($response['data']) && is_array($response['data'])) {
            $response = $response['data'];
        } elseif (isset($response['services']) && is_array($response['services'])) {
            $response = $response['services'];
        }

        // Filter out non-service entries (e.g. error keys at top level)
        $services = array_filter($response, fn($item) => is_array($item));

        return array_values(array_map(function ($item) {
            // Providers use 'service', 'id', or 'service_id' for the service identifier
            $serviceId = $item['service'] ?? $item['service_id'] ?? $item['id'] ?? '';

            return [
                'provider_service_id' => (string) $serviceId,
                'name'                => $item['name'] ?? $item['title'] ?? '',
                'type'                => $item['type'] ?? $item['service_type'] ?? 'other',
                'category'            => $item['category'] ?? $item['platform'] ?? '',
                'min'                 => (int) ($item['min'] ?? $item['min_order'] ?? 1),
                'max'                 => (int) ($item['max'] ?? $item['max_order'] ?? 100000),
                'rate'                => (float) ($item['rate'] ?? $item['price'] ?? 0),
                'description'         => $item['description'] ?? $item['desc'] ?? '',
            ];
        }, $services));
    }

    public function placeOrder(string $providerServiceId, string $link, int $quantity): array
    {
        $response = $this->post([
            'action'   => 'add',
            'service'  => $providerServiceId,
            'link'     => $link,
            'quantity' => $quantity,
        ]);

        $orderId = $response['order'] ?? $response['order_id'] ?? null;

        if (!$orderId) {
            $error = $response['error'] ?? 'Unknown provider error';
            throw new \RuntimeException("Provider order failed: {$error}");
        }

        return [
            'provider_order_id' => (string) $orderId,
            'status'            => 'processing',
        ];
    }

    public function checkStatus(string $providerOrderId): array
    {
        $response = $this->post([
            'action' => 'status',
            'order'  => $providerOrderId,
        ]);

        return [
            'status'      => strtolower($response['status'] ?? 'processing'),
            'start_count' => (int) ($response['start_count'] ?? 0),
            'remains'     => (int) ($response['remains'] ?? 0),
        ];
    }

    public function cancelOrder(string $providerOrderId): bool
    {
        $response = $this->post([
            'action' => 'cancel',
            'orders' => $providerOrderId,
        ]);

        return isset($response['cancel']) && !empty($response['cancel']);
    }

    public function testConnection(): array
    {
        try {
            $services = $this->getServices();
            if (is_array($services)) {
                return [
                    'success' => true,
                    'message' => 'Connected successfully. Found ' . count($services) . ' services.',
                ];
            }
            return ['success' => false, 'message' => 'Unexpected response from provider.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function post(array $params): array
    {
        try {
            $payload = array_merge(['key' => $this->apiKey], $params);

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->apiUrl, $payload);

            $json = $response->json();
            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            Log::error('[SMM Provider] API call failed', [
                'provider' => $this->provider->name,
                'params'   => $params,
                'error'    => $e->getMessage(),
            ]);
            return [];
        }
    }
}
