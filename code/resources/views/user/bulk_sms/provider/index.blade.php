@extends('layouts.master')
@section('content')

<div class="row g-3">
    {{-- Add Provider --}}
    <div class="col-md-5">
        <div class="i-card-md">
            <div class="card-header">
                <h5 class="card-title">{{translate('Add SMS Provider')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('user.bulk-sms.provider.store')}}" method="POST" id="providerForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{translate('Provider')}} <span class="text-danger">*</span></label>
                        <select class="form-select" name="provider" id="providerSelect" required onchange="showFields(this.value)">
                            <option value="">{{translate('Select provider...')}}</option>
                            @foreach($supportedDrivers as $key => $driver)
                            <option value="{{$key}}">{{$driver['label']}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{translate('Label / Nickname')}} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="{{translate('e.g. My Termii Account')}}">
                    </div>

                    @foreach($supportedDrivers as $key => $driver)
                    <div class="provider-fields d-none" id="fields_{{$key}}">
                        @foreach($driver['fields'] as $field)
                        <div class="mb-2">
                            <label class="form-label">
                                {{$field['label']}}
                                @if($field['required'])<span class="text-danger">*</span>@endif
                            </label>
                            <input type="{{$field['type']}}" class="form-control"
                                name="credentials[{{$field['key']}}]"
                                {{$field['required'] ? '' : ''}}>
                        </div>
                        @endforeach
                    </div>
                    @endforeach

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="1">
                        <label class="form-check-label" for="is_default">{{translate('Set as default')}}</label>
                    </div>

                    <button type="submit" class="i-btn primary btn--md">
                        <i class="bi bi-plus-circle me-1"></i> {{translate('Add Provider')}}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Provider List --}}
    <div class="col-md-7">
        <div class="i-card-md">
            <div class="card-header">
                <h5 class="card-title">{{translate('Your SMS Providers')}}</h5>
            </div>
            <div class="card-body px-0">
                @forelse($providers as $provider)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <div>
                        <strong>{{$provider->name}}</strong>
                        <span class="badge bg-secondary ms-1">{{$provider->provider}}</span>
                        @if($provider->is_default)
                            <span class="badge bg-primary ms-1">{{translate('Default')}}</span>
                        @endif
                        @if(!$provider->is_active)
                            <span class="badge bg-danger ms-1">{{translate('Inactive')}}</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        {{-- Test form --}}
                        <button class="i-btn info btn--sm" data-bs-toggle="modal" data-bs-target="#testModal{{$provider->id}}">
                            <i class="bi bi-send"></i> {{translate('Test')}}
                        </button>
                        <a href="{{route('user.bulk-sms.provider.destroy', $provider->id)}}"
                           onclick="return confirm('{{translate('Remove this provider?')}}')"
                           class="i-btn danger btn--sm">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>

                {{-- Test Modal --}}
                <div class="modal fade" id="testModal{{$provider->id}}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <form action="{{route('user.bulk-sms.provider.test', $provider->id)}}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">{{translate('Test')}} {{$provider->name}}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="text" class="form-control" name="test_phone"
                                        placeholder="{{translate('Phone number')}}" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="i-btn success btn--sm">{{translate('Send Test')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted px-3">
                    <i class="bi bi-phone fs-2 d-block mb-2"></i>
                    {{translate('No providers configured.')}}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('script-push')
<script nonce="{{csp_nonce()}}">
function showFields(provider) {
    document.querySelectorAll('.provider-fields').forEach(el => el.classList.add('d-none'));
    if (provider) {
        const el = document.getElementById('fields_' + provider);
        if (el) el.classList.remove('d-none');
    }
}
</script>
@endpush

@endsection
