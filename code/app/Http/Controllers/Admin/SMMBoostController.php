<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SMMOrderStatus;
use App\Enums\SMMPlatform;
use App\Enums\SMMServiceType;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\SMMOrder;
use App\Models\SMMOrderLog;
use App\Models\SMMProvider;
use App\Models\SMMService;
use App\Services\SMM\SMMOrderService;
use App\Services\SMM\SMMProviderFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SMMBoostController extends Controller
{
    public function __construct(protected SMMOrderService $orderService) {}

    // ─── Setup Guide ────────────────────────────────────────────────────────────

    public function setupGuide(): View
    {
        return view('admin.smm_boost.setup_guide', [
            'title' => 'SMM Boost Setup Guide',
        ]);
    }

    // ─── Providers ──────────────────────────────────────────────────────────────

    public function providers(): View
    {
        return view('admin.smm_boost.providers', [
            'title'     => 'SMM Providers',
            'providers' => SMMProvider::latest()->paginate(paginateNumber()),
        ]);
    }

    public function createProvider(): View
    {
        return view('admin.smm_boost.provider_form', [
            'title'    => 'Add Provider',
            'provider' => null,
        ]);
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:191'],
            'api_url' => ['required', 'url'],
            'api_key' => ['required', 'string'],
            'status'  => ['nullable'],
        ]);

        SMMProvider::create([
            'name'    => $data['name'],
            'slug'    => Str::slug($data['name'] . '-' . Str::random(4)),
            'api_url' => $data['api_url'],
            'api_key' => $data['api_key'],
            'status'  => $request->has('status') ? StatusEnum::true->status() : StatusEnum::false->status(),
        ]);

        return redirect()->route('admin.smm.providers')->with(response_status('Provider added successfully.'));
    }

    public function editProvider(int $id): View
    {
        $provider = SMMProvider::findOrFail($id);
        return view('admin.smm_boost.provider_form', [
            'title'    => 'Edit Provider',
            'provider' => $provider,
        ]);
    }

    public function updateProvider(Request $request, int $id): RedirectResponse
    {
        $provider = SMMProvider::findOrFail($id);
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:191'],
            'api_url' => ['required', 'url'],
            'api_key' => ['required', 'string'],
            'status'  => ['nullable'],
        ]);

        $provider->update([
            'name'    => $data['name'],
            'api_url' => $data['api_url'],
            'api_key' => $data['api_key'],
            'status'  => $request->has('status') ? StatusEnum::true->status() : StatusEnum::false->status(),
        ]);

        return redirect()->route('admin.smm.providers')->with(response_status('Provider updated.'));
    }

    public function destroyProvider(int $id): RedirectResponse
    {
        SMMProvider::findOrFail($id)->delete();
        return back()->with(response_status('Provider deleted.'));
    }

    public function testProvider(int $id): JsonResponse
    {
        $provider = SMMProvider::findOrFail($id);
        $driver   = SMMProviderFactory::make($provider);
        $result   = $driver->testConnection();

        return response()->json($result);
    }

    public function importServices(int $id): JsonResponse
    {
        $provider = SMMProvider::findOrFail($id);
        $driver   = SMMProviderFactory::make($provider);
        $services = $driver->getServices();

        if (empty($services)) {
            return response()->json([
                'success' => false,
                'message' => 'No services returned from provider. Check the API URL and key.',
            ]);
        }

        $imported = 0;
        foreach ($services as $svc) {
            if (empty($svc['provider_service_id'])) continue;

            $exists = SMMService::where('provider_id', $provider->id)
                ->where('provider_service_id', $svc['provider_service_id'])
                ->exists();

            if (!$exists) {
                $name     = $svc['name'] ?? '';
                $category = $svc['category'] ?? '';

                SMMService::create([
                    'provider_id'         => $provider->id,
                    'provider_service_id' => $svc['provider_service_id'],
                    'name'                => $name,
                    'platform'            => $this->detectPlatform($name, $category),
                    'service_type'        => $this->detectServiceType($name, $category, $svc['type'] ?? ''),
                    'description'         => $svc['description'] ?? $category,
                    'price_per_1000'      => 1.00,
                    'min_quantity'        => max(1, (int) ($svc['min'] ?? 100)),
                    'max_quantity'        => min(1000000, (int) ($svc['max'] ?? 100000)),
                    'delivery_estimate'   => '1-3 days',
                    'status'              => StatusEnum::false->status(),
                ]);
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} new services out of " . count($services) . " total (disabled by default — set pricing before enabling).",
        ]);
    }

    /**
     * Re-detect platform & service_type for all services of a provider
     * that were imported with 'other' as the platform.
     */
    public function redetectServices(int $id): JsonResponse
    {
        $provider = SMMProvider::findOrFail($id);
        $updated  = 0;

        SMMService::where('provider_id', $provider->id)->chunkById(100, function ($services) use (&$updated) {
            foreach ($services as $svc) {
                $platform    = $this->detectPlatform($svc->name, $svc->description ?? '');
                $serviceType = $this->detectServiceType($svc->name, $svc->description ?? '', '');

                if ($platform !== $svc->platform || $serviceType !== $svc->service_type) {
                    $svc->update([
                        'platform'     => $platform,
                        'service_type' => $serviceType,
                    ]);
                    $updated++;
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Re-detected platform/type for {$updated} services.",
        ]);
    }

    private function detectPlatform(string $name, string $category = ''): string
    {
        $text = strtolower($name . ' ' . $category);

        $map = [
            'instagram' => 'instagram',
            'tiktok'    => 'tiktok',
            'tik tok'   => 'tiktok',
            'youtube'   => 'youtube',
            'facebook'  => 'facebook',
            'twitter'   => 'twitter',
            'telegram'  => 'telegram',
            'spotify'   => 'spotify',
            'linkedin'  => 'linkedin',
            'threads'   => 'threads',
            'soundcloud'=> 'other',
            'twitch'    => 'other',
            'snapchat'  => 'other',
            'pinterest' => 'other',
        ];

        foreach ($map as $keyword => $platform) {
            if (str_contains($text, $keyword)) {
                return $platform;
            }
        }

        return 'other';
    }

    private function detectServiceType(string $name, string $category = '', string $providerType = ''): string
    {
        $text = strtolower($name . ' ' . $category . ' ' . $providerType);

        $map = [
            'story view'  => 'story_views',
            'live view'   => 'live_views',
            'watch time'  => 'watch_time',
            'watch hour'  => 'watch_time',
            'subscriber'  => 'subscribers',
            'follower'    => 'followers',
            'follow'      => 'followers',
            'impression'  => 'impressions',
            'comment'     => 'comments',
            'share'       => 'shares',
            'retweet'     => 'shares',
            'repost'      => 'shares',
            'save'        => 'saves',
            'like'        => 'likes',
            'heart'       => 'likes',
            'reaction'    => 'likes',
            'view'        => 'views',
            'play'        => 'plays',
            'stream'      => 'plays',
        ];

        foreach ($map as $keyword => $type) {
            if (str_contains($text, $keyword)) {
                return $type;
            }
        }

        return 'other';
    }

    // ─── Services ───────────────────────────────────────────────────────────────

    public function services(Request $request): View|JsonResponse
    {
        $query = SMMService::with('provider')
            ->when($request->platform,     fn($q) => $q->where('platform', $request->platform))
            ->when($request->service_type, fn($q) => $q->where('service_type', $request->service_type))
            ->when($request->provider_id,  fn($q) => $q->where('provider_id', $request->provider_id))
            ->when($request->search,       fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest();

        if ($request->ajax()) {
            $services = $query->paginate(paginateNumber());
            return response()->json([
                'html'       => view('admin.smm_boost.partials.services_rows', compact('services'))->render(),
                'pagination' => (string) $services->withQueryString()->links(),
                'total'      => $services->total(),
            ]);
        }

        $services = $query->paginate(paginateNumber());

        return view('admin.smm_boost.services', [
            'title'        => 'SMM Services',
            'services'     => $services,
            'providers'    => SMMProvider::latest()->get(),
            'platforms'    => SMMPlatform::toArray(),
            'service_types'=> SMMServiceType::toArray(),
        ]);
    }

    /**
     * Bulk toggle (activate/deactivate) services.
     */
    public function bulkToggleServices(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => ['required', 'array'],
            'ids.*'  => ['required', 'exists:smm_services,id'],
            'action' => ['required', 'in:activate,deactivate'],
        ]);

        $status = $request->action === 'activate'
            ? StatusEnum::true->status()
            : StatusEnum::false->status();

        SMMService::whereIn('id', $request->ids)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' service(s) ' . ($request->action === 'activate' ? 'activated' : 'deactivated') . '.',
        ]);
    }

    public function createService(): View
    {
        return view('admin.smm_boost.service_form', [
            'title'        => 'Add Service',
            'service'      => null,
            'providers'    => SMMProvider::active()->get(),
            'platforms'    => SMMPlatform::toArray(),
            'service_types'=> SMMServiceType::toArray(),
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_id'         => ['required', 'exists:smm_providers,id'],
            'provider_service_id' => ['required', 'string'],
            'name'                => ['required', 'string', 'max:255'],
            'platform'            => ['required', 'string'],
            'service_type'        => ['required', 'string'],
            'description'         => ['nullable', 'string'],
            'price_per_1000'      => ['required', 'numeric', 'min:0'],
            'min_quantity'        => ['required', 'integer', 'min:1'],
            'max_quantity'        => ['required', 'integer', 'min:1'],
            'delivery_estimate'   => ['nullable', 'string', 'max:100'],
            'status'              => ['nullable'],
        ]);

        $data['status'] = $request->has('status') ? StatusEnum::true->status() : StatusEnum::false->status();
        SMMService::create($data);

        return redirect()->route('admin.smm.services')->with(response_status('Service created.'));
    }

    public function editService(int $id): View
    {
        return view('admin.smm_boost.service_form', [
            'title'        => 'Edit Service',
            'service'      => SMMService::findOrFail($id),
            'providers'    => SMMProvider::active()->get(),
            'platforms'    => SMMPlatform::toArray(),
            'service_types'=> SMMServiceType::toArray(),
        ]);
    }

    public function updateService(Request $request, int $id): RedirectResponse
    {
        $service = SMMService::findOrFail($id);
        $data = $request->validate([
            'provider_id'         => ['required', 'exists:smm_providers,id'],
            'provider_service_id' => ['required', 'string'],
            'name'                => ['required', 'string', 'max:255'],
            'platform'            => ['required', 'string'],
            'service_type'        => ['required', 'string'],
            'description'         => ['nullable', 'string'],
            'price_per_1000'      => ['required', 'numeric', 'min:0'],
            'min_quantity'        => ['required', 'integer', 'min:1'],
            'max_quantity'        => ['required', 'integer', 'min:1'],
            'delivery_estimate'   => ['nullable', 'string', 'max:100'],
            'status'              => ['nullable'],
        ]);

        $data['status'] = $request->has('status') ? StatusEnum::true->status() : StatusEnum::false->status();
        $service->update($data);

        return redirect()->route('admin.smm.services')->with(response_status('Service updated.'));
    }

    public function destroyService(int $id): RedirectResponse
    {
        SMMService::findOrFail($id)->delete();
        return back()->with(response_status('Service deleted.'));
    }

    public function toggleService(int $id): RedirectResponse
    {
        $service = SMMService::findOrFail($id);
        $service->status = $service->isActive()
            ? StatusEnum::false->status()
            : StatusEnum::true->status();
        $service->save();

        return back()->with(response_status('Service status updated.'));
    }

    // ─── Orders ─────────────────────────────────────────────────────────────────

    public function orders(Request $request): View
    {
        $orders = SMMOrder::with(['user', 'service'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->platform, fn($q) => $q->where('platform', $request->platform))
            ->latest()
            ->paginate(paginateNumber());

        return view('admin.smm_boost.orders', [
            'title'     => 'SMM Orders',
            'orders'    => $orders,
            'statuses'  => SMMOrderStatus::labels(),
            'platforms' => SMMPlatform::toArray(),
        ]);
    }

    public function orderDetail(int $id): View
    {
        $order = SMMOrder::with(['user', 'service.provider', 'logs'])->findOrFail($id);

        return view('admin.smm_boost.order_detail', [
            'title'    => 'Order #' . Str::upper(Str::substr($order->uid, 0, 8)),
            'order'    => $order,
            'statuses' => SMMOrderStatus::labels(),
        ]);
    }

    public function updateOrderStatus(Request $request, int $id): RedirectResponse
    {
        $order = SMMOrder::findOrFail($id);
        $request->validate([
            'status'  => ['required', 'in:' . implode(',', array_column(SMMOrderStatus::cases(), 'value'))],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $order->update([
            'status'  => $request->status,
            'remarks' => $request->remarks,
        ]);

        return back()->with(response_status('Order status updated.'));
    }

    public function refundOrder(int $id): RedirectResponse
    {
        $order = SMMOrder::with('user')->findOrFail($id);

        try {
            $this->orderService->refundOrder($order, auth_user()->id);
            return back()->with(response_status('Order refunded successfully.'));
        } catch (\RuntimeException $e) {
            return back()->with(response_status($e->getMessage(), 'error'));
        }
    }

    public function syncOrder(int $id): RedirectResponse
    {
        $order = SMMOrder::with('service.provider')->findOrFail($id);
        $this->orderService->syncOrderStatus($order);
        return back()->with(response_status('Order synced with provider.'));
    }

    public function retryOrder(int $id): RedirectResponse
    {
        $order = SMMOrder::with('service.provider')->findOrFail($id);

        // Allow retry for both FAILED orders and stuck PENDING orders (no logs)
        $isStuck = $order->status === SMMOrderStatus::PENDING->value
                && $order->logs()->count() === 0;

        if ($order->status !== SMMOrderStatus::FAILED->value && !$isStuck) {
            return back()->with(response_status('Only failed or stuck orders can be retried.', 'error'));
        }

        // Reset to pending and send immediately
        $order->update([
            'status'              => SMMOrderStatus::PENDING->value,
            'remarks'             => null,
            'provider_order_id'   => null,
            'sent_to_provider_at' => null,
        ]);

        try {
            $order->load('service.provider');
            $this->orderService->sendToProvider($order);
            return back()->with(response_status('Order sent to provider successfully.'));
        } catch (\Throwable $e) {
            return back()->with(response_status('Provider error: ' . $e->getMessage(), 'error'));
        }
    }

}
