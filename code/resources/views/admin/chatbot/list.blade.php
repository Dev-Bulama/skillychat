@extends('admin.layouts.master')
@push('style-include')
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="i-card-md">
        <div class="card-body">
            <div class="search-action-area">
                <div class="row g-3">
                    <div class="col-md-6 d-flex justify-content-start">
                        <div class="action">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#bulkModal" class="i-btn btn--sm danger">
                                <i class="las la-trash me-1"></i>  {{translate('Bulk Action')}}
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="search-area">
                            <form action="{{route(Route::currentRouteName())}}" method="get">
                                <div class="form-inner">
                                    <input type="text" id="datePicker" name="date" value="{{request()->input('date')}}" placeholder='{{translate("Filter by date")}}'>
                                </div>
                                <div class="form-inner">
                                    <input name="search" value="{{request()->input('search')}}" type="search" placeholder="{{translate('Search by name or user')}}">
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
                            <th scope="col">
                                <input class="check-all" type="checkbox" id="checkAll">
                            </th>
                            <th scope="col">{{translate('Name')}}</th>
                            <th scope="col">{{translate('User')}}</th>
                            <th scope="col">{{translate('AI Provider')}}</th>
                            <th scope="col">{{translate('Widget Type')}}</th>
                            <th scope="col">{{translate('Human Takeover')}}</th>
                            <th scope="col">{{translate('Status')}}</th>
                            <th scope="col">{{translate('Created At')}}</th>
                            <th scope="col">{{translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chatbots as $chatbot)
                            <tr>
                                <td data-label="#">
                                    <input type="checkbox" value="{{$chatbot->id}}" name="ids[]" class="data-checkbox">
                                </td>
                                <td data-label="{{translate('Name')}}">
                                    <a href="{{ route('admin.chatbot.show', $chatbot->uid) }}" class="text-primary fw-bold">
                                        {{ $chatbot->name }}
                                    </a>
                                    <div class="text-muted small">{{ $chatbot->uid }}</div>
                                </td>
                                <td data-label="{{translate('User')}}">
                                    @if($chatbot->user)
                                        <a href="{{ route('admin.user.show', $chatbot->user->id) }}" class="text-info">
                                            {{ $chatbot->user->name }}
                                        </a>
                                        <div class="text-muted small">{{ $chatbot->user->email }}</div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td data-label="{{translate('AI Provider')}}">
                                    @php
                                        $providerColor = match($chatbot->ai_provider) {
                                            'openai' => 'success',
                                            'gemini' => 'info',
                                            'claude' => 'primary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge--{{ $providerColor }}">
                                        {{ ucfirst($chatbot->ai_provider) }}
                                    </span>
                                </td>
                                <td data-label="{{translate('Widget Type')}}">
                                    <span class="badge badge--dark">
                                        {{ ucfirst($chatbot->widget_type) }}
                                    </span>
                                </td>
                                <td data-label="{{translate('Human Takeover')}}">
                                    <span class="badge badge--{{ $chatbot->human_takeover_enabled ? 'success' : 'secondary' }}">
                                        {{ $chatbot->human_takeover_enabled ? translate('Enabled') : translate('Disabled') }}
                                    </span>
                                </td>
                                <td data-label="{{translate('Status')}}">
                                    <div class="form-check form-switch switch-center">
                                        <input
                                            {{ check_permission('update_content') ? '' : 'disabled' }}
                                            type="checkbox"
                                            class="status-update form-check-input"
                                            data-column="status"
                                            data-route="{{ route('admin.chatbot.update.status') }}"
                                            data-model="Chatbot"
                                            data-status="{{ $chatbot->status == '1' ? '0' : '1' }}"
                                            data-id="{{ $chatbot->id }}"
                                            {{ $chatbot->status == '1' ? 'checked' : '' }}
                                            id="status-switch-{{ $chatbot->id }}">
                                        <label class="form-check-label" for="status-switch-{{ $chatbot->id }}"></label>
                                    </div>
                                </td>
                                <td data-label="{{translate('Created At')}}">
                                    {{ diff_for_humans($chatbot->created_at) }}
                                    <div class="text-muted small">{{ get_date_time($chatbot->created_at) }}</div>
                                </td>
                                <td data-label="{{translate('Options')}}">
                                    <div class="d-flex align-items-center justify-content-md-end justify-content-start gap-3">
                                        <a class="i-btn primary--btn btn--sm" href="{{ route('admin.chatbot.show', $chatbot->uid) }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                    </div>
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
                {{ $chatbots->links() }}
            </div>
        </div>
    </div>

    <!-- Bulk Modal -->
    <div class="modal fade" id="bulkModal" tabindex="-1" aria-labelledby="bulkModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkModalLabel">{{ translate('Bulk Action') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.chatbot.bulk') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="action">{{ translate('Select Action') }}</label>
                            <select name="action" id="action" class="form-select" required>
                                <option value="">{{ translate('Select Action') }}</option>
                                <option value="activate">{{ translate('Activate') }}</option>
                                <option value="deactivate">{{ translate('Deactivate') }}</option>
                                <option value="delete">{{ translate('Delete') }}</option>
                            </select>
                        </div>
                        <div id="selected-ids-container"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Apply') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-include')
    <script src="{{asset('assets/global/js/datepicker/moment.min.js')}}"></script>
    <script src="{{asset('assets/global/js/datepicker/daterangepicker.min.js')}}"></script>
    <script src="{{asset('assets/global/js/datepicker/init.js')}}"></script>
@endpush

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    (function($){
        "use strict";

        // Bulk action modal
        $('#bulkModal').on('show.bs.modal', function (e) {
            const selectedIds = [];
            $('.data-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('{{ translate("Please select at least one chatbot") }}');
                e.preventDefault();
                return;
            }

            let html = '';
            selectedIds.forEach(id => {
                html += `<input type="hidden" name="ids[]" value="${id}">`;
            });
            $('#selected-ids-container').html(html);
        });

        // Check all functionality
        $(document).on('change', '#checkAll', function(){
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

    })(jQuery);
</script>
@endpush
