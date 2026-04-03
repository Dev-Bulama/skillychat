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

        $imported = 0;
        foreach ($services as $svc) {
            $exists = SMMService::where('provider_id', $provider->id)
                ->where('provider_service_id', $svc['provider_service_id'])
                ->exists();

            if (!$exists) {
                SMMService::create([
                    'provider_id'          => $provider->id,
                    'provider_service_id'  => $svc['provider_service_id'],
                    'name'                 => $svc['name'],
                    'platform'             => 'other',
                    'service_type'         => $svc['type'] ?? 'other',
                    'description'          => $svc['description'] ?? '',
                    'price_per_1000'       => 1.00, // admin must set proper pricing
                    'min_quantity'         => max(1, $svc['min'] ?? 100),
                    'max_quantity'         => min(1000000, $svc['max'] ?? 100000),
                    'delivery_estimate'    => '1-3 days',
                    'status'               => StatusEnum::false->status(), // disabled until admin reviews
                ]);
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} new services (disabled by default — please review and set pricing).",
        ]);
    }

    // ─── Services ───────────────────────────────────────────────────────────────

    public function services(Request $request): View
    {
        $services = SMMService::with('provider')
            ->when($request->platform, fn($q) => $q->where('platform', $request->platform))
            ->when($request->service_type, fn($q) => $q->where('service_type', $request->service_type))
            ->when($request->provider_id, fn($q) => $q->where('provider_id', $request->provider_id))
            ->latest()
            ->paginate(paginateNumber());

        return view('admin.smm_boost.services', [
            'title'        => 'SMM Services',
            'services'     => $services,
            'providers'    => SMMProvider::active()->get(),
            'platforms'    => SMMPlatform::toArray(),
            'service_types'=> SMMServiceType::toArray(),
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

}
