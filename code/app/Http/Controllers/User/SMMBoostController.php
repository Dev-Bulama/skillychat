<?php

namespace App\Http\Controllers\User;

use App\Enums\SMMOrderStatus;
use App\Enums\SMMPlatform;
use App\Enums\SMMServiceType;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\SMMOrder;
use App\Models\SMMService;
use App\Models\User;
use App\Services\SMM\SMMOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SMMBoostController extends Controller
{
    protected User $user;

    public function __construct(protected SMMOrderService $orderService)
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    /**
     * Service listing page.
     */
    public function index(Request $request): View|JsonResponse
    {
        $allPlatforms    = SMMPlatform::toArray();
        $allServiceTypes = SMMServiceType::toArray();

        $selectedPlatform = $request->platform;
        $selectedType     = $request->service_type;
        $search           = $request->search;

        // Only expose platforms/types that actually have active services
        $activePlatforms = SMMService::active()->distinct()->pluck('platform')->toArray();
        $activeTypes     = SMMService::active()->distinct()->pluck('service_type')->toArray();

        $platforms    = array_filter($allPlatforms,    fn($k) => in_array($k, $activePlatforms),    ARRAY_FILTER_USE_KEY);
        $serviceTypes = array_filter($allServiceTypes, fn($k) => in_array($k, $activeTypes), ARRAY_FILTER_USE_KEY);

        $services = SMMService::with('provider')
            ->active()
            ->when($selectedPlatform, fn($q) => $q->where('platform', $selectedPlatform))
            ->when($selectedType,     fn($q) => $q->where('service_type', $selectedType))
            ->when($search,           fn($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->latest()
            ->paginate(paginateNumber());

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('user.smm_boost.partials.service_cards', compact('services'))->render(),
                'pagination' => (string) $services->withQueryString()->links(),
                'total'      => $services->total(),
            ]);
        }

        return view('user.smm_boost.index', [
            'meta_data'         => $this->metaData(['title' => translate('Social Media Boost')]),
            'services'          => $services,
            'platforms'         => $platforms,
            'service_types'     => $serviceTypes,
            'selected_platform' => $selectedPlatform,
            'selected_type'     => $selectedType,
            'search'            => $search,
        ]);
    }

    /**
     * Order form for a specific service.
     */
    public function orderForm(string $uid): View
    {
        $service = SMMService::with('provider')->where('uid', $uid)->active()->firstOrFail();

        return view('user.smm_boost.order_form', [
            'meta_data' => $this->metaData(['title' => translate('Place Order')]),
            'service'   => $service,
            'user'      => $this->user,
        ]);
    }

    /**
     * Get price for quantity (AJAX).
     */
    public function getPrice(Request $request): JsonResponse
    {
        $service  = SMMService::where('uid', $request->service_uid)->active()->firstOrFail();
        $quantity = max($service->min_quantity, min($service->max_quantity, (int) $request->quantity));
        $charge   = $service->calculateCharge($quantity);

        return response()->json([
            'charge'   => number_format($charge, 2),
            'quantity' => $quantity,
        ]);
    }

    /**
     * Place order.
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'service_uid'  => ['required', 'string'],
            'target_link'  => ['required', 'string', 'max:500'],
            'quantity'     => ['required', 'integer', 'min:1'],
        ]);

        $service = SMMService::where('uid', $request->service_uid)->active()->firstOrFail();

        // Enforce min/max
        if ($request->quantity < $service->min_quantity || $request->quantity > $service->max_quantity) {
            return back()->with('error', translate(
                "Quantity must be between {$service->min_quantity} and {$service->max_quantity}."
            ))->withInput();
        }

        // Prevent near-duplicate orders within 5 minutes
        $recentDuplicate = SMMOrder::where('user_id', $this->user->id)
            ->where('service_id', $service->id)
            ->where('target_link', $request->target_link)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($recentDuplicate) {
            return back()->with('error', translate(
                'You placed an identical order recently. Please wait before reordering.'
            ))->withInput();
        }

        try {
            $order = $this->orderService->placeOrder(
                $this->user,
                $service,
                $request->target_link,
                $request->quantity
            );

            return redirect()->route('user.smm.orders')
                ->with(response_status('Order placed successfully! Order ID: ' . $order->uid));
        } catch (\RuntimeException $e) {
            return back()->with('error', translate($e->getMessage()))->withInput();
        }
    }

    /**
     * User order history.
     */
    public function orders(Request $request): View
    {
        $orders = SMMOrder::with('service')
            ->where('user_id', $this->user->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(paginateNumber());

        return view('user.smm_boost.orders', [
            'meta_data' => $this->metaData(['title' => translate('My Boost Orders')]),
            'orders'    => $orders,
            'statuses'  => SMMOrderStatus::labels(),
        ]);
    }

    /**
     * Order detail.
     */
    public function orderDetail(string $uid): View
    {
        $order = SMMOrder::with('service.provider')
            ->where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        return view('user.smm_boost.order_detail', [
            'meta_data' => $this->metaData(['title' => translate('Order Detail')]),
            'order'     => $order,
        ]);
    }

    /**
     * How It Works / Help guide.
     */
    public function howItWorks(): View
    {
        return view('user.smm_boost.how_it_works', [
            'meta_data' => $this->metaData(['title' => translate('How It Works')]),
        ]);
    }
}
