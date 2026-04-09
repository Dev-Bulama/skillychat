@extends('admin.layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>{{translate('WhatsApp API Global Settings')}}</h4>
                <p class="text-muted mb-0 fs-13">{{translate('These credentials are used as defaults when users connect their WhatsApp account. Users only need to enter their phone number.')}}</p>
            </div>
            <div class="card-body">

                <div class="alert mb-4" style="background:#f0fdf4;border-left:4px solid #25d366;padding:14px 16px;border-radius:8px;">
                    <p class="fw-semibold mb-1 fs-14"><i class="bi bi-info-circle me-1"></i>{{translate('How this works')}}</p>
                    <ul class="fs-13 text-muted mb-0 ps-3">
                        <li>{{translate('You set up ONE Meta App and WhatsApp Business Account (WABA) for the platform.')}}</li>
                        <li>{{translate('Users connect their WhatsApp Business phone number — they do NOT need their own Meta App.')}}</li>
                        <li>{{translate('The platform uses your credentials to manage webhooks and send/receive messages for all users.')}}</li>
                        <li>{{translate('Users can still override with their own credentials if they prefer.')}}</li>
                    </ul>
                </div>

                <form action="{{route('admin.setting.whatsapp.save')}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('Meta App ID')}}</label>
                            <input type="text" name="whatsapp_default_app_id" class="form-control"
                                value="{{site_settings('whatsapp_default_app_id')}}"
                                placeholder="e.g. 123456789012345">
                            <small class="text-muted fs-12">{{translate('From Meta Developer Console → Your App → App ID')}}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('WhatsApp Business Account ID (WABA ID)')}}</label>
                            <input type="text" name="whatsapp_default_waba_id" class="form-control"
                                value="{{site_settings('whatsapp_default_waba_id')}}"
                                placeholder="e.g. 987654321098765">
                            <small class="text-muted fs-12">{{translate('From WhatsApp → Getting Started → WABA ID')}}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('Phone Number ID')}}</label>
                            <input type="text" name="whatsapp_default_phone_number_id" class="form-control"
                                value="{{site_settings('whatsapp_default_phone_number_id')}}"
                                placeholder="e.g. 111222333444555">
                            <small class="text-muted fs-12">{{translate('The default phone number ID (can be overridden per user)')}}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('Access Token')}}</label>
                            <input type="text" name="whatsapp_default_access_token" class="form-control font-monospace"
                                value="{{site_settings('whatsapp_default_access_token')}}"
                                placeholder="EAAxxxxxxxxxxxxxxxx…">
                            <small class="text-muted fs-12">{{translate('System User permanent token from Meta Business Manager')}}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{translate('Default Welcome Message')}}</label>
                            <textarea name="whatsapp_default_welcome_message" class="form-control" rows="2"
                                placeholder="{{translate('Hi! Thanks for messaging us. How can we help you today?')}}">{{site_settings('whatsapp_default_welcome_message')}}</textarea>
                            <small class="text-muted fs-12">{{translate('Sent automatically to first-time contacts (can be overridden per user account)')}}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{translate('Default Fallback Message')}}</label>
                            <textarea name="whatsapp_default_fallback_message" class="form-control" rows="2"
                                placeholder="{{translate('Sorry, I did not understand that. A human agent will be with you shortly.')}}">{{site_settings('whatsapp_default_fallback_message')}}</textarea>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{translate('Save WhatsApp Settings')}}
                            </button>
                        </div>
                    </div>
                </form>

                <hr class="my-4">

                <div class="p-3 rounded-3" style="background:#f8f9fa;border:1px solid #e5e7eb;">
                    <h6 class="fw-semibold mb-2">{{translate('Webhook URL for Meta')}}</h6>
                    <p class="fs-13 text-muted mb-1">{{translate('Each user account has a unique webhook URL. Users will see their specific URL after connecting.')}} {{translate('The format is:')}}</p>
                    <code class="fs-12 d-block p-2 bg-white rounded border">{{ url('/webhook/whatsapp/{user_verify_token}') }}</code>
                    <p class="fs-12 text-muted mt-2 mb-0">{{translate('Point this in Meta → WhatsApp → Configuration → Webhook → Callback URL.')}}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
