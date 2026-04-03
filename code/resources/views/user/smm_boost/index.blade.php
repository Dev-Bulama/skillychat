@extends('layouts.master')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="i-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card--title mb-1">
                        <i class="bi bi-rocket-takeoff me-2 text--primary"></i>
                        {{ translate('Social Media Boost') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">
                        {{ translate('Purchase followers, likes, views, and more for your social media profiles.') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('user.smm.orders') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-list-check me-1"></i>{{ translate('My Orders') }}
                    </a>
                    <a href="{{ route('user.smm.how-it-works') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-question-circle me-1"></i>{{ translate('How It Works') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Disclaimer --}}
    <div class="col-12">
        <div class="alert d-flex align-items-start gap-2 mb-0"
            style="background:#fff3cd;border-left:4px solid #ffc107;padding:12px 16px;border-radius:8px;">
            <i class="bi bi-exclamation-triangle text-warning fs-18 flex-shrink-0 mt-1"></i>
            <div class="fs-14">
                <strong>{{ translate('Important:') }}</strong>
                {{ translate('Engagement results are based on provider delivery. We do not guarantee organic or real interactions. Delivery time varies. Results are estimates only.') }}
            </div>
        </div>
    </div>

    {{-- Platform Filter --}}
    <div class="col-12">
        <div class="i-card">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="fs-13 mb-1">{{ translate('Platform') }}</label>
                    <select name="platform" class="form-select form-select-sm">
                        <option value="">{{ translate('All Platforms') }}</option>
                        @foreach($platforms as $val => $label)
                        <option value="{{ $val }}" {{ $selected_platform == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="fs-13 mb-1">{{ translate('Service Type') }}</label>
                    <select name="service_type" class="form-select form-select-sm">
                        <option value="">{{ translate('All Types') }}</option>
                        @foreach($service_types as $val => $label)
                        <option value="{{ $val }}" {{ $selected_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="i-btn btn--sm btn--primary w-100">
                        <i class="bi bi-funnel me-1"></i>{{ translate('Filter') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Services Grid --}}
    @forelse($services as $service)
    <div class="col-xl-4 col-md-6">
        <div class="i-card h-100 border position-relative d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-3">
                @php
                    $iconMap = ['instagram'=>'bi-instagram','tiktok'=>'bi-tiktok','youtube'=>'bi-youtube','facebook'=>'bi-facebook','twitter'=>'bi-twitter-x','telegram'=>'bi-telegram','spotify'=>'bi-spotify','linkedin'=>'bi-linkedin','threads'=>'bi-threads'];
                    $icon = $iconMap[$service->platform] ?? 'bi-globe';
                @endphp
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
            <i class="bi bi-rocket-takeoff fs-50 text-muted mb-3 d-block"></i>
            <h5>{{ translate('No services available yet') }}</h5>
            <p class="text-muted">{{ translate('Check back soon — services will appear here once configured.') }}</p>
        </div>
    </div>
    @endforelse

    @if($services->hasPages())
    <div class="col-12">
        {{ $services->withQueryString()->links() }}
    </div>
    @endif

</div>
@endsection
