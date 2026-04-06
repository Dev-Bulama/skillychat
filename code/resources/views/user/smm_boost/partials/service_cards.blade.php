@php
$iconMap = ['instagram'=>'bi-instagram','tiktok'=>'bi-tiktok','youtube'=>'bi-youtube','facebook'=>'bi-facebook','twitter'=>'bi-twitter-x','telegram'=>'bi-telegram','spotify'=>'bi-spotify','linkedin'=>'bi-linkedin','threads'=>'bi-threads'];
@endphp
@forelse($services as $service)
<div class="col-xl-4 col-md-6">
    <div class="i-card h-100 border position-relative d-flex flex-column">
        <div class="d-flex align-items-center gap-2 mb-3">
            @php $icon = $iconMap[$service->platform] ?? 'bi-globe'; @endphp
            <span class="icon-btn icon-btn-sm primary circle">
                <i class="bi {{ $icon }}"></i>
            </span>
            <div>
                <span class="badge bg-primary fs-11">{{ ucfirst($service->platform) }}</span>
                <span class="badge bg-secondary fs-11 ms-1">{{ ucfirst(str_replace('_',' ',$service->service_type)) }}</span>
            </div>
        </div>
        <h6 class="fw-semibold mb-2">{{ $service->name }}</h6>
        @if($service->description)
        <p class="text-muted fs-13 mb-3">{{ Str::limit($service->description, 80) }}</p>
        @endif
        <div class="mt-auto">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-2 border rounded text-center">
                        <div class="fw-semibold text--primary">${{ number_format($service->price_per_1000, 2) }}</div>
                        <div class="fs-12 text-muted">{{ translate('per 1,000') }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded text-center">
                        <div class="fw-semibold">{{ number_format($service->min_quantity) }}–{{ number_format($service->max_quantity) }}</div>
                        <div class="fs-12 text-muted">{{ translate('qty range') }}</div>
                    </div>
                </div>
            </div>
            @if($service->delivery_estimate)
            <p class="fs-12 text-muted mb-3">
                <i class="bi bi-clock me-1"></i>{{ translate('Delivery:') }} {{ $service->delivery_estimate }}
            </p>
            @endif
            <a href="{{ route('user.smm.order-form', $service->uid) }}"
                class="i-btn btn--sm btn--primary capsuled w-100">
                <i class="bi bi-cart-plus me-1"></i>{{ translate('Order Now') }}
            </a>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="i-card text-center py-5">
        <i class="bi bi-search fs-50 text-muted mb-3 d-block"></i>
        <h5>{{ translate('No services found') }}</h5>
        <p class="text-muted">{{ translate('Try a different search term or clear the filter.') }}</p>
    </div>
</div>
@endforelse
