@extends('layouts.master')
@php
    $adminAppId      = site_settings('whatsapp_default_app_id', '');
    $adminWabaId     = site_settings('whatsapp_default_waba_id', '');
    $adminToken      = site_settings('whatsapp_default_access_token', '');
    $adminPhoneId    = site_settings('whatsapp_default_phone_number_id', '');
    $adminWelcome    = site_settings('whatsapp_default_welcome_message', '');
    $adminFallback   = site_settings('whatsapp_default_fallback_message', '');
    $hasAdminConfig  = !empty($adminToken) && !empty($adminWabaId);
@endphp
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>{{ translate('Connect WhatsApp Account') }}</h4>
                <a href="{{ route('user.whatsapp.documentation') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-book me-1"></i>{{ translate('Setup Guide') }}
                </a>
            </div>
            <div class="card-body">

                @if($hasAdminConfig)
                {{-- Simplified mode: admin already configured API --}}
                <div class="alert mb-4" style="background:#f0fdf4;border-left:4px solid #25d366;padding:14px 16px;border-radius:8px;">
                    <p class="fw-semibold mb-1 fs-14"><i class="bi bi-check-circle me-1" style="color:#16a34a;"></i>{{ translate('Platform WhatsApp API is Ready') }}</p>
                    <p class="fs-13 text-muted mb-0">{{ translate('Our platform is connected to WhatsApp Business API. Enter your WhatsApp Business phone number and Phone Number ID, then click Connect.') }}</p>
                </div>

                <form action="{{ route('user.whatsapp.store') }}" method="post">
                    @csrf
                    {{-- Hidden: use admin defaults for credentials --}}
                    <input type="hidden" name="waba_id"          value="{{ old('waba_id', $adminWabaId) }}">
                    <input type="hidden" name="access_token"     value="{{ old('access_token', $adminToken) }}">
                    <input type="hidden" name="app_id"           value="{{ old('app_id', $adminAppId) }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Account Label') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="{{ translate('e.g. My Business WhatsApp') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('WhatsApp Business Phone Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number') }}" placeholder="+2348012345678">
                            <small class="text-muted fs-12">{{ translate('Your registered WhatsApp Business number with country code') }}</small>
                            @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Phone Number ID') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number_id" class="form-control @error('phone_number_id') is-invalid @enderror"
                                value="{{ old('phone_number_id') }}" placeholder="e.g. 123456789012345">
                            <small class="text-muted fs-12">
                                {{ translate('Found in Meta → WhatsApp → Getting Started, next to your phone number. This is YOUR number\'s unique ID, not the App ID.') }}
                                <a href="{{ route('user.whatsapp.documentation') }}" target="_blank" class="text-primary">{{ translate('Where to find it →') }}</a>
                            </small>
                            @error('phone_number_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Welcome Message') }}</label>
                            <textarea name="welcome_message" class="form-control" rows="2"
                                placeholder="{{ $adminWelcome ?: translate('Hi! Thanks for messaging us. How can we help?') }}">{{ old('welcome_message', $adminWelcome) }}</textarea>
                            <small class="text-muted fs-12">{{ translate('Sent automatically to first-time contacts') }}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Fallback Message') }}</label>
                            <textarea name="fallback_message" class="form-control" rows="2"
                                placeholder="{{ $adminFallback ?: translate("Sorry, I didn't understand that. A human will assist you shortly.") }}">{{ old('fallback_message', $adminFallback) }}</textarea>
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

                        {{-- Advanced: allow user to override credentials --}}
                        <div class="col-12">
                            <button type="button" class="btn btn-link p-0 fs-13 text-muted" data-bs-toggle="collapse" data-bs-target="#advancedFields">
                                <i class="bi bi-chevron-down me-1"></i>{{ translate('Advanced — Use my own API credentials') }}
                            </button>
                        </div>
                        <div class="col-12 collapse" id="advancedFields">
                            <div class="row g-3 p-3 rounded-3" style="background:#fafafa;border:1px solid #e5e7eb;">
                                <p class="fs-13 text-muted mb-0">{{ translate('Override the platform defaults with your own Meta App credentials.') }}</p>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-13">{{ translate('WABA ID') }}</label>
                                    <input type="text" name="waba_id" class="form-control form-control-sm" value="{{ old('waba_id', $adminWabaId) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold fs-13">{{ translate('Access Token') }}</label>
                                    <input type="text" name="access_token" class="form-control form-control-sm font-monospace" value="{{ old('access_token', $adminToken) }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-whatsapp me-1"></i>{{ translate('Connect WhatsApp') }}
                            </button>
                            <a href="{{ route('user.whatsapp.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>
                    </div>
                </form>

                @else
                {{-- Full manual setup mode: admin hasn't configured API yet --}}
                <div class="alert mb-4" style="background:#fefce8;border-left:4px solid #fbbf24;padding:14px 16px;border-radius:8px;">
                    <p class="fw-semibold mb-1 fs-14"><i class="bi bi-exclamation-triangle me-1" style="color:#d97706;"></i>{{ translate('Manual Setup Required') }}</p>
                    <p class="fs-13 text-muted mb-0">{{ translate('Platform-level WhatsApp API is not configured yet. You need to provide your own Meta App credentials.') }}
                        <a href="{{ route('user.whatsapp.documentation') }}" class="fw-semibold">{{ translate('View the Setup Guide') }}</a> {{ translate('for step-by-step instructions.') }}
                    </p>
                </div>

                <form action="{{ route('user.whatsapp.store') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Account Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="f_name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="My Business WhatsApp">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number') }}</label>
                            <input type="text" name="phone_number" id="f_phone" class="form-control" value="{{ old('phone_number') }}" placeholder="+2348012345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number ID') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number_id" id="f_phone_id" class="form-control" value="{{ old('phone_number_id') }}" placeholder="From Meta Dashboard">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('WABA ID') }}</label>
                            <input type="text" name="waba_id" id="f_waba" class="form-control" value="{{ old('waba_id') }}" placeholder="WhatsApp Business Account ID">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('App ID') }}</label>
                            <input type="text" name="app_id" class="form-control" value="{{ old('app_id') }}" placeholder="Meta App ID">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Verify Token') }}</label>
                            <input type="text" name="verify_token" id="f_verify" class="form-control" value="{{ old('verify_token', \Illuminate\Support\Str::random(32)) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Access Token') }} <span class="text-danger">*</span></label>
                            <input type="text" name="access_token" id="f_token" class="form-control font-monospace" value="{{ old('access_token') }}" placeholder="EAAxxxxxxxxxx...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Welcome Message') }}</label>
                            <textarea name="welcome_message" id="f_welcome" class="form-control" rows="2" placeholder="Hi! How can I help you?">{{ old('welcome_message') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Fallback Message') }}</label>
                            <textarea name="fallback_message" id="f_fallback" class="form-control" rows="2" placeholder="Thanks for your message!">{{ old('fallback_message') }}</textarea>
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
                                <i class="bi bi-save me-1"></i>{{ translate('Connect Account') }}
                            </button>
                            <a href="{{ route('user.whatsapp.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>
                    </div>
                </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
document.getElementById('ai_enabled')?.addEventListener('change', function() {
    document.getElementById('chatbotRow').style.display = this.checked ? '' : 'none';
});
</script>
@endpush
