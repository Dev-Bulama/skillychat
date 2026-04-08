@extends('layouts.master')
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-whatsapp me-2 text-success"></i>{{ isset($account) ? translate('Edit WhatsApp Account') : translate('Connect WhatsApp Account') }}</h4>
                <a href="{{ route('user.whatsapp.documentation') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-book me-1"></i>{{ translate('Setup Guide') }}
                </a>
            </div>
            <div class="card-body">

                {{-- Demo populate button --}}
                <div class="alert mb-4" style="background:#f0f9ff;border-left:4px solid #0891b2;padding:12px 16px;border-radius:8px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="fs-14">
                            <i class="bi bi-magic me-2" style="color:#0891b2;"></i>
                            <strong>{{ translate('Try with Demo Data') }}</strong> — {{ translate('Auto-fill the form with sample credentials to explore the interface.') }}
                        </div>
                        <button type="button" class="i-btn btn--sm capsuled" style="background:#0891b2;color:#fff;border:none;" id="loadDemoBtn">
                            <i class="bi bi-play-circle me-1"></i>{{ translate('Load Demo Data') }}
                        </button>
                    </div>
                </div>

                <form action="{{ isset($account) ? route('user.whatsapp.update', $account->uid) : route('user.whatsapp.store') }}" method="post">
                    @csrf
                    @if(isset($account)) @method('PUT') @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Account Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="f_name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $account->name ?? '') }}"
                                placeholder="e.g. My Business WhatsApp">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Display Phone Number') }}</label>
                            <input type="text" name="phone_number" id="f_phone" class="form-control"
                                value="{{ old('phone_number', $account->phone_number ?? '') }}"
                                placeholder="+2348012345678">
                            <small class="text-muted">{{ translate('Your WhatsApp number (for display only)') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number ID') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number_id" id="f_phone_id" class="form-control @error('phone_number_id') is-invalid @enderror"
                                value="{{ old('phone_number_id', $account->phone_number_id ?? '') }}"
                                placeholder="From Meta Business Manager">
                            @error('phone_number_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('WhatsApp Business Account ID (WABA ID)') }}</label>
                            <input type="text" name="waba_id" id="f_waba" class="form-control"
                                value="{{ old('waba_id', $account->waba_id ?? '') }}"
                                placeholder="Meta WABA ID">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Permanent Access Token') }} <span class="text-danger">*</span></label>
                            <input type="text" name="access_token" id="f_token" class="form-control @error('access_token') is-invalid @enderror"
                                value="{{ old('access_token', $account->access_token ?? '') }}"
                                placeholder="EAAxxxxxxxxxx...">
                            @error('access_token')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">{{ translate('Generate a permanent token in Meta Business → System Users') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Webhook Verify Token') }}</label>
                            <div class="input-group">
                                <input type="text" name="verify_token" id="f_verify" class="form-control"
                                    value="{{ old('verify_token', $account->verify_token ?? Str::random(32)) }}">
                                <button type="button" class="btn btn-outline-secondary" id="regenToken">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <small class="text-muted">{{ translate('Copy this as the Verify Token in Meta webhook settings') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('App Secret') }}</label>
                            <input type="password" name="app_secret" class="form-control"
                                value="{{ old('app_secret', $account->app_secret ?? '') }}"
                                placeholder="Optional — for signature verification">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Welcome Message') }}</label>
                            <textarea name="welcome_message" id="f_welcome" class="form-control" rows="2"
                                placeholder="Hi! Thanks for reaching out. How can I help you today?">{{ old('welcome_message', $account->welcome_message ?? '') }}</textarea>
                            <small class="text-muted">{{ translate('Sent to first-time contacts') }}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Fallback Message') }}</label>
                            <textarea name="fallback_message" id="f_fallback" class="form-control" rows="2"
                                placeholder="Thanks for your message! We'll get back to you shortly.">{{ old('fallback_message', $account->fallback_message ?? '') }}</textarea>
                            <small class="text-muted">{{ translate("Sent when AI can't answer or AI is disabled") }}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ai_enabled" id="ai_enabled" value="1"
                                    {{ old('ai_enabled', $account->ai_enabled ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="ai_enabled">
                                    {{ translate('Enable AI Auto-Reply') }}
                                </label>
                            </div>
                            <small class="text-muted">{{ translate('Route inbound messages through your linked chatbot') }}</small>
                        </div>
                        <div class="col-md-6" id="chatbotRow">
                            <label class="form-label fw-semibold">{{ translate('Linked Chatbot') }}</label>
                            <select name="chatbot_id" class="form-select">
                                <option value="">{{ translate('-- Select chatbot --') }}</option>
                                @foreach($chatbots as $bot)
                                <option value="{{ $bot->id }}" {{ old('chatbot_id', $account->chatbot_id ?? '') == $bot->id ? 'selected' : '' }}>
                                    {{ $bot->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Webhook URL display --}}
                        @if(isset($account))
                        <div class="col-12">
                            <div class="p-3 rounded-3" style="background:#f8f9fa;border:1px dashed #dee2e6;">
                                <p class="fw-semibold mb-1 fs-14"><i class="bi bi-link-45deg me-1"></i>{{ translate('Your Webhook URL') }}</p>
                                <code class="fs-13 user-select-all" id="webhookUrl">{{ url('/whatsapp/webhook/' . $account->uid) }}</code>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrl').textContent);this.innerHTML='✅ Copied'">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <p class="fs-12 text-muted mt-1 mb-0">{{ translate('Paste this URL in Meta Developer → Webhooks → Callback URL') }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{ isset($account) ? translate('Save Changes') : translate('Connect Account') }}
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
<script nonce="{{ csp_nonce() }}">
// Regenerate verify token
document.getElementById('regenToken')?.addEventListener('click', () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let token = '';
    for(let i=0;i<32;i++) token += chars.charAt(Math.floor(Math.random()*chars.length));
    document.getElementById('f_verify').value = token;
});

// Demo data auto-fill
document.getElementById('loadDemoBtn')?.addEventListener('click', () => {
    document.getElementById('f_name').value        = 'Demo Business WhatsApp';
    document.getElementById('f_phone').value       = '+2348012345678';
    document.getElementById('f_phone_id').value    = '123456789012345';
    document.getElementById('f_waba').value        = '987654321098765';
    document.getElementById('f_token').value       = 'EAADemoTokenxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    document.getElementById('f_welcome').value     = 'Hi there! 👋 Welcome to Demo Business. How can I help you today?';
    document.getElementById('f_fallback').value    = "Thanks for your message! We've received it and will reply within a few minutes.";
    document.getElementById('loadDemoBtn').innerHTML = '✅ Demo Data Loaded — Edit fields with your real credentials';
    document.getElementById('loadDemoBtn').style.background = '#16a34a';
});
</script>
@endpush
