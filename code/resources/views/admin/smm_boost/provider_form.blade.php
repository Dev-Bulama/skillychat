@extends('admin.layouts.master')
@section('content')
<form action="{{ $provider ? route('admin.smm.providers.update', $provider->id) : route('admin.smm.providers.store') }}"
    method="post" class="add-listing-form">
    @csrf
    @if($provider) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{ $provider ? translate('Edit Provider') : translate('Add SMM Provider') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-inner">
                                <label>{{ translate('Provider Name') }} <small class="text-danger">*</small></label>
                                <input type="text" name="name" required placeholder="{{ translate('e.g. JustAnotherPanel') }}"
                                    value="{{ old('name', $provider?->name) }}">
                                <small class="text-muted">{{ translate('A friendly name to identify this provider.') }}</small>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="form-inner">
                                <label>{{ translate('API URL') }} <small class="text-danger">*</small></label>
                                <input type="url" name="api_url" required placeholder="https://provider.com/api/v2"
                                    value="{{ old('api_url', $provider?->api_url) }}">
                                <small class="text-muted">{{ translate('The base endpoint URL provided by the SMM panel.') }}</small>
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="form-inner">
                                <label>{{ translate('API Key') }} <small class="text-danger">*</small></label>
                                <input type="text" name="api_key" required placeholder="{{ translate('Your API key') }}"
                                    value="{{ old('api_key', $provider?->api_key) }}">
                                <small class="text-muted">{{ translate('Found in your account settings on the provider\'s website.') }}</small>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <div class="d-flex align-items-center gap-3">
                                <label class="mb-0">{{ translate('Active') }}</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" id="status"
                                        {{ old('status', $provider?->isActive() ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="i-btn btn--md btn--primary" data-anim="ripple">
                                {{ $provider ? translate('Update Provider') : translate('Save Provider') }}
                            </button>
                            <a href="{{ route('admin.smm.providers') }}" class="i-btn btn--md btn--outline ms-2">
                                {{ translate('Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Quick Reference') }}</h4>
                </div>
                <div class="card-body">
                    <p class="fs-14 text-muted mb-3">{{ translate('Popular providers and their API endpoints:') }}</p>
                    @foreach([
                        ['JustAnotherPanel', 'https://justanotherpanel.com/api/v2'],
                        ['SMMWorld', 'https://smmworld.net/api/v2'],
                        ['SafeSMM', 'https://safesmm.com/api/v2'],
                        ['PeakSMM', 'https://peaksmm.com/api/v2'],
                        ['ExoBooster', 'https://www.exobooster.site/api/v2'],
                        ['ExoSupplier', 'https://exosupplier.com/api'],
                    ] as [$name, $url])
                    <div class="mb-3 pb-2 border-bottom">
                        <strong class="d-block fs-14">{{ $name }}</strong>
                        <code class="fs-12 text-break">{{ $url }}</code>
                        <button type="button" class="btn btn-link btn-sm p-0 mt-1 fill-url"
                            data-url="{{ $url }}" data-name="{{ $name }}">
                            {{ translate('Use this') }}
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
document.querySelectorAll('.fill-url').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var url = this.dataset.url;
        var name = this.dataset.name;
        var nameInput = document.querySelector('[name="name"]');
        var urlInput = document.querySelector('[name="api_url"]');
        if (!nameInput.value) nameInput.value = name;
        urlInput.value = url;
    });
});
</script>
@endpush
