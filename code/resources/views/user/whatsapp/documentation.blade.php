@extends('layouts.master')
@section('content')
<div class="row g-4">

    {{-- Page header --}}
    <div class="col-12">
        <div class="i-card" style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border:1.5px solid #86efac;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold mb-1" style="color:#15803d;"><i class="bi bi-whatsapp me-2"></i>{{ translate('WhatsApp + CRM + Drip Setup Guide') }}</h3>
                    <p class="text-muted fs-14 mb-0">{{ translate('Follow this step-by-step guide to connect your WhatsApp Business account, set up your CRM pipeline, and launch automated drip sequences.') }}</p>
                </div>
                <a href="{{ route('user.whatsapp.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Accounts') }}</a>
            </div>
        </div>
    </div>

    {{-- Quick nav --}}
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            @foreach([
                ['#part1','Part 1: Meta Setup','bi-gear'],
                ['#part2','Part 2: Connect Account','bi-whatsapp'],
                ['#part3','Part 3: CRM Pipeline','bi-kanban'],
                ['#part4','Part 4: Drip Sequences','bi-funnel'],
                ['#part5','Part 5: Testing','bi-bug'],
                ['#faq','FAQ','bi-question-circle'],
            ] as [$href,$label,$icon])
            <a href="{{ $href }}" class="i-btn btn--sm btn--outline capsuled"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- PART 1: Meta Developer Setup --}}
    <div class="col-12" id="part1">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><span class="badge bg-success me-2">Part 1</span>{{ translate('Meta Developer Console Setup') }}</h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @php
                    $metaSteps = [
                        ['01', 'Create a Meta App', 'Go to <strong>developers.facebook.com</strong> → My Apps → Create App. Choose <strong>Business</strong> type. Name it anything (e.g. "SkillyChatWA").'],
                        ['02', 'Add WhatsApp Product', 'Inside your new app, click <strong>Add a Product</strong> → <strong>WhatsApp</strong> → Set Up.'],
                        ['03', 'Get Phone Number ID', 'In WhatsApp → Getting Started, you will see a test phone number with a <strong>Phone Number ID</strong>. Copy this — you will need it later.'],
                        ['04', 'Get WABA ID', 'Just below the Phone Number ID you will find the <strong>WhatsApp Business Account ID (WABA ID)</strong>. Copy it.'],
                        ['05', 'Generate Access Token', 'Scroll to "Temporary access token" and click <strong>Generate Token</strong>. Copy the token. (For production, create a System User with a permanent token in Business Manager → System Users.)'],
                        ['06', 'Add a Real Phone Number', 'To send to real numbers go to WhatsApp → Phone Numbers → Add Phone Number. Verify via SMS or voice call. Use this number in production.'],
                    ];
                    @endphp
                    @foreach($metaSteps as [$n, $title, $desc])
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <span class="fw-black fs-28 lh-1 d-block mb-1" style="color:#15803d;opacity:.2;">{{ $n }}</span>
                            <p class="fw-semibold mb-1 fs-14">{{ $title }}</p>
                            <p class="fs-13 text-muted mb-0">{!! $desc !!}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- PART 2: Connect Account --}}
    <div class="col-12" id="part2">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><span class="badge bg-success me-2">Part 2</span>{{ translate('Connect Your WhatsApp Account') }}</h4>
            </div>
            <div class="card-body">
                <ol class="fs-14" style="line-height:2;">
                    <li>In the sidebar click <strong>WhatsApp → Add Account</strong>.</li>
                    <li>Fill in: <strong>Account Name</strong> (any label), <strong>Phone Number</strong>, <strong>Phone Number ID</strong>, <strong>WABA ID</strong>, <strong>Access Token</strong>.</li>
                    <li>Leave <strong>Verify Token</strong> blank — the system auto-generates one. You can change it if you prefer a custom string.</li>
                    <li>Click <strong>Save Account</strong>. The page will show your unique <strong>Webhook URL</strong>.</li>
                    <li>Copy the Webhook URL, go back to Meta Developer Console → WhatsApp → Configuration → Webhook.</li>
                    <li>Click <strong>Edit</strong> and paste the Webhook URL into "Callback URL".</li>
                    <li>Paste your <strong>Verify Token</strong> (same string shown on this platform) into "Verify token".</li>
                    <li>Click <strong>Verify and Save</strong>. Meta will call your webhook to verify — it should pass instantly.</li>
                    <li>Under <strong>Webhook fields</strong>, subscribe to: <code>messages</code>.</li>
                    <li>Back in SkillyChatWA, click <strong>Test Connection</strong> button on the account card to confirm everything is working.</li>
                </ol>

                <div class="alert mt-3" style="background:#fefce8;border-left:4px solid #fbbf24;padding:12px 16px;border-radius:8px;">
                    <strong>Tip:</strong> For production, add your real phone number and make sure it is approved for WhatsApp Business API by Meta. Test numbers only allow messages to verified recipients.
                </div>
            </div>
        </div>
    </div>

    {{-- PART 3: CRM Pipeline --}}
    <div class="col-12" id="part3">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><span class="badge" style="background:#6d28d9;" class="me-2">Part 3</span>&nbsp;{{ translate('Set Up Your CRM Pipeline') }}</h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @php
                    $crmSteps = [
                        ['01','Create a Pipeline','Go to <strong>CRM → Pipeline Setup</strong>. Click "Create Pipeline". A default "Sales Pipeline" is created with 6 stages: New Lead, Contacted, Qualified, Proposal Sent, Won, Lost.'],
                        ['02','Customize Stages','On the Pipeline Setup page, add custom stages using the color picker. Each stage can be color-coded for easy visual identification on the Kanban board.'],
                        ['03','Add Leads Manually','Go to <strong>CRM → Add Lead</strong>. Fill in the lead details. Use the "Load Demo Lead" button to auto-populate a sample lead and explore the form.'],
                        ['04','View the Kanban Board','Go to <strong>CRM → Board</strong>. Your leads appear as cards. <strong>Drag and drop</strong> cards between columns to move them to a new stage — every move is logged automatically.'],
                        ['05','Auto-create from WhatsApp','When a new WhatsApp message arrives from an unknown number, the system automatically creates a CRM lead with the contact\'s number and name.'],
                        ['06','Enroll Leads in Drip','From a lead\'s detail page, you can see which drip sequences they are enrolled in. From a sequence\'s detail page, you can manually enroll leads.'],
                    ];
                    @endphp
                    @foreach($crmSteps as [$n, $title, $desc])
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background:#faf5ff;border:1px solid #e9d5ff;">
                            <span class="fw-black fs-28 lh-1 d-block mb-1" style="color:#6d28d9;opacity:.2;">{{ $n }}</span>
                            <p class="fw-semibold mb-1 fs-14">{{ $title }}</p>
                            <p class="fs-13 text-muted mb-0">{!! $desc !!}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- PART 4: Drip Sequences --}}
    <div class="col-12" id="part4">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><span class="badge bg-danger me-2">Part 4</span>{{ translate('Create Drip Sequences') }}</h4>
            </div>
            <div class="card-body">
                <div class="row g-4 mb-4">
                    @php
                    $dripSteps = [
                        ['01','Create a Sequence','Go to <strong>Drip → New Sequence</strong>. Give it a name (e.g. "Welcome Sequence") and choose a trigger. Use "Load Demo" to see a pre-built 3-step example.'],
                        ['02','Choose a Trigger','<strong>Manual</strong>: you enroll contacts yourself. <strong>New Lead</strong>: auto-enrolls every new CRM lead. <strong>Tag Added</strong>: fires when a specific tag is added to a lead.'],
                        ['03','Add Steps','After creating the sequence, go to the detail page and add steps. Each step has a <strong>channel</strong> (Email / SMS / WhatsApp), a <strong>delay</strong> (days + hours after the previous step), and a <strong>message</strong>.'],
                        ['04','Use Placeholders','In your messages use <code>{{contact_name}}</code>, <code>{{contact_email}}</code>, <code>{{contact_phone}}</code>. The system replaces these automatically when sending.'],
                        ['05','Activate the Sequence','Click <strong>Activate</strong>. The sequence must have at least one step. Once active, the system processes it every 5 minutes.'],
                        ['06','Enroll Contacts','For Manual sequences, use the "Enroll a Contact" form on the sequence detail page. Enter name, email, and/or phone. The system sends each step at the scheduled delay.'],
                    ];
                    @endphp
                    @foreach($dripSteps as [$n, $title, $desc])
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background:#fff5f5;border:1px solid #fecaca;">
                            <span class="fw-black fs-28 lh-1 d-block mb-1" style="color:#dc2626;opacity:.2;">{{ $n }}</span>
                            <p class="fw-semibold mb-1 fs-14">{{ $title }}</p>
                            <p class="fs-13 text-muted mb-0">{!! $desc !!}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Sample sequence table --}}
                <h6 class="fw-semibold mb-3">{{ translate('Example: Welcome Sequence (3 steps)') }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered fs-13">
                        <thead class="table-light">
                            <tr>
                                <th>Step</th>
                                <th>Channel</th>
                                <th>Delay</th>
                                <th>Message Summary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><span class="badge" style="background:#ecfeff;color:#0891b2;">Email</span></td>
                                <td>Immediately (0d 0h)</td>
                                <td>Welcome email — introduce your brand, set expectations</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><span class="badge" style="background:#ecfeff;color:#0891b2;">Email</span></td>
                                <td>2 days later</td>
                                <td>Quick-start tip — help them get value fast</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td><span class="badge" style="background:#dcfce7;color:#15803d;">WhatsApp</span></td>
                                <td>5 days later</td>
                                <td>Personal check-in message via WhatsApp</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PART 5: Testing --}}
    <div class="col-12" id="part5">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><span class="badge bg-warning text-dark me-2">Part 5</span>{{ translate('Testing Your Setup') }}</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fefce8;border:1px solid #fde047;">
                            <p class="fw-semibold mb-2 fs-14">Test WhatsApp Connection</p>
                            <ol class="fs-13 text-muted mb-0">
                                <li>Go to WhatsApp → your account card</li>
                                <li>Click <strong>Test Connection</strong></li>
                                <li>Green = connected. Red = check your token/phone ID</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fefce8;border:1px solid #fde047;">
                            <p class="fw-semibold mb-2 fs-14">Test Drip Sequence</p>
                            <ol class="fs-13 text-muted mb-0">
                                <li>Create a sequence with Step 1 delay = 0 days 0 hours</li>
                                <li>Activate the sequence</li>
                                <li>Enroll yourself (your own email/phone)</li>
                                <li>Wait up to 5 minutes — the processor runs every 5 min</li>
                                <li>Check your inbox / WhatsApp</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fefce8;border:1px solid #fde047;">
                            <p class="fw-semibold mb-2 fs-14">Test Webhook (Inbound)</p>
                            <ol class="fs-13 text-muted mb-0">
                                <li>Send a WhatsApp message to your registered business number</li>
                                <li>Check <strong>CRM → All Leads</strong> — a new lead should appear automatically</li>
                                <li>Check <strong>WhatsApp → Messages</strong> — the conversation should be there</li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fefce8;border:1px solid #fde047;">
                            <p class="fw-semibold mb-2 fs-14">Common Issues</p>
                            <ul class="fs-13 text-muted mb-0">
                                <li><strong>Webhook not verifying:</strong> Double-check Verify Token matches exactly</li>
                                <li><strong>Messages not arriving:</strong> Ensure webhook is subscribed to "messages" field</li>
                                <li><strong>Drip not sending:</strong> Confirm sequence status = Active and cron is running</li>
                                <li><strong>Token expired:</strong> Temporary tokens expire. Generate a System User token in Business Manager for production</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="col-12" id="faq">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-question-circle me-2" style="color:#6d28d9;"></i>{{ translate('Frequently Asked Questions') }}</h4>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    @php
                    $faqs = [
                        ['Do I need a paid Meta account?', 'No. The WhatsApp Business API is free. However, Meta charges per conversation for business-initiated messages beyond the free monthly tier. Check Meta\'s current pricing at developers.facebook.com/docs/whatsapp/pricing.'],
                        ['Can I use a personal WhatsApp number?', 'No. You need a WhatsApp Business API number. This must be a phone number that is NOT already linked to a regular WhatsApp or WhatsApp Business app. You can use a virtual/SIM number.'],
                        ['How often does the drip processor run?', 'Every 5 minutes via your server\'s cron job. Make sure Laravel\'s scheduler is running: add "* * * * * php /path/to/artisan schedule:run" to your server crontab.'],
                        ['What variables can I use in messages?', 'Use {{contact_name}}, {{contact_email}}, {{contact_phone}}. More placeholders may be added in future updates.'],
                        ['Can I use the AI chatbot with WhatsApp?', 'Yes! When creating or editing a WhatsApp account, enable "AI Chatbot" and select one of your AI chatbots. Inbound messages are automatically routed to the chatbot for a response.'],
                        ['Will CRM leads auto-enroll in drip sequences?', 'Yes, if you have a sequence with trigger type "New Lead Created" and status Active. Any newly created lead (manual, WhatsApp, or chatbot) will be enrolled automatically.'],
                    ];
                    @endphp
                    @foreach($faqs as $i => [$q, $a])
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fs-14 fw-semibold" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq{{ $i }}">
                                {{ $q }}
                            </button>
                        </h2>
                        <div id="faq{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body fs-14 text-muted">{{ $a }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
