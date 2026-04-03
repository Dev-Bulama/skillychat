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

    {{-- Platform Tabs --}}
    @if(count($platforms))
    <div class="col-12">
        <div class="i-card py-2 px-3">
            @php
                $iconMap = ['instagram'=>'bi-instagram','tiktok'=>'bi-tiktok','youtube'=>'bi-youtube','facebook'=>'bi-facebook','twitter'=>'bi-twitter-x','telegram'=>'bi-telegram','spotify'=>'bi-spotify','linkedin'=>'bi-linkedin','threads'=>'bi-threads','other'=>'bi-globe'];
            @endphp
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('user.smm.index') }}"
                    class="i-btn btn--sm {{ !$selected_platform ? 'btn--primary' : 'btn--outline' }} capsuled">
                    {{ translate('All') }}
                </a>
                @foreach($platforms as $val => $label)
                <a href="{{ route('user.smm.index', array_filter(['platform' => $val, 'service_type' => $selected_type])) }}"
                    class="i-btn btn--sm {{ $selected_platform == $val ? 'btn--primary' : 'btn--outline' }} capsuled">
                    <i class="bi {{ $iconMap[$val] ?? 'bi-globe' }} me-1"></i>{{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Service Type Filter --}}
    @if(count($service_types))
    <div class="col-12">
        <div class="i-card py-2 px-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="fs-13 text-muted me-1">{{ translate('Type:') }}</span>
                <a href="{{ route('user.smm.index', array_filter(['platform' => $selected_platform, 'service_type' => ''])) }}"
                    class="badge {{ !$selected_type ? 'bg-primary' : 'bg-light text-dark border' }} fs-12 text-decoration-none px-3 py-2">
                    {{ translate('All') }}
                </a>
                @foreach($service_types as $val => $label)
                <a href="{{ route('user.smm.index', array_filter(['platform' => $selected_platform, 'service_type' => $val])) }}"
                    class="badge {{ $selected_type == $val ? 'bg-primary' : 'bg-light text-dark border' }} fs-12 text-decoration-none px-3 py-2">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

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
