@extends('admin.layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">

        {{-- ── Settings Card ────────────────────────────────────────────── --}}
        <div class="i-card-md mb-4">
            <div class="card--header">
                <div>
                    <h4 class="card-title"><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>{{translate('WhatsApp API Global Settings')}}</h4>
                    <p class="text-muted mb-0 fs-13">{{translate('These credentials are used as platform defaults. Users only need to enter their phone number.')}}</p>
                </div>
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
                                value="{{site_settings('whatsapp_default_app_id')}}" placeholder="e.g. 123456789012345">
                            <small class="text-muted fs-12">{{translate('From Meta Developer Console → Your App → App ID')}}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('WhatsApp Business Account ID (WABA ID)')}}</label>
                            <input type="text" name="whatsapp_default_waba_id" class="form-control"
                                value="{{site_settings('whatsapp_default_waba_id')}}" placeholder="e.g. 987654321098765">
                            <small class="text-muted fs-12">{{translate('From WhatsApp → Getting Started → WABA ID')}}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('Phone Number ID')}}</label>
                            <input type="text" name="whatsapp_default_phone_number_id" class="form-control"
                                value="{{site_settings('whatsapp_default_phone_number_id')}}" placeholder="e.g. 111222333444555">
                            <small class="text-muted fs-12">{{translate('The default phone number ID (can be overridden per user)')}}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{translate('Access Token (Permanent System User Token)')}}</label>
                            <input type="text" name="whatsapp_default_access_token" class="form-control font-monospace"
                                value="{{site_settings('whatsapp_default_access_token')}}" placeholder="EAAxxxxxxxxxxxxxxxx…">
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
                    <h6 class="fw-semibold mb-2"><i class="bi bi-link-45deg me-1"></i>{{translate('Webhook URL Format')}}</h6>
                    <p class="fs-13 text-muted mb-1">{{translate('Each user account has a unique webhook URL shown after they connect. The format is:')}}</p>
                    <code class="fs-12 d-block p-2 bg-white rounded border">{{ url('/webhook/whatsapp/{user_verify_token}') }}</code>
                    <p class="fs-12 text-muted mt-2 mb-0">{{translate('Point this in Meta → WhatsApp → Configuration → Webhook → Callback URL. Each user sees their own URL on their account detail page.')}}</p>
                </div>
            </div>
        </div>

        {{-- ── Step-by-Step Setup Guide ─────────────────────────────────── --}}
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-map me-2"></i>{{translate('Step-by-Step Meta Setup Guide')}}</h4>
                <span class="badge bg-success fs-12">{{translate('Click-by-Click')}}</span>
            </div>
            <div class="card-body">

                <p class="text-muted fs-13 mb-4">{{translate('Follow these steps once to connect the platform to Meta\'s WhatsApp Cloud API. After setup, your users connect their WhatsApp Business phone number in one click.')}}</p>

                {{-- Step 1 --}}
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-16"
                        style="width:42px;height:42px;min-width:42px;background:#6366f1;">1</div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-2">{{translate('Create a Meta Developer Account and App')}}</h6>
                        <ol class="fs-13 text-muted ps-3 mb-0">
                            <li class="mb-2">Open <strong>developers.facebook.com</strong> in your browser. Log in with a Facebook account that has admin access to your business.</li>
                            <li class="mb-2">Click <strong>My Apps</strong> in the top-right corner → click the blue <strong>Create App</strong> button.</li>
                            <li class="mb-2">When asked <em>"What do you want your app to do?"</em> — select <strong>Other</strong> → click <strong>Next</strong>.</li>
                            <li class="mb-2">Select <strong>Business</strong> as the App type → click <strong>Next</strong>.</li>
                            <li class="mb-2">Enter an <strong>App Name</strong> (e.g. <em>"MyPlatform Messaging"</em>), enter your Business email → click <strong>Create App</strong>.</li>
                            <li class="mb-2">On the <em>Add Products to Your App</em> page, scroll to find <strong>WhatsApp</strong> → click <strong>Set up</strong>.</li>
                            <li>Copy the <strong>App ID</strong> shown in the top-left of your app dashboard (a 15–16 digit number) → paste it into <strong>Meta App ID</strong> in the form above.</li>
                        </ol>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-16"
                        style="width:42px;height:42px;min-width:42px;background:#6366f1;">2</div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-2">{{translate('Connect a WhatsApp Business Account (WABA) and Phone Number')}}</h6>
                        <ol class="fs-13 text-muted ps-3 mb-0">
                            <li class="mb-2">In the left sidebar of your Meta App, click <strong>WhatsApp → Getting Started</strong>.</li>
                            <li class="mb-2">Under <em>Step 1 — Select a WhatsApp Business Account</em>, click the dropdown and either select an existing WABA or choose <strong>Create a new WhatsApp Business Account</strong>.</li>
                            <li class="mb-2">Copy the <strong>WhatsApp Business Account ID</strong> shown below the dropdown → paste it into <strong>WABA ID</strong> in the form above.</li>
                            <li class="mb-2">Under <em>Step 2</em>, you will see a test phone number provided by Meta. You can use this for testing, or click <strong>Add phone number</strong> to add your own real business number.</li>
                            <li class="mb-2">Below the selected phone number, you will see a <strong>Phone number ID</strong> (a long numeric value). Copy that → paste into <strong>Phone Number ID</strong> above.</li>
                            <li>If adding a real number: enter it in international format, verify via SMS code, then copy the Phone number ID that appears.</li>
                        </ol>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-16"
                        style="width:42px;height:42px;min-width:42px;background:#6366f1;">3</div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-2">{{translate('Generate a Permanent Access Token (System User)')}}</h6>
                        <div class="alert fs-13 mb-3" style="background:#fef3c7;border-left:3px solid #f59e0b;padding:10px 14px;border-radius:6px;">
                            <strong><i class="bi bi-exclamation-triangle me-1 text-warning"></i>Important:</strong>
                            {{translate('Do NOT use the temporary token on the Getting Started page — it expires in 24 hours. You need a permanent System User token.')}}
                        </div>
                        <ol class="fs-13 text-muted ps-3 mb-0">
                            <li class="mb-2">Open <strong>business.facebook.com</strong> → click the <strong>Settings</strong> gear icon in the bottom-left.</li>
                            <li class="mb-2">In the left menu, click <strong>Users → System Users</strong>.</li>
                            <li class="mb-2">Click the blue <strong>Add</strong> button → enter a name (e.g. <em>"Platform Bot"</em>) → set role to <strong>Admin</strong> → click <strong>Create System User</strong>.</li>
                            <li class="mb-2">Click on the new system user in the list → click <strong>Add Assets</strong>.</li>
                            <li class="mb-2">In the asset selector: click <strong>Apps</strong> → find and select your Meta App → enable <strong>Manage App</strong> → click <strong>Save Changes</strong>.</li>
                            <li class="mb-2">Back on the system user page, click <strong>Generate New Token</strong> → select your app from the dropdown.</li>
                            <li class="mb-2">Tick the following permissions: <code>whatsapp_business_messaging</code> and <code>whatsapp_business_management</code>.</li>
                            <li class="mb-2">Click <strong>Generate Token</strong> → copy the full token that appears.</li>
                            <li>Paste it into <strong>Access Token</strong> in the form above. <span class="text-danger fw-semibold">Save a copy — it is only shown once.</span></li>
                        </ol>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-16"
                        style="width:42px;height:42px;min-width:42px;background:#6366f1;">4</div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-2">{{translate('Save Settings on This Page')}}</h6>
                        <ol class="fs-13 text-muted ps-3 mb-0">
                            <li class="mb-2">Fill in all four fields above: <strong>App ID, WABA ID, Phone Number ID, Access Token</strong>.</li>
                            <li class="mb-2">Optionally set a <strong>Default Welcome Message</strong> (sent to every new contact) and <strong>Default Fallback Message</strong> (sent when AI/bot doesn't understand).</li>
                            <li>Click the <strong>Save WhatsApp Settings</strong> button.</li>
                        </ol>
                        <div class="alert mt-3 fs-13" style="background:#f0fdf4;border-left:3px solid #22c55e;padding:10px 14px;border-radius:6px;">
                            <i class="bi bi-check-circle me-1 text-success"></i>
                            {{translate('Once saved, users will see a simplified connect form — they only enter their WhatsApp Business phone number!')}}
                        </div>
                    </div>
                </div>

                {{-- Step 5 --}}
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-16"
                        style="width:42px;height:42px;min-width:42px;background:#6366f1;">5</div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-2">{{translate('Configure the Webhook in Meta')}}</h6>
                        <ol class="fs-13 text-muted ps-3 mb-0">
                            <li class="mb-2">In your Meta App, go to <strong>WhatsApp → Configuration</strong> (left sidebar).</li>
                            <li class="mb-2">Under the <em>Webhook</em> section, click <strong>Edit</strong>.</li>
                            <li class="mb-2">In the <strong>Callback URL</strong> field, paste the webhook URL for one of your test users. The URL format is:<br>
                                <code class="d-block mt-1 p-2 bg-light rounded border fs-12">{{ url('/webhook/whatsapp/') }}{verify_token}</code>
                                <span class="text-muted fs-12 d-block mt-1">Each user's Verify Token is shown on their WhatsApp account detail page after they connect.</span>
                            </li>
                            <li class="mb-2">In the <strong>Verify Token</strong> field, enter that same user's Verify Token.</li>
                            <li class="mb-2">Click <strong>Verify and Save</strong> — Meta will make a GET request to confirm the URL is reachable.</li>
                            <li class="mb-2">After saving, click <strong>Manage</strong> (next to <em>Webhook Fields</em>) → enable the <strong>messages</strong> checkbox → click <strong>Done</strong>.</li>
                            <li>Repeat this for each user's phone number as they connect, or use a single shared webhook if you route by token.</li>
                        </ol>
                    </div>
                </div>

                {{-- Step 6 --}}
                <div class="d-flex gap-3 mb-4">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-16"
                        style="width:42px;height:42px;min-width:42px;background:#25d366;">6</div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-2">{{translate('Users Connect Their Phone Number')}}</h6>
                        <ol class="fs-13 text-muted ps-3 mb-0">
                            <li class="mb-2">Tell your users to go to <strong>WhatsApp → Connect Account</strong> in their dashboard.</li>
                            <li class="mb-2">They enter an <strong>Account Label</strong> and their <strong>WhatsApp Business Phone Number</strong> in international format (e.g. +2348012345678).</li>
                            <li class="mb-2">They click <strong>Connect WhatsApp</strong>.</li>
                            <li class="mb-2">Their account detail page shows their unique <strong>Webhook URL</strong> and <strong>Verify Token</strong> → they paste these into Meta's Webhook configuration for their phone number.</li>
                            <li>Messages sent to their number will now be received by the platform and handled by their AI chatbot or live agent.</li>
                        </ol>
                    </div>
                </div>

                {{-- FAQ Accordion --}}
                <hr class="my-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-question-circle me-1"></i>{{translate('Frequently Asked Questions')}}</h6>
                <div class="accordion" id="waFaq">
                    @php
                    $faqs = [
                        [
                            'q' => 'Can multiple users share the same Meta App?',
                            'a' => 'Yes. The platform uses one Meta App (your credentials above) and routes incoming messages to the correct user based on the unique verify token in each user\'s webhook URL. Each user gets their own token automatically.'
                        ],
                        [
                            'q' => 'What phone numbers can users connect?',
                            'a' => 'Users must connect a WhatsApp Business phone number that is registered in the Meta WABA linked to your platform App. They (or you on their behalf) add the number in Meta Business Manager → WhatsApp → Phone Numbers → Add Phone Number, verify it via SMS, then connect it here.'
                        ],
                        [
                            'q' => 'Where does a user find their Webhook URL and Verify Token?',
                            'a' => 'After a user clicks Connect WhatsApp and submits the form, they are redirected to their account detail page which clearly shows their Webhook URL and Verify Token. They copy these into Meta\'s WhatsApp → Configuration → Webhook section.'
                        ],
                        [
                            'q' => 'What if a user wants to use their own Meta App credentials?',
                            'a' => 'On the user Connect page, there is an Advanced — Use my own API credentials collapsible section. Expanding it lets them override the App ID, WABA ID, Phone Number ID, and Access Token with their own values.'
                        ],
                        [
                            'q' => 'My Meta App is in Development mode. Will it work?',
                            'a' => 'In Development mode, only phone numbers explicitly added as test numbers in Meta can send and receive messages. To go live with real users, submit your App for Business Verification and apply for the whatsapp_business_messaging permission via Meta App Review.'
                        ],
                        [
                            'q' => 'The temporary token on the Getting Started page expired. What do I do?',
                            'a' => 'That is expected — it lasts 24 hours. Use the System User token from Step 3 above. That token does not expire unless you revoke it manually.'
                        ],
                    ];
                    @endphp
                    @foreach($faqs as $i => $faq)
                    <div class="accordion-item border mb-2 rounded">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fs-13 fw-semibold py-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#wafaq{{ $i }}">
                                {{ $faq['q'] }}
                            </button>
                        </h2>
                        <div id="wafaq{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#waFaq">
                            <div class="accordion-body fs-13 text-muted">{{ $faq['a'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
