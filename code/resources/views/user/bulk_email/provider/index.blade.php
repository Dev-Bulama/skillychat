@extends('layouts.master')
@section('content')

<div class="row g-3">
    <div class="col-md-5">
        <div class="i-card-md">
            <div class="card-header"><h5 class="card-title">{{translate('Add Email Provider')}}</h5></div>
            <div class="card-body">
                <form action="{{route('user.bulk-email.provider.store')}}" method="POST" id="emailProviderForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{translate('Provider')}} <span class="text-danger">*</span></label>
                        <select class="form-select" name="provider" required onchange="showEmailFields(this.value)">
                            <option value="">{{translate('Select...')}}</option>
                            @foreach($supportedDrivers as $key => $driver)
                            <option value="{{$key}}">{{$driver['label']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{translate('Label')}} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    @foreach($supportedDrivers as $key => $driver)
                    <div class="email-provider-fields d-none" id="email_fields_{{$key}}">
                        @foreach($driver['fields'] as $field)
                        <div class="mb-2">
                            <label class="form-label">{{$field['label']}} @if($field['required'])<span class="text-danger">*</span>@endif</label>
                            <input type="{{$field['type']}}" class="form-control" name="credentials[{{$field['key']}}]">
                        </div>
                        @endforeach
                    </div>
                    @endforeach

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_default" id="emailDefault" value="1">
                        <label class="form-check-label" for="emailDefault">{{translate('Set as default')}}</label>
                    </div>

                    <button type="submit" class="i-btn primary btn--md">
                        <i class="bi bi-plus-circle me-1"></i> {{translate('Add Provider')}}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="i-card-md">
            <div class="card-header"><h5 class="card-title">{{translate('Your Email Providers')}}</h5></div>
            <div class="card-body px-0">
                @forelse($providers as $provider)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <div>
                        <strong>{{$provider->name}}</strong>
                        <span class="badge bg-secondary ms-1">{{$provider->provider}}</span>
                        @if($provider->is_default)<span class="badge bg-primary ms-1">{{translate('Default')}}</span>@endif
                    </div>
                    <div class="d-flex gap-2">
                        <button class="i-btn info btn--sm" data-bs-toggle="modal" data-bs-target="#testEmailModal{{$provider->id}}">
                            <i class="bi bi-send"></i> {{translate('Test')}}
                        </button>
                        <a href="{{route('user.bulk-email.provider.destroy', $provider->id)}}"
                           onclick="return confirm('{{translate('Remove?')}}')"
                           class="i-btn danger btn--sm">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>

                <div class="modal fade" id="testEmailModal{{$provider->id}}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <form action="{{route('user.bulk-email.provider.test', $provider->id)}}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">{{translate('Test')}} {{$provider->name}}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="email" class="form-control" name="test_email"
                                        placeholder="{{translate('Test email address')}}" required>
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
                    <i class="bi bi-envelope fs-2 d-block mb-2"></i>
                    {{translate('No providers configured.')}}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('script-push')
<script nonce="{{csp_nonce()}}">
function showEmailFields(provider) {
    document.querySelectorAll('.email-provider-fields').forEach(el => el.classList.add('d-none'));
    if (provider) {
        const el = document.getElementById('email_fields_' + provider);
        if (el) el.classList.remove('d-none');
    }
}
</script>
@endpush

@endsection
