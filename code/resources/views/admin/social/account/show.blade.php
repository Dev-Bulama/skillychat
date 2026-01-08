@extends('admin.layouts.master')
@section('content')
    <div class="i-card-md">
        <div class="card--header">
            <h4 class="card-title">
                {{ translate('Latest Feed of ') }}{{ $account->account_information->name ?? '' }}
            </h4>
        </div>

        <div class="card-body">
            <div class="row row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1 g-3 post-card-container">
                @forelse($response['data'] ?? [] as $post)
                    <div class="col">
                        <div class="social-preview-body single-post position-relative">
                            <!-- View Post button top-right (subtle) -->
                            @if(!empty($post['permalink']))
                                <div class="position-absolute top-0 end-0 p-2 z-3">
                                    <a href="{{ $post['permalink'] }}" target="_blank"
                                       class="btn btn-sm btn-light text-muted shadow-sm">
                                        <i class="bi bi-box-arrow-up-right fs-12"></i>
                                    </a>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="social-auth d-flex align-items-center gap-3">
                                    <div class="profile-img">
                                        <img data-fallback="{{ get_default_img() }}"
                                             src="{{ $account->account_information->avatar ?? get_default_img() }}"
                                             alt="{{ translate('Social account image') }}">
                                    </div>

                                    <div class="profile-meta">
                                        @if($account->account_information->link ?? false)
                                            <h6 class="mb-0">
                                                <a target="_blank" href="{{ $account->account_information->link }}">
                                                    {{ $account->account_information->name }}
                                                </a>
                                            </h6>
                                        @else
                                            <h6 class="mb-0">{{ $account->account_information->name }}</h6>
                                        @endif

                                        <div class="d-flex align-items-center gap-2">
                                            <p class="mb-0 small text-muted">
                                                {{ diff_for_humans(\Carbon\Carbon::parse($post['timestamp'])) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($account->platform->slug === 'facebook')
                                    <span class="status i-badge info">
                                        {{ k2t(Arr::get($post, $account->account_type == App\Enums\AccountType::PAGE->value ? 'status_type' : 'type', 'status')) }}
                                    </span>
                                @endif
                            </div>

                            <div class="social-caption">
                                @if(!empty($post['caption']))
                                    <div class="caption-text mb-3">{{ $post['caption'] }}</div>
                                @endif

                                @if(!empty($post['media_url']))
                                    <div class="caption-imgs mb-3">
                                        <img src="{{ $post['media_url'] }}" class="img-fluid rounded" alt="Post media">
                                    </div>
                                @endif

                                <div class="action-count d-flex justify-content-between align-items-center">
                                    <div class="emoji d-flex align-items-center gap-1">
                                        <ul class="d-flex gap-0 react-icon-list mb-0">
                                            <li><img src="{{ asset('assets/images/default/like.png') }}" alt="like"></li>
                                            @if($account->platform->slug !== 'youtube')
                                                <li><img src="{{ asset('assets/images/default/love.png') }}" alt="love"></li>
                                                <li><img src="{{ asset('assets/images/default/care.png') }}" alt="care"></li>
                                            @endif
                                        </ul>
                                        <span class="fs-13">{{ $post['reactions'] }}</span>
                                    </div>

                                    <div class="comment-count py-2 px-0">
                                        <ul class="d-flex align-items-center gap-3 mb-0">
                                            <li>{{ $post['comments'] }} {{ translate('Comments') }}</li>
                                            @if($account->platform->slug === 'youtube')
                                                <li>{{ $post['views'] ?? 0 }} {{ translate('Views') }}</li>
                                            @else
                                                <li>{{ $post['shares'] }} {{ translate('Shares') }}</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    @include('admin.partials.not_found')
                @endforelse
            </div>
        </div>
    </div>

    @if($account->platform->slug === 'facebook' && $account->account_type == App\Enums\AccountType::PAGE->value && !empty($response['page_insights']))
        <div class="i-card-md mt-4">
            <div class="card--header">
                <h4 class="card-title">
                    {{ translate('Page Insight Of') }} {{ $account->account_information->name }}
                    <small>({{ translate('Last 30 days') }})</small>
                </h4>
            </div>

            @php
                $insightData        = $response['page_insights'];
                $dailyInsight       = $insightData[0] ?? [];
                $dailyInsightValues = collect($dailyInsight['values'] ?? []);
            @endphp

            <div class="card-body">
                <div class="row g-2">
                    @if($dailyInsightValues->count() > 0)
                        @php
                            $graphLabel = $dailyInsightValues->pluck('end_time')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray();
                            $graphValue = $dailyInsightValues->pluck('value')->toArray();
                        @endphp
                        <div class="col-12">
                            <div id="engagementReport" class="apex-chart"></div>
                        </div>
                    @else
                        @include('admin.partials.not_found')
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script-include')
    @if($account->platform->slug === 'facebook' && $account->account_type == App\Enums\AccountType::PAGE->value && ($dailyInsightValues ?? null)?->count() > 0)
        <script src="{{ asset('assets/global/js/apexcharts.js') }}"></script>
    @endif
@endpush

@push('script-push')
    @if($account->platform->slug === 'facebook' && $account->account_type == App\Enums\AccountType::PAGE->value && ($dailyInsightValues ?? null)?->count() > 0)
        <script nonce="{{ csp_nonce() }}">
            "use strict";
            const options = {
                chart: { height: 350, type: "line", toolbar: { show: false } },
                dataLabels: { enabled: false },
                colors: ["{{ site_settings('primary_color') }}"],
                series: [{ name: "{{ translate('Total Engagement') }}", data: @json($graphValue) }],
                xaxis: { categories: @json($graphLabel) },
                markers: { size: 6 },
                stroke: { width: 4 }
            };
            new ApexCharts(document.querySelector("#engagementReport"), options).render();
        </script>
    @endif
@endpush