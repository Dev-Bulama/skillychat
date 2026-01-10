@extends('admin.layouts.master')
@push('style-include')
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="i-card-md">
        <div class="card-body">
            <div class="search-action-area">
                <div class="row g-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <div class="search-area">
                            <form action="{{route(Route::currentRouteName())}}" method="get">
                                <div class="form-inner">
                                    <input type="text" id="datePicker" name="date" value="{{request()->input('date')}}" placeholder='{{translate("Filter by date")}}'>
                                </div>
                                <div class="form-inner">
                                    <select name="chatbot" class="form-select">
                                        <option value="">{{ translate('All Chatbots') }}</option>
                                        @foreach($chatbots as $chatbot)
                                            <option value="{{ $chatbot->uid }}" {{ request()->input('chatbot') == $chatbot->uid ? 'selected' : '' }}>
                                                {{ $chatbot->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-inner">
                                    <select name="status" class="form-select">
                                        <option value="">{{ translate('All Statuses') }}</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ request()->input('status') == $status ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-inner">
                                    <input name="search" value="{{request()->input('search')}}" type="search" placeholder="{{translate('Search by visitor ID')}}">
                                </div>
                                <button class="i-btn btn--sm info">
                                    <i class="las la-sliders-h"></i>
                                </button>
                                <a href="{{route(Route::currentRouteName())}}" class="i-btn btn--sm danger">
                                    <i class="las la-sync"></i>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container position-relative">
                @include('admin.partials.loader')
                <table>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{translate('Chatbot')}}</th>
                            <th scope="col">{{translate('Owner')}}</th>
                            <th scope="col">{{translate('Visitor ID')}}</th>
                            <th scope="col">{{translate('Status')}}</th>
                            <th scope="col">{{translate('Assigned Agent')}}</th>
                            <th scope="col">{{translate('Started')}}</th>
                            <th scope="col">{{translate('Last Activity')}}</th>
                            <th scope="col">{{translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversations as $conversation)
                            <tr>
                                <td data-label="#">
                                    {{$loop->iteration}}
                                </td>
                                <td data-label="{{translate('Chatbot')}}">
                                    <a href="{{ route('admin.chatbot.show', $conversation->chatbot->uid) }}" class="text-primary fw-bold">
                                        {{ $conversation->chatbot->name }}
                                    </a>
                                    <div class="text-muted small">{{ $conversation->chatbot->uid }}</div>
                                </td>
                                <td data-label="{{translate('Owner')}}">
                                    @if($conversation->chatbot->user)
                                        <a href="{{ route('admin.user.show', $conversation->chatbot->user->id) }}" class="text-info">
                                            {{ $conversation->chatbot->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td data-label="{{translate('Visitor ID')}}">
                                    <span class="font-monospace small">
                                        {{ substr($conversation->visitor_id, 0, 16) }}...
                                    </span>
                                </td>
                                <td data-label="{{translate('Status')}}">
                                    @php
                                        $statusBadge = match($conversation->status) {
                                            'ai_active' => 'primary',
                                            'human_requested' => 'warning',
                                            'human_active' => 'info',
                                            'resolved' => 'success',
                                            'closed' => 'secondary',
                                            default => 'dark'
                                        };
                                    @endphp
                                    <span class="badge badge--{{ $statusBadge }}">
                                        {{ ucwords(str_replace('_', ' ', $conversation->status)) }}
                                    </span>
                                </td>
                                <td data-label="{{translate('Assigned Agent')}}">
                                    @if($conversation->assignedAgent && $conversation->assignedAgent->user)
                                        {{ $conversation->assignedAgent->user->name }}
                                        <div class="text-muted small">
                                            <i class="las la-clock"></i> {{ diff_for_humans($conversation->taken_over_at) }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-label="{{translate('Started')}}">
                                    {{ diff_for_humans($conversation->created_at) }}
                                    <div class="text-muted small">{{ get_date_time($conversation->created_at) }}</div>
                                </td>
                                <td data-label="{{translate('Last Activity')}}">
                                    {{ diff_for_humans($conversation->updated_at) }}
                                </td>
                                <td data-label="{{translate('Options')}}">
                                    <a class="i-btn primary--btn btn--sm" href="{{ route('admin.chatbot.conversation.details', $conversation->uid) }}">
                                        <i class="las la-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="9">{{ translate('No Data Found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="Paginations">
                {{ $conversations->links() }}
            </div>
        </div>
    </div>
@endsection

@push('script-include')
    <script src="{{asset('assets/global/js/datepicker/moment.min.js')}}"></script>
    <script src="{{asset('assets/global/js/datepicker/daterangepicker.min.js')}}"></script>
    <script src="{{asset('assets/global/js/datepicker/init.js')}}"></script>
@endpush
