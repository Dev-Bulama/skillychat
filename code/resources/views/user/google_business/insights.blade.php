@extends('layouts.master')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-semibold">
                        <i class="bi bi-graph-up me-2" style="color:#34a853;"></i>
                        {{ translate('Insights & Analytics') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">{{ $location->display_name }}</p>
                </div>
                <a href="{{ route('user.google-business.index') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                </a>
            </div>

            {{-- Date Range Picker --}}
            <div class="i-card-md mb-4">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="fw-semibold fs-14">{{ translate('Date Range') }}:</span>
                        @foreach([7 => translate('Last 7 Days'), 30 => translate('Last 30 Days'), 90 => translate('Last 90 Days')] as $d => $label)
                            <a href="{{ request()->fullUrlWithQuery(['days' => $d]) }}"
                               class="i-btn btn--sm {{ $days == $d ? 'btn--primary' : 'btn--outline' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                        <form method="get" class="ms-auto d-flex align-items-center gap-2">
                            <input type="hidden" name="days" value="{{ $days }}">
                            <button type="submit" class="i-btn btn--sm btn--outline">
                                <i class="bi bi-arrow-clockwise me-1"></i>{{ translate('Refresh') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($error)
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ translate('Could not fetch insights from Google:') }} {{ $error }}
                </div>
            @endif

            @php
                // Parse metric values from the Google Insights API response
                $metricValues = [];
                if (! empty($insightsData['locationMetrics'])) {
                    foreach ($insightsData['locationMetrics'][0]['metricValues'] ?? [] as $mv) {
                        $metric = $mv['metric'] ?? '';
                        $total  = 0;
                        foreach ($mv['dimensionalValues'] ?? [] as $dv) {
                            $total += (int) ($dv['value'] ?? 0);
                        }
                        $metricValues[$metric] = $total;
                    }
                }

                $stats = [
                    'QUERIES_DIRECT'      => ['label' => translate('Direct Searches'), 'icon' => 'bi-search', 'color' => '#4285f4'],
                    'QUERIES_INDIRECT'    => ['label' => translate('Discovery Searches'), 'icon' => 'bi-binoculars', 'color' => '#34a853'],
                    'VIEWS_MAPS'          => ['label' => translate('Map Views'), 'icon' => 'bi-map', 'color' => '#ea4335'],
                    'VIEWS_SEARCH'        => ['label' => translate('Search Views'), 'icon' => 'bi-eye', 'color' => '#fbbc04'],
                    'ACTIONS_WEBSITE'     => ['label' => translate('Website Clicks'), 'icon' => 'bi-globe', 'color' => '#4285f4'],
                    'ACTIONS_PHONE'       => ['label' => translate('Phone Calls'), 'icon' => 'bi-telephone', 'color' => '#34a853'],
                    'ACTIONS_DRIVING_DIRECTIONS' => ['label' => translate('Direction Requests'), 'icon' => 'bi-sign-turn-right', 'color' => '#ea4335'],
                    'PHOTOS_VIEWS_MERCHANT'      => ['label' => translate('Photo Views'), 'icon' => 'bi-image', 'color' => '#fbbc04'],
                ];
            @endphp

            {{-- Stat Cards --}}
            <div class="row g-4 mb-4">
                @foreach($stats as $metric => $info)
                <div class="col-sm-6 col-xl-3">
                    <div class="i-card-md h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-2 p-2"
                                     style="background:{{ $info['color'] }}1a;">
                                    <i class="bi {{ $info['icon'] }} fs-5" style="color:{{ $info['color'] }};"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold mb-1">{{ number_format($metricValues[$metric] ?? 0) }}</h3>
                            <p class="text-muted fs-13 mb-0">{{ $info['label'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Info Note --}}
            <div class="i-card-md">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong class="d-block mb-1">{{ translate('About Insights Data') }}</strong>
                            <p class="text-muted fs-14 mb-0">
                                {{ translate('Insights data is fetched live from Google\'s Business Profile API and reflects aggregated activity for the selected date range. Data may be delayed up to 48 hours. For detailed breakdowns, visit') }}
                                <a href="https://business.google.com" target="_blank" class="text-primary">business.google.com</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
