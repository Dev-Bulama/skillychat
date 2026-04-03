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

        // Normalize response — most providers return a flat array of service objects
        return array_map(function ($item) {
            return [
                'provider_service_id' => (string) ($item['service'] ?? $item['id'] ?? ''),
                'name'                => $item['name'] ?? '',
                'type'                => strtolower($item['type'] ?? 'other'),
                'category'            => $item['category'] ?? '',
                'min'                 => (int) ($item['min'] ?? 1),
                'max'                 => (int) ($item['max'] ?? 100000),
                'rate'                => (float) ($item['rate'] ?? 0),  // provider's own price per 1000
                'description'         => $item['description'] ?? '',
            ];
        }, $response);
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
