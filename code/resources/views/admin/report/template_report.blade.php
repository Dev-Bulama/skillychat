@extends('admin.layouts.master')

@push('style-include')
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet"
        type="text/css" />

    <style>
        .small-video {
            width: 160px;      /* Fixed width */
            height: 100px;     /* Fixed height */
            object-fit: cover; /* Crop to fit the box */
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            display: block;
        }

    </style>
@endpush

@section('content')

    <div class="row g-4">
        <div class="col-xxl-3 col-xl-4">

            <div id="word-summary" class="summary-content">
                @include('admin.partials.summary', ['summaries' => $word_summaries])
            </div>
            <div id="image-summary" class="summary-content d-none">
                @include('admin.partials.summary', ['summaries' => $image_summaries])
            </div>
            <div id="video-summary" class="summary-content d-none">
                @include('admin.partials.summary', ['summaries' => $video_summaries])
            </div>

        </div>

        <div class="col-xxl-9 col-xl-8">
            <div class="i-card-md h-full">
                <div class="card--header text-end">
                    <h4 class="card-title" id="card-title">
                        {{ translate('Word Generation (Current Year)')}}
                    </h4>
                </div>
                <div class="card-body">
                    <div id="template-report"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="i-card-md">
                <div class="card-body">
                    <div class="search-action-area">
                        <div class="row align-items-center g-3">
                            <div class="col-md-5">
                                <ul class="nav nav-tabs style-2 mb-0" id="aiTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="content-tab" data-bs-toggle="tab"
                                            data-bs-target="#content" type="button" role="tab">
                                            {{ translate('AI Content') }}
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="image-tab" data-bs-toggle="tab" data-bs-target="#image"
                                            type="button" role="tab">
                                            {{ translate('AI Image') }}
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="video-tab" data-bs-toggle="tab" data-bs-target="#video"
                                            type="button" role="tab">
                                            {{ translate('AI Video') }}
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-7 d-flex gap-2 justify-content-end">
                                <div class="filter-wrapper">
                                    <button class="i-btn btn--primary btn--sm filter-btn" type="button">
                                        <i class="las la-filter"></i>
                                    </button>
                                    <div class="filter-dropdown">
                                        <form action="{{ route(Route::currentRouteName()) }}" method="get">
                                            <div class="form-inner">
                                                <input type="text" id="datePicker" name="date"
                                                    value="{{ request()->input('date') }}"
                                                    placeholder='{{ translate("Filter by date") }}'>
                                            </div>
                                            <div class="form-inner">
                                                <select name="user" id="user" class="user">
                                                    <option value="">{{ translate('Select User') }}</option>
                                                    @foreach(system_users() as $user)
                                                        <option {{ Arr::get($user, "username", null) == request()->input('user') ? 'selected' : "" }} value="{{ Arr::get($user, "username", null) }}">
                                                            {{ Arr::get($user, "name", null) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-inner">
                                                <select name="template" id="template" class="select2">
                                                    <option value="">{{ translate('Select Template') }}</option>
                                                    @foreach($templates as $template)
                                                        <option {{ $template->slug == request()->input('template') ? 'selected' : "" }} value="{{ $template->slug }}">
                                                            {{ $template->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="i-btn btn--md info w-100">
                                                <i class="las la-sliders-h"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route(Route::currentRouteName()) }}" class="i-btn btn--sm danger">
                                        <i class="las la-sync"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="aiTabContent">

                        <div class="tab-pane fade show active" id="content" role="tabpanel">
                            @include('admin.partials.loader')
                            <div class="table-container position-relative">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ translate('Template') }}</th>
                                            <th>{{ translate('Generated By') }}</th>
                                            <th>{{ translate('Generated On') }}</th>
                                            <th>{{ translate('Words') }}</th>
                                            <th>{{ translate('Options') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reports as $report)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <p>{{ $report->template?->name }}</p>
                                                </td>
                                                <td>
                                                    @php
                                                        $name = $report->user ? $report->user->name : @$report->admin->name;
                                                        $role = $report->user ? translate('System User') : translate('Admin');
                                                    @endphp
                                                    <span class="i-badge capsuled success">{{ $name }} ({{ $role }})</span>
                                                </td>
                                                <td>
                                                    {{ get_date_time($report->created_at) }}
                                                    <div>{{ diff_for_humans($report->created_at) }}</div>
                                                </td>
                                                <td>
                                                    <span class="i-badge capsuled success">{{ $report->total_words }}</span>
                                                </td>
                                                <td>
                                                    <div class="table-action">
                                                        <a href="javascript:void(0);" data-report="{{ $report }}"
                                                            class="pointer show-info icon-btn info">
                                                            <i class="las la-info"></i>
                                                        </a>
                                                        @if(check_permission('delete_report'))
                                                            <a href="javascript:void(0);"
                                                                data-href="{{ route('admin.template.report.destroy', $report->id) }}"
                                                                class="pointer delete-item icon-btn danger">
                                                                <i class="las la-trash-alt"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="border-bottom-0" colspan="6">
                                                    @include('admin.partials.not_found', ['custom_message' => "No Reports found!!"])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="Paginations">
                                {{ $reports->links() }}
                            </div>
                        </div>

                        <div class="tab-pane fade" id="image" role="tabpanel">
                            @include('admin.partials.loader')
                            <div class="table-container position-relative">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ translate('Template') }}</th>
                                            <th>{{ translate('Generated By') }}</th>
                                            <th>{{ translate('Generated On') }}</th>
                                            <th>{{ translate('Images') }}</th>
                                            <th>{{ translate('Options') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($imageReports as $report)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <p>{{ $report->template?->name }}</p>
                                                </td>
                                                <td>
                                                    @php
                                                        $name = $report->user ? $report->user->name : @$report->admin->name;
                                                        $role = $report->user ? translate('System User') : translate('Admin');
                                                    @endphp
                                                    <span class="i-badge capsuled success">{{ $name }} ({{ $role }})</span>
                                                </td>
                                                <td>
                                                    {{ get_date_time($report->created_at) }}
                                                    <div>{{ diff_for_humans($report->created_at) }}</div>
                                                </td>

                                                <td>
                                                    <div class="image-overlap-list">
                                                        @foreach($report->images ?? [] as $image)
                                                            <img src="{{ $image }}" alt="Image" class="overlap-img">
                                                        @endforeach
                                                        @if(count($report->images ?? []) > 3)
                                                            <div class="more-count">+{{ count($report->images) - 3 }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="table-action">

                                                        @if(check_permission('view_report'))
                                                            <a href="{{route('admin.template.report.details', $report->id)}}"
                                                                class="pointer icon-btn info">
                                                                <i class="las la-eye"></i></i>
                                                            </a>
                                                        @endif

                                                        <a href="javascript:void(0);" data-report="{{ $report }}"
                                                            data-type={{$report->type}} class="pointer show-info icon-btn info">
                                                            <i class="las la-info"></i>
                                                        </a>



                                                        @if(check_permission('delete_report'))
                                                            <a href="javascript:void(0);"
                                                                data-href="{{ route('admin.template.report.destroy', $report->id) }}"
                                                                class="pointer delete-item icon-btn danger">
                                                                <i class="las la-trash-alt"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="border-bottom-0" colspan="6">
                                                    @include('admin.partials.not_found', ['custom_message' => "No Image Reports found!!"])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="Paginations">
                                {{ $imageReports->links() }}
                            </div>
                        </div>

                        <div class="tab-pane fade" id="video" role="tabpanel">
                            @include('admin.partials.loader')
                            <div class="table-container position-relative">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ translate('Template') }}</th>
                                            <th>{{ translate('Generated By') }}</th>
                                            <th>{{ translate('Generated On') }}</th>
                                            <th>{{ translate('Videos') }}</th>
                                            <th>{{ translate('Options') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($videoReports as $report)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <p>{{ $report->template?->name }}</p>
                                                </td>
                                                <td>
                                                    @php
                                                        $name = $report->user ? $report->user->name : @$report->admin->name;
                                                        $role = $report->user ? translate('System User') : translate('Admin');
                                                    @endphp
                                                    <span class="i-badge capsuled success">{{ $name }} ({{ $role }})</span>
                                                </td>
                                                <td>
                                                    {{ get_date_time($report->created_at) }}
                                                    <div>{{ diff_for_humans($report->created_at) }}</div>
                                                </td>

                                                <td>
                                                    <div class="video-preview">
                                                        @if(isset($report->videos) && count($report->videos ?? []) === 1)
                                                            <video class="small-video" controls>
                                                                <source src="{{ $report->videos[0] }}" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        @elseif(isset($report->videos) && count($report->videos ?? []) > 1)
                                                            <div class="image-overlap-list">
                                                                @foreach($report->videos as $index => $video)
                                                                    @if($index < 3)
                                                                        <div class="overlap-video">
                                                                            <img src="{{ $video->thumbnail ?? asset('images/default-video-thumbnail.jpg') }}"
                                                                                alt="Video Thumbnail"
                                                                                class="overlap-img">
                                                                            <span class="play-icon">▶</span>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                                @if(count($report->videos) > 3)
                                                                    <div class="more-count">+{{ count($report->videos) - 3 }}</div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="table-action">

                                                        @if(check_permission('view_report'))
                                                            <a href="{{route('admin.template.report.details', $report->id)}}"
                                                                class="pointer icon-btn info">
                                                                <i class="las la-eye"></i></i>
                                                            </a>
                                                        @endif

                                                        <a href="javascript:void(0);" data-report="{{ $report }}"
                                                            data-type={{$report->type}} class="pointer show-info icon-btn info">
                                                            <i class="las la-info"></i>
                                                        </a>



                                                        @if(check_permission('delete_report'))
                                                            <a href="javascript:void(0);"
                                                                data-href="{{ route('admin.template.report.destroy', $report->id) }}"
                                                                class="pointer delete-item icon-btn danger">
                                                                <i class="las la-trash-alt"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="border-bottom-0" colspan="6">
                                                    @include('admin.partials.not_found', ['custom_message' => "No Image Reports found!!"])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="Paginations">
                                {{ $imageReports->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
@section('modal')
    @include('modal.delete_modal')
    @include('admin.partials.modal.template_report')
@endsection

@push('script-include')
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/moment.min.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/daterangepicker.min.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/init.js')}}"></script>
    <script src="{{asset('assets/global/js/apexcharts.js')}}"></script>
@endpush

@push('script-push')
    <script nonce="{{ csp_nonce() }}">
        (function ($) {
            "use strict";

            $(".select2").select2({});
            $(".user").select2({});

            $(document).on('click', '.show-info', function (e) {
                e.preventDefault();
                var modal = $('#report-info');
                var report = JSON.parse($(this).attr('data-report'));
                var reportType = $(this).attr('data-type');
                var cleanContent = DOMPurify.sanitize(report.content);
                var lists = "";

                if (reportType == 'image') {
                    modal.find('textarea[name="content"]').addClass('d-none');
                } if (reportType == 'video') {
                    modal.find('textarea[name="content"]').addClass('d-none');
                } else {
                    modal.find('textarea[name="content"]').removeClass('d-none');

                    modal.find('textarea[name="content"]').html(cleanContent);
                }

                lists = renderObject(report.open_ai_usage);
                $("#additionalInfo").html(lists);

                modal.modal('show');
            });

            function formatKey(key) {
                return key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
            }


            function renderObject(obj, prefix = '') {
                let html = '';
                for (var key in obj) {
                    let value = obj[key];
                    let label = prefix ? `${prefix} > ${formatKey(key)}` : formatKey(key);
                    if (typeof value === 'object' && value !== null) {
                        html += renderObject(value, '');
                    } else {
                        html += `<li class="list-group-item">${label} : ${value}</li>`;
                    }
                }
                return html;
            }

            var wordLabels = @json(array_keys($graph_data));
            var wordData = @json(array_values($graph_data));
            var imageLabels = @json(array_keys($image_graph_data));
            var imageData = @json(array_values($image_graph_data));
            var videoLabels = @json(array_keys($video_graph_data));
            var videoData = @json(array_values($video_graph_data));

            var chart = null;

            function renderChart(tab) {
                if (chart) {
                    chart.destroy();
                }

                let title = '';
                let seriesName = '';
                let labels = [];
                let data = [];

                switch (tab) {
                    case 'content':
                        title = "{{ translate('Word Generation (Current Year)') }}";
                        seriesName = "{{ translate('Total words') }}";
                        labels = wordLabels;
                        data = wordData;
                        break;

                    case 'image':
                        title = "{{ translate('Image Generation (Current Year)') }}";
                        seriesName = "{{ translate('Total images') }}";
                        labels = imageLabels;
                        data = imageData;
                        break;

                    case 'video':
                        title = "{{ translate('Video Generation (Current Year)') }}";
                        seriesName = "{{ translate('Total videos') }}";
                        labels = videoLabels;
                        data = videoData;
                        break;

                    default:
                        console.warn('Unknown tab type:', tab);
                        return;
                }

                var chartOptions = {
                    series: [{
                        name: seriesName,
                        data: data
                    }],
                    chart: {
                        nonce: "{{ csp_nonce() }}",
                        height: 365,
                        type: 'bar',
                        events: {
                            click: function (chart, w, e) { }
                        },
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '45%',
                            distributed: true,
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        show: false
                    },
                    xaxis: {
                        categories: labels,
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    }
                };

                $('#card-title').text(title);
                chart = new ApexCharts(document.querySelector("#template-report"), chartOptions);
                chart.render();
            }

            renderChart('content');

            $('#content-tab').on('shown.bs.tab', function () {
                renderChart('content');
                $('#word-summary').removeClass('d-none');
                $('#image-summary').addClass('d-none');
                $('#video-summary').addClass('d-none');
            });

            $('#image-tab').on('shown.bs.tab', function () {
                renderChart('image');
                $('#image-summary').removeClass('d-none');
                $('#word-summary').addClass('d-none');
                $('#video-summary').addClass('d-none');
            });

            $('#video-tab').on('shown.bs.tab', function () {
                renderChart('video');
                $('#video-summary').removeClass('d-none');
                $('#word-summary').addClass('d-none');
                $('#image-summary').addClass('d-none');
            });



            function updateActionButtons() {
                const checkedCount = document.querySelectorAll('.gallery-checkbox:checked').length;
                const actionButtons = document.getElementById('actionButtons');
                if (checkedCount > 0) {
                    actionButtons.classList.remove('d-none');
                    actionButtons.classList.add('d-block');
                } else {
                    actionButtons.classList.remove('d-block');
                    actionButtons.classList.add('d-none');
                }
            }

            document.getElementById('checkAll').addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.gallery-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateActionButtons();
            });

            document.querySelectorAll('.gallery-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    if (e.target.tagName.toLowerCase() !== 'input') {
                        const checkbox = this.querySelector('.gallery-checkbox');
                        checkbox.checked = !checkbox.checked;
                        updateActionButtons();
                    }
                });
            });

            document.querySelectorAll('.gallery-checkbox').forEach(checkbox => {
                checkbox.addEventListener('click', function (e) {
                    e.stopPropagation();
                    updateActionButtons();
                });
            });

        })(jQuery);
    </script>
@endpush
