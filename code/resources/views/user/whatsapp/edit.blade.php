@extends('layouts.master')
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>{{ translate('Edit WhatsApp Account') }}</h4>
                <a href="{{ route('user.whatsapp.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
            </div>
            <div class="card-body">

                {{-- Webhook URL display --}}
                <div class="alert mb-4" style="background:#f0fdf4;border-left:4px solid #25d366;padding:12px 16px;border-radius:8px;">
                    <p class="fw-semibold mb-1 fs-14"><i class="bi bi-link-45deg me-1"></i>{{ translate('Your Webhook URL') }}</p>
                    <div class="input-group input-group-sm">
                        <input type="text" id="webhookUrl" class="form-control font-monospace"
                            value="{{ url('/webhook/whatsapp/' . $account->verify_token) }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWebhook()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <p class="fs-12 text-muted mt-1 mb-0">{{ translate('Paste this URL in Meta Developer Console → Webhooks.') }}</p>
                </div>

                <form action="{{ route('user.whatsapp.update', $account->uid) }}" method="post">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Account Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $account->name) }}" placeholder="My Business WhatsApp">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number') }}</label>
                            <input type="text" name="phone_number" class="form-control"
                                value="{{ old('phone_number', $account->phone_number) }}" placeholder="+2348012345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number ID') }}</label>
                            <input type="text" name="phone_number_id" class="form-control"
                                value="{{ old('phone_number_id', $account->phone_number_id) }}" placeholder="From Meta Developer Console">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('WhatsApp Business Account ID (WABA ID)') }}</label>
                            <input type="text" name="waba_id" class="form-control"
                                value="{{ old('waba_id', $account->waba_id) }}" placeholder="WABA ID from Meta">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Access Token') }}</label>
                            <input type="text" name="access_token" class="form-control font-monospace"
                                value="{{ old('access_token', $account->access_token) }}" placeholder="EAAxxxxxxx…">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Verify Token') }}</label>
                            <input type="text" name="verify_token" class="form-control font-monospace"
                                value="{{ old('verify_token', $account->verify_token) }}">
                            <small class="text-muted fs-12">{{ translate('Used by Meta to verify webhook ownership.') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('App ID') }}</label>
                            <input type="text" name="app_id" class="form-control"
                                value="{{ old('app_id', $account->app_id) }}" placeholder="Meta App ID">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Welcome Message') }}</label>
                            <textarea name="welcome_message" class="form-control" rows="2"
                                placeholder="{{ translate('Sent automatically to first-time contacts.') }}">{{ old('welcome_message', $account->welcome_message) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Fallback Message') }}</label>
                            <textarea name="fallback_message" class="form-control" rows="2"
                                placeholder="{{ translate('Sent when the chatbot cannot respond.') }}">{{ old('fallback_message', $account->fallback_message) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Enable AI Chatbot') }}</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="ai_enabled" id="aiEnabled"
                                    value="1" {{ old('ai_enabled', $account->ai_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="aiEnabled">{{ translate('Auto-reply with AI') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="chatbotSelectRow" style="{{ $account->ai_enabled ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold">{{ translate('Select Chatbot') }}</label>
                            <select name="chatbot_id" class="form-select">
                                <option value="">{{ translate('None') }}</option>
                                @foreach($chatbots as $bot)
                                <option value="{{ $bot->id }}" {{ old('chatbot_id', $account->chatbot_id) == $bot->id ? 'selected' : '' }}>{{ $bot->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{ translate('Save Changes') }}
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
document.getElementById('aiEnabled')?.addEventListener('change', function() {
    document.getElementById('chatbotSelectRow').style.display = this.checked ? '' : 'none';
});
function copyWebhook() {
    const el = document.getElementById('webhookUrl');
    navigator.clipboard.writeText(el.value).then(() => {
        const btn = el.nextElementSibling;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
    });
}
</script>
@endpush
