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

    {{-- Search Bar --}}
    <div class="col-12">
        <div class="i-card py-2 px-3">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="smm-search" class="form-control border-start-0 ps-0"
                    placeholder="{{ translate('Search for services (e.g. Instagram followers, TikTok views…)') }}"
                    value="{{ $search ?? '' }}" autocomplete="off">
                <button class="btn btn-outline-secondary" id="smm-search-clear" type="button">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Services Grid --}}
    <div class="col-12">
        <div class="row g-4" id="services-grid">
            @include('user.smm_boost.partials.service_cards', ['services' => $services])
        </div>
    </div>

    <div class="col-12" id="smm-pagination">
        @if($services->hasPages())
        {{ $services->withQueryString()->links() }}
        @endif
    </div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    var baseUrl      = '{{ route('user.smm.index') }}';
    var debounceTimer;

    function getParams() {
        return {
            platform:     '{{ $selected_platform ?? '' }}',
            service_type: '{{ $selected_type ?? '' }}',
            search:       document.getElementById('smm-search').value.trim(),
        };
    }

    function fetchServices() {
        var params  = getParams();
        var url     = new URL(baseUrl);
        Object.entries(params).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); });

        var grid = document.getElementById('services-grid');
        grid.style.opacity = '0.4';

        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                grid.innerHTML = data.html;
                grid.style.opacity = '1';
                document.getElementById('smm-pagination').innerHTML = data.pagination;
            })
            .catch(() => { grid.style.opacity = '1'; });
    }

    document.getElementById('smm-search').addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchServices, 300);
    });

    document.getElementById('smm-search-clear').addEventListener('click', function () {
        document.getElementById('smm-search').value = '';
        fetchServices();
    });
})();
</script>
@endpush

</div>
@endsection
