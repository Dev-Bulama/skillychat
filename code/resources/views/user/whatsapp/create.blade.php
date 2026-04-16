@extends('layouts.master')
@php
    $appId       = site_settings('whatsapp_default_app_id', '');
    $configId    = site_settings('whatsapp_embedded_config_id', '');
    $adminToken  = site_settings('whatsapp_default_access_token', '');
    $adminWabaId = site_settings('whatsapp_default_waba_id', '');
    $adminAppId  = site_settings('whatsapp_default_app_id', '');
    $appSecret   = site_settings('whatsapp_app_secret', '');
    $hasEmbedded = !empty($appId) && !empty($configId) && !empty($appSecret);
    $hasAdmin    = !empty($adminToken) && !empty($adminWabaId);
@endphp
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8">

        {{-- ── Embedded Signup (primary method) ─────────────────────────── --}}
        @if($hasEmbedded)
        <div class="i-card-md mb-4">
            <div class="card--header">
                <div>
                    <h4 class="card-title">
                        <i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>
                        {{ translate('Connect WhatsApp Business') }}
                    </h4>
                    <p class="text-muted mb-0 fs-13">{{ translate('Connect your own WhatsApp Business account in 2 minutes — no manual setup required.') }}</p>
                </div>
                <a href="{{ route('user.whatsapp.documentation') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-book me-1"></i>{{ translate('Guide') }}
                </a>
            </div>
            <div class="card-body">

                {{-- Step indicator --}}
                <div class="d-flex align-items-center gap-2 mb-4 fs-13 text-muted">
                    <span id="step1Indicator" class="badge rounded-pill bg-primary">1. Authorize</span>
                    <i class="bi bi-chevron-right"></i>
                    <span id="step2Indicator" class="badge rounded-pill bg-light text-muted border">2. Pick Number</span>
                    <i class="bi bi-chevron-right"></i>
                    <span id="step3Indicator" class="badge rounded-pill bg-light text-muted border">3. Done</span>
                </div>

                {{-- Step 1: Facebook authorize button --}}
                <div id="stepAuthorize">
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width:72px;height:72px;">
                                <i class="bi bi-whatsapp fs-1" style="color:#25d366;"></i>
                            </div>
                            <h6 class="fw-semibold mb-1">{{ translate('One-Click WhatsApp Setup') }}</h6>
                            <p class="text-muted fs-13 mb-4 mx-auto" style="max-width:420px;">
                                {{ translate('Click the button below to open a secure Meta popup. Log in with the Facebook account that manages your WhatsApp Business number.') }}
                            </p>
                        </div>

                        <button type="button" id="embeddedSignupBtn" onclick="launchEmbeddedSignup()"
                            class="i-btn btn--md btn--primary capsuled px-5">
                            <i class="bi bi-facebook me-2"></i>{{ translate('Connect with Facebook') }}
                        </button>

                        <div id="embeddedError" class="alert alert-danger mt-3 d-none fs-13"></div>
                        <div id="embeddedLoading" class="mt-3 d-none">
                            <div class="spinner-border spinner-border-sm text-success me-2"></div>
                            <span class="text-muted fs-13">{{ translate('Fetching your business accounts…') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Phone number selection --}}
                <div id="stepPickPhone" class="d-none">
                    <h6 class="fw-semibold mb-3">{{ translate('Select Your WhatsApp Business Number') }}</h6>
                    <div id="phoneList" class="row g-3 mb-4"></div>

                    <form id="embeddedCompleteForm">
                        @csrf
                        <input type="hidden" name="phone_number_id" id="sel_phone_number_id">
                        <input type="hidden" name="phone_number"    id="sel_phone_number">
                        <input type="hidden" name="waba_id"         id="sel_waba_id">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ translate('Account Label') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="sel_name" class="form-control"
                                    placeholder="{{ translate('e.g. My Business WhatsApp') }}" required>
                                <small class="text-muted fs-12">{{ translate('A label to identify this account in your dashboard') }}</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ translate('Welcome Message') }}</label>
                                <textarea name="welcome_message" class="form-control" rows="2"
                                    placeholder="{{ site_settings('whatsapp_default_welcome_message', translate('Hi! How can we help you today?')) }}">{{ site_settings('whatsapp_default_welcome_message') }}</textarea>
                                <small class="text-muted fs-12">{{ translate('Auto-sent to first-time contacts') }}</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ translate('Fallback Message') }}</label>
                                <textarea name="fallback_message" class="form-control" rows="2"
                                    placeholder="{{ site_settings('whatsapp_default_fallback_message', translate("Sorry, I didn't understand that.")) }}">{{ site_settings('whatsapp_default_fallback_message') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="ai_enabled" id="ai_enabled_emb" value="1">
                                    <label class="form-check-label fw-semibold" for="ai_enabled_emb">{{ translate('Enable AI Auto-Reply') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6" id="chatbotRowEmb" style="display:none;">
                                <select name="chatbot_id" class="form-select">
                                    <option value="">{{ translate('-- Select Chatbot --') }}</option>
                                    @foreach($chatbots as $bot)
                                    <option value="{{ $bot->id }}">{{ $bot->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="button" id="completeBtn" onclick="completeSignup()"
                                class="i-btn btn--md btn--primary capsuled" disabled>
                                <i class="bi bi-check-circle me-1"></i>{{ translate('Finish Setup') }}
                            </button>
                            <button type="button" onclick="resetFlow()" class="i-btn btn--md btn--outline capsuled">
                                {{ translate('Start Over') }}
                            </button>
                        </div>
                        <div id="completeError" class="alert alert-danger mt-3 d-none fs-13"></div>
                        <div id="completeLoading" class="mt-3 d-none">
                            <div class="spinner-border spinner-border-sm text-success me-2"></div>
                            <span class="text-muted fs-13">{{ translate('Saving account…') }}</span>
                        </div>
                    </form>
                </div>

            </div>
        </div>
        @endif

        {{-- ── Manual / Advanced fallback ─────────────────────────────────── --}}
        <div class="i-card-md">
            <div class="card--header">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-gear me-2"></i>
                    {{ $hasEmbedded ? translate('Advanced — Manual Setup') : translate('Connect WhatsApp Account') }}
                </h5>
                @if($hasEmbedded)
                <small class="text-muted">{{ translate('Use this only if the one-click method above doesn\'t work for you.') }}</small>
                @endif
            </div>
            <div class="card-body">
                @if($hasAdmin)
                <div class="alert mb-3" style="background:#f0fdf4;border-left:4px solid #25d366;padding:12px 16px;border-radius:8px;">
                    <p class="fw-semibold mb-0 fs-13"><i class="bi bi-check-circle me-1" style="color:#16a34a;"></i>
                        {{ translate('Platform API is configured. Only your Phone Number ID is needed below.') }}
                    </p>
                </div>
                @endif

                <form action="{{ route('user.whatsapp.store') }}" method="post">
                    @csrf
                    @if($hasAdmin)
                    <input type="hidden" name="waba_id"      value="{{ $adminWabaId }}">
                    <input type="hidden" name="access_token" value="{{ $adminToken }}">
                    <input type="hidden" name="app_id"       value="{{ $adminAppId }}">
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Account Label') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="{{ translate('e.g. My Business') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('WhatsApp Business Phone Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number') }}" placeholder="+2348012345678">
                            @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Phone Number ID') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number_id" class="form-control @error('phone_number_id') is-invalid @enderror"
                                value="{{ old('phone_number_id') }}" placeholder="e.g. 123456789012345">
                            <small class="text-muted fs-12">{{ translate('Meta → WhatsApp → Getting Started → Phone number ID (next to your number)') }}</small>
                            @error('phone_number_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(!$hasAdmin)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('WABA ID') }}</label>
                            <input type="text" name="waba_id" class="form-control" value="{{ old('waba_id') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('App ID') }}</label>
                            <input type="text" name="app_id" class="form-control" value="{{ old('app_id') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Access Token') }} <span class="text-danger">*</span></label>
                            <input type="text" name="access_token" class="form-control font-monospace" value="{{ old('access_token') }}" placeholder="EAAxxxxxxxxxx...">
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Welcome Message') }}</label>
                            <textarea name="welcome_message" class="form-control" rows="2">{{ old('welcome_message', site_settings('whatsapp_default_welcome_message')) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Fallback Message') }}</label>
                            <textarea name="fallback_message" class="form-control" rows="2">{{ old('fallback_message', site_settings('whatsapp_default_fallback_message')) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="ai_enabled" id="ai_enabled" value="1" {{ old('ai_enabled') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="ai_enabled">{{ translate('Enable AI Auto-Reply') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="chatbotRow" style="{{ old('ai_enabled') ? '' : 'display:none;' }}">
                            <select name="chatbot_id" class="form-select">
                                <option value="">{{ translate('-- Select Chatbot --') }}</option>
                                @foreach($chatbots as $bot)
                                <option value="{{ $bot->id }}" {{ old('chatbot_id') == $bot->id ? 'selected' : '' }}>{{ $bot->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-whatsapp me-1"></i>{{ translate('Connect WhatsApp') }}
                            </button>
                            <a href="{{ route('user.whatsapp.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('script-push')
{{-- Meta Facebook SDK --}}
@if($hasEmbedded)
<script nonce="{{ csp_nonce() }}">
window.fbAsyncInit = function() {
    FB.init({
        appId     : '{{ $appId }}',
        autoLogAppEvents: true,
        xfbml     : true,
        version   : 'v19.0'
    });
};
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = 'https://connect.facebook.net/en_US/sdk.js';
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

var selectedPhone = null;

function launchEmbeddedSignup() {
    document.getElementById('embeddedError').classList.add('d-none');
    FB.login(function(response) {
        if (response.authResponse && response.authResponse.code) {
            showLoading(true);
            fetch('{{ route("user.whatsapp.embedded-signup") }}', {
                method : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: response.authResponse.code })
            })
            .then(r => r.json())
            .then(data => {
                showLoading(false);
                if (data.success) {
                    renderPhones(data.phones);
                } else {
                    showError('embeddedError', data.error || 'Connection failed.');
                }
            })
            .catch(() => {
                showLoading(false);
                showError('embeddedError', 'Network error. Please try again.');
            });
        } else {
            showError('embeddedError', 'Authorization cancelled or denied.');
        }
    }, {
        config_id                    : '{{ $configId }}',
        response_type                : 'code',
        override_default_response_type: true,
        extras: { setup: {}, featureType: '', sessionInfoVersion: '3' }
    });
}

function renderPhones(phones) {
    document.getElementById('stepAuthorize').classList.add('d-none');
    document.getElementById('stepPickPhone').classList.remove('d-none');
    document.getElementById('step1Indicator').className = 'badge rounded-pill bg-success';
    document.getElementById('step2Indicator').className = 'badge rounded-pill bg-primary';

    var html = '';
    phones.forEach(function(p, i) {
        html += `<div class="col-md-6">
            <div class="phone-card p-3 rounded-3 border cursor-pointer h-100" data-idx="${i}"
                onclick="selectPhone(this, ${JSON.stringify(p).replace(/"/g, '&quot;')})"
                style="cursor:pointer;transition:border-color .2s;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width:44px;height:44px;min-width:44px;">
                        <i class="bi bi-whatsapp fs-5 text-success"></i>
                    </div>
                    <div>
                        <div class="fw-semibold fs-14">${p.verified_name || p.phone_number}</div>
                        <div class="text-muted fs-12">${p.phone_number}</div>
                        <small class="text-muted fs-11">${p.waba_name}</small>
                    </div>
                </div>
            </div>
        </div>`;
    });
    document.getElementById('phoneList').innerHTML = html;
}

function selectPhone(el, phone) {
    document.querySelectorAll('.phone-card').forEach(function(c) {
        c.style.borderColor = '';
        c.style.background = '';
    });
    el.style.borderColor = '#25d366';
    el.style.background = '#f0fdf4';

    selectedPhone = phone;
    document.getElementById('sel_phone_number_id').value = phone.phone_number_id;
    document.getElementById('sel_phone_number').value    = phone.phone_number;
    document.getElementById('sel_waba_id').value         = phone.waba_id;
    document.getElementById('sel_name').value            = phone.verified_name || '';
    document.getElementById('completeBtn').disabled = false;
}

function completeSignup() {
    if (!selectedPhone) return;
    document.getElementById('completeError').classList.add('d-none');
    document.getElementById('completeLoading').classList.remove('d-none');
    document.getElementById('completeBtn').disabled = true;

    var form = document.getElementById('embeddedCompleteForm');
    var data = {
        _token         : '{{ csrf_token() }}',
        name           : form.querySelector('[name=name]').value,
        phone_number_id: form.querySelector('[name=phone_number_id]').value,
        phone_number   : form.querySelector('[name=phone_number]').value,
        waba_id        : form.querySelector('[name=waba_id]').value,
        welcome_message: form.querySelector('[name=welcome_message]').value,
        fallback_message: form.querySelector('[name=fallback_message]').value,
        ai_enabled     : form.querySelector('[name=ai_enabled]').checked ? 1 : 0,
        chatbot_id     : form.querySelector('[name=chatbot_id]')?.value || '',
    };

    fetch('{{ route("user.whatsapp.embedded-complete") }}', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body   : JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('completeLoading').classList.add('d-none');
        if (data.success) {
            document.getElementById('step2Indicator').className = 'badge rounded-pill bg-success';
            document.getElementById('step3Indicator').className = 'badge rounded-pill bg-success';
            window.location.href = data.redirect;
        } else {
            document.getElementById('completeBtn').disabled = false;
            showError('completeError', data.error || 'Failed to save account.');
        }
    })
    .catch(() => {
        document.getElementById('completeLoading').classList.add('d-none');
        document.getElementById('completeBtn').disabled = false;
        showError('completeError', 'Network error. Please try again.');
    });
}

function resetFlow() {
    selectedPhone = null;
    document.getElementById('stepPickPhone').classList.add('d-none');
    document.getElementById('stepAuthorize').classList.remove('d-none');
    document.getElementById('step1Indicator').className = 'badge rounded-pill bg-primary';
    document.getElementById('step2Indicator').className = 'badge rounded-pill bg-light text-muted border';
    document.getElementById('step3Indicator').className = 'badge rounded-pill bg-light text-muted border';
}

function showLoading(on) {
    document.getElementById('embeddedLoading').classList.toggle('d-none', !on);
    document.getElementById('embeddedSignupBtn').disabled = on;
}

function showError(id, msg) {
    var el = document.getElementById(id);
    el.textContent = msg;
    el.classList.remove('d-none');
}
</script>
@endif

<script nonce="{{ csp_nonce() }}">
document.getElementById('ai_enabled')?.addEventListener('change', function() {
    document.getElementById('chatbotRow').style.display = this.checked ? '' : 'none';
});
document.getElementById('ai_enabled_emb')?.addEventListener('change', function() {
    document.getElementById('chatbotRowEmb').style.display = this.checked ? '' : 'none';
});
</script>
@endpush
