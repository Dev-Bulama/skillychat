@extends('layouts.master')
@section('content')
@php
    $iconMap = ['instagram'=>'bi-instagram','tiktok'=>'bi-tiktok','youtube'=>'bi-youtube','facebook'=>'bi-facebook','twitter'=>'bi-twitter-x','telegram'=>'bi-telegram','spotify'=>'bi-spotify','linkedin'=>'bi-linkedin','threads'=>'bi-threads'];
    $icon = $iconMap[$service->platform] ?? 'bi-globe';
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="i-card">
            <div class="card--header mb-4">
                <div>
                    <a href="{{ route('user.smm.index') }}" class="text-muted fs-13 d-block mb-1">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Services') }}
                    </a>
                    <h4 class="card--title mb-0">{{ translate('Place Order') }}</h4>
                </div>
            </div>

            {{-- Service Summary --}}
            <div class="p-3 mb-4 border rounded" style="background:#f8f9fa;">
                <div class="d-flex align-items-center gap-3">
                    <span class="icon-btn icon-btn-sm primary circle">
                        <i class="bi {{ $icon }}"></i>
                    </span>
                    <div>
                        <h6 class="mb-0">{{ $service->name }}</h6>
                        <span class="badge bg-primary fs-11 me-1">{{ ucfirst($service->platform) }}</span>
                        <span class="badge bg-secondary fs-11">{{ ucfirst(str_replace('_',' ',$service->service_type)) }}</span>
                        @if($service->delivery_estimate)
                        <span class="ms-2 fs-12 text-muted"><i class="bi bi-clock"></i> {{ $service->delivery_estimate }}</span>
                        @endif
                    </div>
                </div>
                @if($service->description)
                <p class="mt-2 mb-0 fs-13 text-muted">{{ $service->description }}</p>
                @endif
            </div>

            {{-- Disclaimer --}}
            <div class="alert fs-13 mb-4" style="background:#fff3cd;border-left:4px solid #ffc107;padding:12px 16px;border-radius:8px;">
                <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                {{ translate('Engagement services are delivered by third-party providers. Results may vary. We do not guarantee organic or real interactions.') }}
            </div>

            <form action="{{ route('user.smm.place-order') }}" method="post" id="orderForm">
                @csrf
                <input type="hidden" name="service_uid" value="{{ $service->uid }}">

                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-inner">
                            <label>
                                {{ translate('Target Link / Username') }}
                                <small class="text-danger">*</small>
                            </label>
                            <input type="text" name="target_link" required
                                placeholder="{{ translate('Enter full URL or username (e.g. https://instagram.com/yourprofile)') }}"
                                value="{{ old('target_link') }}"
                                class="form-control">
                            <small class="text-muted">
                                {{ translate('Paste the full link to your post, video, or profile page. Make sure the account is public.') }}
                            </small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-inner">
                            <label>
                                {{ translate('Quantity') }}
                                <small class="text-danger">*</small>
                                <span class="text-muted fs-12">({{ translate('Min:') }} {{ number_format($service->min_quantity) }} / {{ translate('Max:') }} {{ number_format($service->max_quantity) }})</span>
                            </label>
                            <input type="number" name="quantity" id="quantityInput" required
                                min="{{ $service->min_quantity }}"
                                max="{{ $service->max_quantity }}"
                                step="1"
                                value="{{ old('quantity', $service->min_quantity) }}"
                                class="form-control">
                        </div>
                    </div>

                    {{-- Price Preview --}}
                    <div class="col-12">
                        <div class="p-3 border rounded d-flex justify-content-between align-items-center"
                            style="background:#f0f4ff;">
                            <div>
                                <div class="fs-13 text-muted mb-1">{{ translate('Estimated Cost') }}</div>
                                <div class="fs-22 fw-bold text--primary" id="pricePreview">
                                    $<span id="priceValue">{{ number_format($service->calculateCharge($service->min_quantity), 2) }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-13 text-muted mb-1">{{ translate('Wallet Balance') }}</div>
                                <div class="fw-semibold {{ $user->balance < $service->calculateCharge($service->min_quantity) ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format($user->balance, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="i-btn btn--md btn--primary w-100" data-anim="ripple">
                            <i class="bi bi-cart-check me-2"></i>{{ translate('Place Order') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="i-card mb-4">
            <h5 class="card--title mb-3">{{ translate('Pricing') }}</h5>
            <div class="row g-2">
                <div class="col-6 text-muted fs-13">{{ translate('Rate per 1,000') }}</div>
                <div class="col-6 fw-semibold">${{ number_format($service->price_per_1000, 4) }}</div>
                <div class="col-6 text-muted fs-13">{{ translate('Minimum Order') }}</div>
                <div class="col-6 fw-semibold">{{ number_format($service->min_quantity) }}</div>
                <div class="col-6 text-muted fs-13">{{ translate('Maximum Order') }}</div>
                <div class="col-6 fw-semibold">{{ number_format($service->max_quantity) }}</div>
                @if($service->delivery_estimate)
                <div class="col-6 text-muted fs-13">{{ translate('Est. Delivery') }}</div>
                <div class="col-6 fw-semibold">{{ $service->delivery_estimate }}</div>
                @endif
            </div>
        </div>

        <div class="i-card">
            <h5 class="card--title mb-3">{{ translate('Tips') }}</h5>
            <ul class="fs-13 text-muted ps-3">
                <li class="mb-2">{{ translate('Ensure your account is set to Public before ordering.') }}</li>
                <li class="mb-2">{{ translate('Double check the link before submitting.') }}</li>
                <li class="mb-2">{{ translate('Do not change your username during delivery.') }}</li>
                <li class="mb-2">{{ translate('Results begin within the estimated delivery window.') }}</li>
                <li>{{ translate('Contact support if your order does not progress within 24 hours.') }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    var rate    = {{ $service->price_per_1000 }};
    var minQty  = {{ $service->min_quantity }};
    var maxQty  = {{ $service->max_quantity }};
    var input   = document.getElementById('quantityInput');
    var display = document.getElementById('priceValue');

    function updatePrice() {
        var qty = Math.max(minQty, Math.min(maxQty, parseInt(input.value) || minQty));
        var price = (rate / 1000) * qty;
        display.textContent = price.toFixed(2);
    }

    input.addEventListener('input', updatePrice);
    updatePrice();
})();
</script>
@endpush
