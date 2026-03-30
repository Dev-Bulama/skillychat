@extends('layouts.master')

@push('style-include')
<style nonce="{{ csp_nonce() }}">
.guide-wrap        { max-width: 820px; margin: 0 auto; }
.guide-phase       { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.07); margin-bottom:20px; overflow:hidden; }
.guide-phase-header{ background:#6366f1; color:#fff; padding:16px 24px; display:flex; align-items:center; gap:14px; cursor:pointer; user-select:none; }
.guide-phase-num   { width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem; flex-shrink:0; }
.guide-phase-title { font-size:1rem; font-weight:700; flex:1; }
.guide-phase-icon  { font-size:1.1rem; transition:.25s; }
.guide-phase-body  { padding:24px; line-height:1.75; font-size:.93rem; color:#334155; display:none; }
.guide-phase-body.open { display:block; }
.guide-phase-body ol, .guide-phase-body ul { padding-left:20px; margin:0 0 14px; }
.guide-phase-body li { margin-bottom:8px; }
.guide-phase-body h4 { font-size:.97rem; font-weight:700; margin:18px 0 8px; color:#1e293b; }
.guide-phase-body code{ background:#f1f5f9; border-radius:4px; padding:2px 6px; font-size:.85rem; color:#6366f1; }
.guide-phase-body pre { background:#1e293b; color:#e2e8f0; border-radius:8px; padding:16px; font-size:.82rem; overflow-x:auto; margin:12px 0; }
.guide-phase-body .tip{ background:#eff6ff; border-left:3px solid #6366f1; border-radius:4px; padding:10px 14px; margin:14px 0; font-size:.88rem; }
.guide-phase-body .tip strong { color:#3730a3; }
.guide-progress    { display:flex; gap:6px; margin-bottom:24px; }
.guide-dot         { width:12px; height:12px; border-radius:50%; background:#e2e8f0; transition:.2s; }
.guide-dot.done    { background:#10b981; }
.guide-dot.active  { background:#6366f1; }
.guide-hero        { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border-radius:14px; padding:32px; margin-bottom:24px; text-align:center; }
.guide-hero h1     { font-size:1.6rem; font-weight:800; margin-bottom:8px; }
.guide-hero p      { opacity:.85; margin-bottom:0; }
</style>
@endpush

@section('content')
<div class="guide-wrap">

    <div class="guide-hero">
        <h1>{{ translate('n8n + Chatbot Integration Guide') }}</h1>
        <p>{{ translate('Follow each phase step by step to connect your chatbot to powerful automation workflows via n8n.') }}</p>
    </div>

    <div class="guide-progress" id="progressDots">
        @for($i=0;$i<5;$i++)<div class="guide-dot @if($i===0) active @endif" id="dot-{{$i}}"></div>@endfor
    </div>

    {{-- Phase 1 --}}
    <div class="guide-phase">
        <div class="guide-phase-header" onclick="togglePhase(0, this)">
            <div class="guide-phase-num">1</div>
            <div class="guide-phase-title">{{ translate('Set Up Your n8n Instance') }}</div>
            <i class="bi bi-chevron-down guide-phase-icon"></i>
        </div>
        <div class="guide-phase-body open" data-phase="0">
            <h4>{{ translate('Option A — n8n Cloud (Recommended for beginners)') }}</h4>
            <ol>
                <li>{{ translate('Visit') }} <strong>n8n.io</strong> {{ translate('and sign up for a free account.') }}</li>
                <li>{{ translate('After logging in, you\'ll be taken to the n8n workflow editor.') }}</li>
                <li>{{ translate('No server setup needed — n8n handles everything.') }}</li>
            </ol>
            <h4>{{ translate('Option B — Self-hosted n8n') }}</h4>
            <ol>
                <li>{{ translate('Install n8n on your server using npm:') }}</li>
            </ol>
            <pre>npm install -g n8n
n8n start</pre>
            <p>{{ translate('Or use Docker:') }}</p>
            <pre>docker run -it --rm --name n8n -p 5678:5678 n8nio/n8n</pre>
            <div class="tip"><strong>{{ translate('Tip:') }}</strong> {{ translate('For production, make sure your n8n instance is accessible over HTTPS so the chatbot can reach its webhooks.') }}</div>
        </div>
    </div>

    {{-- Phase 2 --}}
    <div class="guide-phase">
        <div class="guide-phase-header" onclick="togglePhase(1, this)">
            <div class="guide-phase-num">2</div>
            <div class="guide-phase-title">{{ translate('Create a Webhook Workflow in n8n') }}</div>
            <i class="bi bi-chevron-down guide-phase-icon"></i>
        </div>
        <div class="guide-phase-body" data-phase="1">
            <ol>
                <li>{{ translate('In n8n, click') }} <strong>+ New Workflow</strong>.</li>
                <li>{{ translate('Click the') }} <strong>+</strong> {{ translate('button to add a node and search for') }} <strong>Webhook</strong>.</li>
                <li>{{ translate('In the Webhook node settings:') }}
                    <ul>
                        <li>{{ translate('Set') }} <strong>HTTP Method</strong> {{ translate('to') }} <code>POST</code>.</li>
                        <li>{{ translate('Set') }} <strong>Response Mode</strong> {{ translate('to') }} <strong>Last Node</strong>.</li>
                        <li>{{ translate('Copy the generated') }} <strong>Webhook URL</strong> — {{ translate('you\'ll need it in Phase 4.') }}</li>
                    </ul>
                </li>
                <li>{{ translate('Click') }} <strong>Listen For Test Event</strong> {{ translate('to activate the test listener.') }}</li>
            </ol>
            <div class="tip"><strong>{{ translate('Important:') }}</strong> {{ translate('The chatbot sends a POST request to this URL containing the visitor\'s message. n8n processes it and sends back the reply.') }}</div>
        </div>
    </div>

    {{-- Phase 3 --}}
    <div class="guide-phase">
        <div class="guide-phase-header" onclick="togglePhase(2, this)">
            <div class="guide-phase-num">3</div>
            <div class="guide-phase-title">{{ translate('Build Your Automation Logic') }}</div>
            <i class="bi bi-chevron-down guide-phase-icon"></i>
        </div>
        <div class="guide-phase-body" data-phase="2">
            <p>{{ translate('After the Webhook node, add your processing logic. Here are common patterns:') }}</p>
            <h4>{{ translate('Pattern A — AI Agent Response') }}</h4>
            <ol>
                <li>{{ translate('Add an') }} <strong>OpenAI</strong> {{ translate('node.') }}</li>
                <li>{{ translate('Set Operation to') }} <strong>Message a Model</strong>.</li>
                <li>{{ translate('In the prompt field, use the expression:') }} <code>&#123;&#123; $json.message &#125;&#125;</code></li>
                <li>{{ translate('Connect it to a') }} <strong>Respond to Webhook</strong> {{ translate('node.') }}</li>
            </ol>
            <h4>{{ translate('Pattern B — CRM / Database Lookup') }}</h4>
            <ol>
                <li>{{ translate('Add a') }} <strong>MySQL / Postgres / Airtable</strong> {{ translate('node.') }}</li>
                <li>{{ translate('Query your data based on') }} <code>&#123;&#123; $json.visitor_id &#125;&#125;</code>.</li>
                <li>{{ translate('Format the result and pass to') }} <strong>Respond to Webhook</strong>.</li>
            </ol>
            <h4>{{ translate('Respond to Webhook — Required Final Node') }}</h4>
            <ol>
                <li>{{ translate('Add a') }} <strong>Respond to Webhook</strong> {{ translate('node at the end.') }}</li>
                <li>{{ translate('Set Response Body to') }} <code>JSON</code>.</li>
                <li>{{ translate('Enter the JSON body:') }}</li>
            </ol>
            <pre>{
  "message": "&#123;&#123; $json.text &#125;&#125;"
}</pre>
            <div class="tip"><strong>{{ translate('Note:') }}</strong> {{ translate('The chatbot reads the') }} <code>message</code> {{ translate('field from the JSON response and displays it to the visitor.') }}</div>
        </div>
    </div>

    {{-- Phase 4 --}}
    <div class="guide-phase">
        <div class="guide-phase-header" onclick="togglePhase(3, this)">
            <div class="guide-phase-num">4</div>
            <div class="guide-phase-title">{{ translate('Connect n8n to Your Chatbot') }}</div>
            <i class="bi bi-chevron-down guide-phase-icon"></i>
        </div>
        <div class="guide-phase-body" data-phase="3">
            <ol>
                <li>{{ translate('In this platform, go to') }} <strong>{{ translate('AI Chatbots') }} → {{ translate('Edit Chatbot') }}</strong>.</li>
                <li>{{ translate('Scroll down to the') }} <strong>{{ translate('n8n Automation') }}</strong> {{ translate('section.') }}</li>
                <li>{{ translate('Toggle on') }} <strong>{{ translate('Enable n8n Webhook Automation') }}</strong>.</li>
                <li>{{ translate('Paste your n8n') }} <strong>{{ translate('Webhook URL') }}</strong> {{ translate('into the field.') }}</li>
                <li>{{ translate('(Optional) Enter an') }} <strong>{{ translate('API Key') }}</strong> {{ translate('if you secured your n8n webhook with one.') }}</li>
                <li>{{ translate('Select which') }} <strong>{{ translate('Trigger Events') }}</strong> {{ translate('should fire the webhook (e.g., New Message).') }}</li>
                <li>{{ translate('Click') }} <strong>{{ translate('Save') }}</strong>.</li>
            </ol>
            <div class="tip"><strong>{{ translate('Payload sent to n8n:') }}</strong><br>
                {{ translate('The chatbot sends this JSON to your webhook on each trigger:') }}
            </div>
            <pre>{
  "message":         "Visitor's message text",
  "visitor_id":      "unique-visitor-id",
  "conversation_id": "conversation-uid",
  "chatbot_id":      "chatbot-uid",
  "event":           "new_message"
}</pre>
        </div>
    </div>

    {{-- Phase 5 --}}
    <div class="guide-phase">
        <div class="guide-phase-header" onclick="togglePhase(4, this)">
            <div class="guide-phase-num">5</div>
            <div class="guide-phase-title">{{ translate('Test and Go Live') }}</div>
            <i class="bi bi-chevron-down guide-phase-icon"></i>
        </div>
        <div class="guide-phase-body" data-phase="4">
            <ol>
                <li>{{ translate('In n8n, click') }} <strong>Listen For Test Event</strong> {{ translate('on your Webhook node.') }}</li>
                <li>{{ translate('Open your chatbot widget (use the preview button on the chatbot settings page) and type a message.') }}</li>
                <li>{{ translate('n8n should receive the event and show it in the execution log.') }}</li>
                <li>{{ translate('Verify your workflow executes correctly and the') }} <code>message</code> {{ translate('field is returned.') }}</li>
                <li>{{ translate('Once working, click') }} <strong>Activate</strong> {{ translate('in n8n to go live.') }}</li>
            </ol>
            <h4>{{ translate('Troubleshooting') }}</h4>
            <ul>
                <li><strong>{{ translate('No response from chatbot:') }}</strong> {{ translate('Check the Webhook URL is saved correctly and your n8n workflow is active.') }}</li>
                <li><strong>{{ translate('n8n not receiving events:') }}</strong> {{ translate('Ensure the webhook is accessible from the internet (not localhost).') }}</li>
                <li><strong>{{ translate('"Unexpected response" error:') }}</strong> {{ translate('Make sure the Respond to Webhook node returns a JSON object with a') }} <code>message</code> {{ translate('field.') }}</li>
            </ul>
            <div class="tip"><strong>{{ translate('Done!') }}</strong> {{ translate('Your chatbot is now powered by n8n workflows. You can build complex automations — CRM lookups, AI responses, email notifications, and more.') }}</div>
        </div>
    </div>

</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    const completed = new Set();

    window.togglePhase = function (index, header) {
        const body = header.nextElementSibling;
        const icon = header.querySelector('.guide-phase-icon');
        const isOpen = body.classList.contains('open');
        body.classList.toggle('open', !isOpen);
        icon.style.transform = isOpen ? '' : 'rotate(180deg)';
        if (!isOpen) {
            completed.add(index);
            updateProgress();
        }
    };

    function updateProgress() {
        for (let i = 0; i < 5; i++) {
            const dot = document.getElementById('dot-' + i);
            if (completed.has(i)) dot.classList.add('done'), dot.classList.remove('active');
        }
    }
})();
</script>
@endpush
