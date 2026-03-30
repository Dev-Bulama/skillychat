@extends('admin.layouts.master')
@section('content')

<form action="{{ route('admin.setting.user.documentation.save') }}" method="POST">
    @csrf
    <div class="i-card-md mb-4">
        <div class="card-header">
            <h5 class="card-title">{{ translate('User Documentation Settings') }}</h5>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-600">{{ translate('Menu Label') }}</label>
                    <input type="text" class="form-control" name="user_docs_label"
                        value="{{ site_settings('user_docs_label', 'Documentation') }}"
                        placeholder="{{ translate('e.g. Getting Started, Help, Documentation') }}">
                    <small class="text-muted">{{ translate('This label appears in the user sidebar navigation.') }}</small>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-600">{{ translate('Documentation Content') }}</label>
                <small class="text-muted d-block mb-2">
                    {{ translate('Write your documentation using the rich text editor below. Supports HTML, images, links, and formatted text. Users will see this on a dedicated Documentation page.') }}
                </small>
                <textarea id="user_docs_content" name="user_docs_content" rows="20" class="form-control"
                    style="font-family:monospace;">{!! site_settings('user_docs_content', '') !!}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="i-btn btn--md success">
                    <i class="las la-save me-1"></i> {{ translate('Save Documentation') }}
                </button>
            </div>

        </div>
    </div>
</form>

{{-- Quick-start template buttons --}}
<div class="i-card-md">
    <div class="card-header">
        <h5 class="card-title">{{ translate('Quick Templates') }}</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">{{ translate('Click a template to populate the documentation editor with a pre-written guide.') }}</p>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="i-btn btn--sm info" onclick="loadTemplate('n8n')">
                <i class="las la-plug me-1"></i> {{ translate('n8n Integration Guide') }}
            </button>
            <button type="button" class="i-btn btn--sm primary" onclick="loadTemplate('live-agent')">
                <i class="las la-headset me-1"></i> {{ translate('Live Agent Setup Guide') }}
            </button>
            <button type="button" class="i-btn btn--sm success" onclick="loadTemplate('getting-started')">
                <i class="las la-star me-1"></i> {{ translate('Getting Started Guide') }}
            </button>
        </div>
    </div>
