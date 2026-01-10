@extends('admin.layouts.master')

@push('style-include')
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('AI Chatbot Analytics') }}</h4>
                    <div class="d-flex gap-2">
                        <form action="{{ route(Route::currentRouteName()) }}" method="get" class="d-flex gap-2">
                            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                            <button type="submit" class="i-btn btn--sm info">
                                <i class="las la-filter"></i> {{ translate('Filter') }}
                            </button>
                            <a href="{{ route(Route::currentRouteName()) }}" class="i-btn btn--sm danger">
                                <i class="las la-sync"></i>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Messages Per Day') }}</h4>
                </div>
                <div class="card-body">
                    <div id="messagesChart" class="apex-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Conversations Per Day') }}</h4>
                </div>
                <div class="card-body">
                    <div id="conversationsChart" class="apex-chart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Top Performing Chatbots') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{translate('Chatbot Name')}}</th>
                                    <th>{{translate('Owner')}}</th>
                                    <th>{{translate('Conversations')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_chatbots as $chatbot)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('admin.chatbot.show', $chatbot->uid) }}" class="text-primary">
                                                {{ $chatbot->name }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($chatbot->user)
                                                {{ $chatbot->user->name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge--primary">
                                                {{ $chatbot->conversations_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">{{ translate('No data available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Human Takeover Statistics') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="stat-card p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ translate('Total Takeovers') }}</h6>
                                        <h3 class="mt-2 mb-0 text-primary">{{ $takeover_stats['total_takeovers'] }}</h3>
                                    </div>
                                    <div class="icon">
                                        <i class="las la-hands-helping la-3x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card p-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ translate('Average Response Time') }}</h6>
                                        <h3 class="mt-2 mb-0 text-info">
                                            @if($takeover_stats['avg_takeover_time'])
                                                {{ round($takeover_stats['avg_takeover_time'] / 60, 1) }} {{ translate('minutes') }}
                                            @else
                                                {{ translate('N/A') }}
                                            @endif
                                        </h3>
                                    </div>
                                    <div class="icon">
                                        <i class="las la-clock la-3x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-include')
    <script src="{{asset('assets/global/js/datepicker/moment.min.js')}}"></script>
    <script src="{{asset('assets/global/js/datepicker/daterangepicker.min.js')}}"></script>
    <script src="{{asset('assets/global/js/apexcharts.js')}}"></script>
@endpush

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    (function($){
        "use strict";

        // Messages Per Day Chart
        var messagesData = @json($messages_per_day);
        var messagesDates = messagesData.map(item => item.date);
        var messagesCounts = messagesData.map(item => item.count);

        var messagesOptions = {
            chart: {
                nonce: "{{ csp_nonce() }}",
                height: 350,
                type: "line",
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false,
            },
            colors: ["var(--color-primary)"],
            series: [
                {
                    name: "{{ translate('Messages') }}",
                    data: messagesCounts,
                },
            ],
            xaxis: {
                categories: messagesDates,
            },
            tooltip: {
                shared: false,
                intersect: true,
                y: {
                    formatter: function (value) {
                        return parseInt(value);
                    }
                }
            },
            markers: {
                size: 6,
            },
            stroke: {
                width: [4],
                curve: 'smooth'
            },
        };

        var messagesChart = new ApexCharts(document.querySelector("#messagesChart"), messagesOptions);
        messagesChart.render();

        // Conversations Per Day Chart
        var conversationsData = @json($conversations_per_day);
        var conversationsDates = conversationsData.map(item => item.date);
        var conversationsCounts = conversationsData.map(item => item.count);

        var conversationsOptions = {
            chart: {
                nonce: "{{ csp_nonce() }}",
                height: 350,
                type: "bar",
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false,
            },
            colors: ["var(--color-success)"],
            series: [
                {
                    name: "{{ translate('Conversations') }}",
                    data: conversationsCounts,
                },
            ],
            xaxis: {
                categories: conversationsDates,
            },
            tooltip: {
                shared: false,
                intersect: true,
                y: {
                    formatter: function (value) {
                        return parseInt(value);
                    }
                }
            },
        };

        var conversationsChart = new ApexCharts(document.querySelector("#conversationsChart"), conversationsOptions);
        conversationsChart.render();

    })(jQuery);
</script>
@endpush