</div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
const templates = {
    'n8n': `<h2>🔗 How to Connect Your Chatbot to n8n</h2>

<p>n8n is a powerful workflow automation tool that lets your chatbot trigger workflows, send data to external services, and receive intelligent responses from complex pipelines.</p>

<h3>Step 1 — Set Up Your n8n Instance</h3>
<ol>
  <li>Go to <strong>n8n.io</strong> and create a free account, or self-host n8n on your server.</li>
  <li>Once logged in, create a new <strong>Workflow</strong>.</li>
</ol>

<h3>Step 2 — Create a Webhook Trigger</h3>
<ol>
  <li>In your n8n workflow, click <strong>Add Node</strong> and search for <strong>Webhook</strong>.</li>
  <li>Set the method to <strong>POST</strong>.</li>
  <li>Copy the generated Webhook URL — you'll need it in the next step.</li>
  <li>Set <strong>Response Mode</strong> to <strong>Last Node</strong> so n8n sends the response back to the chatbot.</li>
</ol>

<h3>Step 3 — Connect in Your Chatbot Settings</h3>
<ol>
  <li>Go to <strong>AI Chatbots → Edit Chatbot</strong>.</li>
  <li>Scroll to the <strong>n8n Integration</strong> section and enable it.</li>
  <li>Paste your n8n <strong>Webhook URL</strong> into the field provided.</li>
  <li>Save your chatbot.</li>
</ol>

<h3>Step 4 — Build Your n8n Workflow</h3>
<ol>
  <li>After the Webhook node, add your logic nodes (e.g., HTTP Request, AI Agent, Database lookup, etc.).</li>
  <li>At the end of your workflow, add a <strong>Respond to Webhook</strong> node.</li>
  <li>Set the response body to a JSON object: <code>{"message": "Your response here"}</code></li>
  <li>The chatbot will display whatever text is in the <code>message</code> field.</li>
</ol>

<h3>Step 5 — Test It</h3>
<ol>
  <li>Open your chatbot widget and send a test message.</li>
  <li>Check the n8n execution log to confirm the webhook was triggered.</li>
  <li>You should see the chatbot respond with the message from your workflow.</li>
</ol>

<h3>Tips</h3>
<ul>
  <li>The chatbot sends the visitor's <code>message</code>, <code>visitor_id</code>, and <code>conversation_id</code> in the POST body.</li>
  <li>You can use n8n's AI Agent node with OpenAI to build advanced response logic.</li>
  <li>Use n8n credentials to connect to CRMs, databases, email, Slack, and thousands of other services.</li>
</ul>`,

    'live-agent': `<h2>🧑‍💼 How to Use the Live Agent Feature</h2>

<p>The Live Agent feature lets your team take over chatbot conversations in real time when a human touch is needed.</p>

<h3>Step 1 — Enable Human Takeover on Your Chatbot</h3>
<ol>
  <li>Go to <strong>AI Chatbots → Edit Chatbot</strong>.</li>
  <li>Enable the <strong>Human Takeover</strong> toggle.</li>
  <li>Save your chatbot settings.</li>
</ol>

<h3>Step 2 — Monitor the Live Agent Dashboard</h3>
<ol>
  <li>In the sidebar, click <strong>AI Chatbots → Live Agent</strong>.</li>
  <li>You'll see a real-time list of all active conversations.</li>
  <li>Conversations where a visitor requested a human are marked <strong>Pending</strong> with an orange badge.</li>
  <li>A notification appears (and a badge on the menu) when new requests arrive.</li>
</ol>

<h3>Step 3 — Claim a Conversation</h3>
<ol>
  <li>Click on a <strong>Pending</strong> conversation in the left panel.</li>
  <li>Read the conversation history in the chat area on the right.</li>
  <li>Click the <strong>Claim</strong> button to take over from the AI.</li>
  <li>The visitor will now receive your replies instead of AI responses.</li>
</ol>

<h3>Step 4 — Reply in Real Time</h3>
<ol>
  <li>Type your message in the composer at the bottom and press <strong>Enter</strong> (or click Send).</li>
  <li>Messages appear instantly for both you and the visitor.</li>
  <li>New messages from the visitor appear automatically every few seconds — no refresh needed.</li>
</ol>

<h3>Step 5 — Resolve or Resume AI</h3>
<ul>
  <li>Click <strong>Resolve</strong> when the issue is solved — this closes the conversation.</li>
  <li>Click <strong>Resume AI</strong> to hand the conversation back to the AI bot.</li>
</ul>

<h3>Tips</h3>
<ul>
  <li>Set your agent status to <strong>Online</strong> using the dropdown at the top of the Live Agent page.</li>
  <li>The red badge on the "Live Agent" menu item shows how many conversations are waiting.</li>
  <li>You can have multiple agents monitoring the same chatbot simultaneously.</li>
</ul>`,

    'getting-started': `<h2>🚀 Getting Started with SkillyChatbot</h2>

<p>Welcome! This guide will help you get your AI chatbot up and running in minutes.</p>

<h3>Step 1 — Add Your AI API Key</h3>
<ol>
  <li>Go to <strong>AI Chatbots → API Keys</strong>.</li>
  <li>Click <strong>Add Key</strong> and enter your <strong>OpenAI API key</strong> (or Gemini/Claude key).</li>
  <li>Your chatbot will use this key to power its AI responses.</li>
</ol>

<h3>Step 2 — Create Your First Chatbot</h3>
<ol>
  <li>Go to <strong>AI Chatbots → Create Chatbot</strong>.</li>
  <li>Enter a name, select your AI model, and set a welcome message.</li>
  <li>Choose your chatbot's color, position, and language.</li>
  <li>Click <strong>Create</strong>.</li>
</ol>

<h3>Step 3 — Train Your Chatbot</h3>
<ol>
  <li>Open your chatbot and go to the <strong>Training Data</strong> tab.</li>
  <li>Add FAQs, paste text, upload documents, or add website URLs.</li>
  <li>The chatbot will use this knowledge to answer visitor questions.</li>
</ol>

<h3>Step 4 — Embed the Widget on Your Website</h3>
<ol>
  <li>Go to your chatbot's <strong>Integration</strong> tab.</li>
  <li>Copy the embed code snippet.</li>
  <li>Paste it before the closing <code>&lt;/body&gt;</code> tag on your website.</li>
  <li>The chat bubble will appear in the corner of your site.</li>
</ol>

<h3>Step 5 — Monitor Conversations</h3>
<ol>
  <li>View all conversations under <strong>AI Chatbots → My Chatbots → Conversations</strong>.</li>
  <li>Use the <strong>Live Agent</strong> feature to take over chats when needed.</li>
  <li>Check <strong>Analytics</strong> to see how your chatbot is performing.</li>
</ol>

<h3>Need Help?</h3>
<p>Contact our support team or refer to the full documentation for advanced features like n8n integration, voice support, and bulk messaging.</p>`,
};

function loadTemplate(key) {
    if (!templates[key]) return;
    if (!confirm('{{ translate("This will replace the current content. Continue?") }}')) return;
    document.getElementById('user_docs_content').value = templates[key];
}
</script>
@endpush

@endsection
