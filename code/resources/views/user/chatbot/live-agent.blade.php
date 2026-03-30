@extends('layouts.master')

@push('style-include')
<style nonce="{{ csp_nonce() }}">
/* ── Layout ─────────────────────────────────────────────── */
.la-wrap          { display:flex; height:calc(100vh - 180px); gap:0; border-radius:12px; overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,.08); background:#fff; }
.la-list          { width:320px; min-width:260px; border-right:1px solid #e2e8f0; display:flex; flex-direction:column; flex-shrink:0; }
.la-list-header   { padding:16px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; }
.la-list-header h6{ margin:0; font-weight:700; font-size:.95rem; }
.la-list-body     { flex:1; overflow-y:auto; }
.la-chat          { flex:1; display:flex; flex-direction:column; min-width:0; }
.la-chat-header   { padding:14px 18px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
.la-messages      { flex:1; overflow-y:auto; padding:18px; display:flex; flex-direction:column; gap:10px; background:#f8fafc; }
.la-composer      { border-top:1px solid #e2e8f0; padding:12px 16px; display:flex; gap:8px; flex-shrink:0; background:#fff; }
.la-composer textarea { resize:none; flex:1; border-radius:8px; font-size:.9rem; border:1px solid #cbd5e1; padding:8px 12px; }
.la-composer textarea:focus { outline:none; border-color:#6366f1; }

/* ── Conversation list items ────────────────────────────── */
.la-item          { padding:12px 16px; border-bottom:1px solid #f1f5f9; cursor:pointer; transition:.15s; }
.la-item:hover    { background:#f8fafc; }
.la-item.active   { background:#eef2ff; border-left:3px solid #6366f1; }
.la-item-name     { font-weight:600; font-size:.88rem; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.la-item-preview  { font-size:.78rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.la-item-meta     { display:flex; align-items:center; gap:4px; justify-content:space-between; margin-top:4px; }

/* ── Status badges ──────────────────────────────────────── */
.la-badge         { font-size:.68rem; padding:2px 7px; border-radius:12px; font-weight:600; }
.la-badge.pending { background:#fef3c7; color:#92400e; }
.la-badge.active  { background:#d1fae5; color:#065f46; }
.la-badge.resolved{ background:#e2e8f0; color:#475569; }

/* ── Notification pill ──────────────────────────────────── */
.la-pending-pill  { background:#ef4444; color:#fff; font-size:.68rem; font-weight:700; padding:1px 6px; border-radius:10px; display:none; }
.la-pending-pill.visible { display:inline-block; }

/* ── Messages ───────────────────────────────────────────── */
.msg-row          { display:flex; }
.msg-row.visitor  { justify-content:flex-end; }
.msg-row.agent    { justify-content:flex-start; }
.msg-row.ai       { justify-content:flex-start; }
.msg-row.system   { justify-content:center; }
.msg-bubble       { max-width:68%; padding:9px 14px; border-radius:12px; font-size:.875rem; line-height:1.5; word-break:break-word; }
.msg-row.visitor .msg-bubble  { background:#6366f1; color:#fff; border-bottom-right-radius:3px; }
.msg-row.agent   .msg-bubble  { background:#fff; border:1px solid #e2e8f0; color:#1e293b; border-bottom-left-radius:3px; }
.msg-row.ai      .msg-bubble  { background:#f1f5f9; color:#334155; border-bottom-left-radius:3px; }
.msg-row.system  .msg-bubble  { background:transparent; color:#94a3b8; font-size:.75rem; }
.msg-meta         { font-size:.7rem; color:#94a3b8; margin-top:3px; }
.msg-row.visitor .msg-meta    { text-align:right; }

/* ── Empty state ────────────────────────────────────────── */
.la-empty         { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94a3b8; text-align:center; padding:40px; }
.la-empty i       { font-size:3rem; margin-bottom:12px; }

/* ── Action buttons in header ───────────────────────────── */
.la-btn           { padding:5px 14px; border-radius:7px; border:none; font-size:.82rem; font-weight:600; cursor:pointer; transition:.15s; display:inline-flex; align-items:center; gap:5px; }
.la-btn-claim     { background:#6366f1; color:#fff; }
.la-btn-claim:hover { background:#4f46e5; }
.la-btn-resolve   { background:#10b981; color:#fff; }
.la-btn-resolve:hover { background:#059669; }
.la-btn-secondary { background:#f1f5f9; color:#475569; }
.la-btn-secondary:hover { background:#e2e8f0; }

/* ── Status selector ─────────────────────────────────────── */
.la-status-sel    { border:1px solid #e2e8f0; border-radius:7px; padding:5px 10px; font-size:.82rem; color:#334155; background:#fff; cursor:pointer; }

/* ── Notification toast ─────────────────────────────────── */
.la-toast         { position:fixed; top:20px; right:20px; z-index:9999; background:#1e293b; color:#fff; padding:12px 18px; border-radius:10px; font-size:.875rem; box-shadow:0 4px 20px rgba(0,0,0,.2); transform:translateX(120%); transition:transform .3s ease; max-width:320px; }
.la-toast.show    { transform:translateX(0); }
.la-toast .la-toast-icon { font-size:1.2rem; margin-right:8px; }
</style>
@endpush

@section('content')

{{-- Stats row --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Pending')}}</p>
                        <h3 class="mb-0" id="stat-pending">{{$stats['pending']}}</h3>
                    </div>
                    <div class="icon-box warning"><i class="bi bi-clock-history"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Active')}}</p>
                        <h3 class="mb-0" id="stat-active">{{$stats['active']}}</h3>
                    </div>
                    <div class="icon-box success"><i class="bi bi-chat-dots"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Resolved Today')}}</p>
                        <h3 class="mb-0">{{$stats['resolved_today']}}</h3>
                    </div>
                    <div class="icon-box info"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main live agent interface --}}
<div class="la-wrap">

    {{-- LEFT: Conversation list --}}
    <div class="la-list">
        <div class="la-list-header">
            <div class="d-flex align-items-center gap-2">
                <h6>{{translate('Conversations')}}</h6>
                <span class="la-pending-pill" id="pendingPill">0</span>
            </div>
            @if($agent)
            <select class="la-status-sel" id="agent-status">
                <option value="online"  {{$agent->status === 'online'  ? 'selected' : ''}}>● {{translate('Online')}}</option>
                <option value="away"    {{$agent->status === 'away'    ? 'selected' : ''}}>◐ {{translate('Away')}}</option>
                <option value="busy"    {{$agent->status === 'busy'    ? 'selected' : ''}}>● {{translate('Busy')}}</option>
                <option value="offline" {{$agent->status === 'offline' ? 'selected' : ''}}>○ {{translate('Offline')}}</option>
            </select>
            @endif
        </div>
        <div class="la-list-body" id="conversationList">
            @forelse($conversations as $conv)
            <div class="la-item" data-uid="{{$conv->uid}}" onclick="openConversation('{{$conv->uid}}', this)">
                <div class="la-item-name">{{$conv->visitor_name ?? 'Anonymous'}}</div>
                <div class="la-item-preview">{{$conv->chatbot->name}}</div>
                <div class="la-item-meta">
                    @if($conv->status === 'human_requested')
                        <span class="la-badge pending">{{translate('Pending')}}</span>
                    @elseif($conv->status === 'human_active')
                        <span class="la-badge active">{{translate('Active')}}</span>
                    @else
                        <span class="la-badge resolved">{{translate('Resolved')}}</span>
                    @endif
                    <small class="text-muted" style="font-size:.7rem;">{{$conv->last_message_at?->diffForHumans()}}</small>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted px-3">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                <small>{{translate('No active conversations')}}</small>
            </div>
            @endforelse
        </div>
    </div>

    {{-- RIGHT: Chat panel --}}
    <div class="la-chat">
        {{-- Empty state --}}
        <div class="la-empty" id="emptyState">
            <i class="bi bi-chat-square-dots"></i>
            <p class="fw-600">{{translate('Select a conversation')}}</p>
            <small>{{translate('Choose a conversation from the left to start chatting.')}}</small>
        </div>

        {{-- Active chat (hidden until conv selected) --}}
        <div id="chatPanel" style="display:none; flex:1; display:flex; flex-direction:column; min-height:0;">
            <div class="la-chat-header">
                <div>
                    <strong id="chatVisitorName">—</strong>
                    <small class="text-muted d-block" id="chatMeta"></small>
                </div>
                <div class="d-flex gap-2">
                    <button class="la-btn la-btn-claim" id="claimBtn" onclick="claimConversation()" style="display:none;">
                        <i class="bi bi-hand-index"></i> {{translate('Claim')}}
                    </button>
                    <button class="la-btn la-btn-secondary" id="resumeAiBtn" onclick="resumeAI()" style="display:none;">
                        <i class="bi bi-robot"></i> {{translate('Resume AI')}}
                    </button>
                    <button class="la-btn la-btn-resolve" id="resolveBtn" onclick="resolveConversation()" style="display:none;">
                        <i class="bi bi-check-lg"></i> {{translate('Resolve')}}
                    </button>
                </div>
            </div>

            <div class="la-messages" id="messagesArea"></div>

            <div class="la-composer" id="composerArea" style="display:none;">
                <textarea id="messageInput" rows="2"
                    placeholder="{{translate('Type a message… (Enter to send, Shift+Enter for newline)')}}"
                    onkeydown="handleKey(event)"></textarea>
                <button class="la-btn la-btn-claim" onclick="sendMessage()">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Notification toast --}}
<div class="la-toast" id="laToast">
    <span class="la-toast-icon">🔔</span>
    <span id="laToastMsg"></span>
</div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    'use strict';

    const ROUTES = {
        getConv:    uid => `{{ route('user.live-agent.conversation', '') }}/${uid}`,
        claim:      '{{ route('user.live-agent.claim') }}',
        send:       '{{ route('user.live-agent.send-message') }}',
        resolve:    '{{ route('user.live-agent.resolve') }}',
        resumeAI:   '{{ route('user.live-agent.resume-ai') }}',
        setStatus:  '{{ route('user.live-agent.set-status') }}',
        pollMsgs:   '{{ route('user.live-agent.poll-messages') }}',
        pending:    '{{ route('user.live-agent.pending-count') }}',
    };
    const CSRF = '{{ csrf_token() }}';

    let activeUid        = null;
    let lastMessageId    = null;
    let pollInterval     = null;
    let pendingPollTimer = null;
    let knownPending     = {{ $stats['pending'] }};
    let activeStatus     = null;

    // ── Helpers ───────────────────────────────────────────
    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        }).then(r => r.json());
    }

    function showToast(msg) {
        const t = document.getElementById('laToast');
        document.getElementById('laToastMsg').textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 4000);
    }

    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Open conversation ─────────────────────────────────
    window.openConversation = function (uid, el) {
        if (activeUid === uid) return;
        activeUid     = uid;
        lastMessageId = null;

        document.querySelectorAll('.la-item').forEach(i => i.classList.remove('active'));
        if (el) el.classList.add('active');

        document.getElementById('emptyState').style.display  = 'none';
        const panel = document.getElementById('chatPanel');
        panel.style.display = 'flex';

        document.getElementById('messagesArea').innerHTML =
            '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        stopPoll();
        loadConversation();
    };

    function loadConversation() {
        if (!activeUid) return;
        fetch(ROUTES.getConv(activeUid))
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const c = data.conversation;
                activeStatus = c.status;
                document.getElementById('chatVisitorName').textContent = c.visitor_name;
                document.getElementById('chatMeta').textContent =
                    (c.visitor_email ? c.visitor_email + ' · ' : '') + c.chatbot_name;
                renderMessages(data.messages);
                updateActionButtons(c.status);
                if (data.messages.length) {
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
                startPoll();
            });
    }

    // ── Render messages ───────────────────────────────────
    function renderMessages(messages, append = false) {
        const area = document.getElementById('messagesArea');
        if (!append) area.innerHTML = '';
        messages.forEach(m => area.appendChild(buildBubble(m)));
        area.scrollTop = area.scrollHeight;
    }

    function buildBubble(m) {
        const isNote = m.is_internal_note;
        const row = document.createElement('div');
        row.className = 'msg-row ' + (m.sender_type === 'visitor' ? 'visitor' : (m.sender_type === 'agent' ? 'agent' : (m.sender_type === 'system' ? 'system' : 'ai')));
        if (isNote) row.style.opacity = '.65';
        const label = m.sender_type === 'agent' ? ('🧑 ' + (m.agent_name || '{{ translate("Agent") }}')) :
                      m.sender_type === 'ai'    ? '🤖 AI' :
                      m.sender_type === 'system'? '' : '👤 {{ translate("Visitor") }}';
        row.innerHTML = `
            <div>
                ${label ? `<div class="msg-meta">${escHtml(label)}${isNote ? ' · <em>{{ translate("internal") }}</em>' : ''}</div>` : ''}
                <div class="msg-bubble">${escHtml(m.message)}</div>
                <div class="msg-meta">${escHtml(m.formatted_time)}</div>
            </div>`;
        return row;
    }

    // ── Action buttons ────────────────────────────────────
    function updateActionButtons(status) {
        activeStatus = status;
        const claim   = document.getElementById('claimBtn');
        const resolve = document.getElementById('resolveBtn');
        const resume  = document.getElementById('resumeAiBtn');
        const composer= document.getElementById('composerArea');

        claim.style.display   = status === 'human_requested' ? '' : 'none';
        resolve.style.display = (status === 'human_active')  ? '' : 'none';
        resume.style.display  = (status === 'human_active')  ? '' : 'none';
        composer.style.display= (status === 'human_active')  ? '' : 'none';
    }

    // ── Polling ───────────────────────────────────────────
    function startPoll() {
        stopPoll();
        pollInterval = setInterval(doPoll, 3000);
    }
    function stopPoll() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }
    function doPoll() {
        if (!activeUid) return;
        post(ROUTES.pollMsgs, { conversation_id: activeUid, last_message_id: lastMessageId })
            .then(data => {
                if (!data.success) return;
                if (data.messages && data.messages.length) {
                    renderMessages(data.messages, true);
                    lastMessageId = data.messages[data.messages.length - 1].id;
                }
                if (data.conversation_status && data.conversation_status !== activeStatus) {
                    updateActionButtons(data.conversation_status);
                }
            }).catch(() => {});
    }

    // ── Pending-count poll (for notification) ─────────────
    function pollPending() {
        fetch(ROUTES.pending)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const count = data.pending_count;
                const pill  = document.getElementById('pendingPill');
                const stat  = document.getElementById('stat-pending');
                if (stat) stat.textContent = count;
                pill.textContent = count;
                pill.classList.toggle('visible', count > 0);
                if (count > knownPending) {
                    showToast('{{ translate("New conversation waiting for an agent!") }}');
                    // Play a subtle sound if supported
                    try { new Audio('data:audio/wav;base64,UklGRnoAAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQoAAAABAAAAAQAA').play().catch(()=>{}); } catch(e){}
                }
                knownPending = count;
            }).catch(() => {});
    }
    pendingPollTimer = setInterval(pollPending, 8000);

    // ── Claim ─────────────────────────────────────────────
    window.claimConversation = function () {
        post(ROUTES.claim, { conversation_id: activeUid })
            .then(data => {
                if (data.success) {
                    updateActionButtons('human_active');
                    startPoll();
                } else {
                    alert(data.message || '{{ translate("Could not claim conversation.") }}');
                }
            });
    };

    // ── Send message ──────────────────────────────────────
    window.sendMessage = function () {
        const input = document.getElementById('messageInput');
        const msg   = input.value.trim();
        if (!msg || !activeUid) return;
        input.value = '';
        post(ROUTES.send, { conversation_id: activeUid, message: msg })
            .then(data => {
                if (data.success && data.message) {
                    renderMessages([{ ...data.message, formatted_time: 'just now', agent_name: '{{ translate("You") }}' }], true);
                    lastMessageId = data.message.id;
                }
            });
    };

    window.handleKey = function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    };

    // ── Resolve ───────────────────────────────────────────
    window.resolveConversation = function () {
        if (!confirm('{{ translate("Mark this conversation as resolved?") }}')) return;
        post(ROUTES.resolve, { conversation_id: activeUid })
            .then(data => {
                if (data.success) {
                    stopPoll();
                    updateActionButtons('resolved');
                    document.getElementById('composerArea').style.display = 'none';
                    renderMessages([{ sender_type:'system', message:'{{ translate("Conversation resolved.") }}', formatted_time:'', is_internal_note:false }], true);
                    // Refresh sidebar item badge
                    const item = document.querySelector(`.la-item[data-uid="${activeUid}"]`);
                    if (item) {
                        item.querySelector('.la-badge').className = 'la-badge resolved';
                        item.querySelector('.la-badge').textContent = '{{ translate("Resolved") }}';
                    }
                }
            });
    };

    // ── Resume AI ─────────────────────────────────────────
    window.resumeAI = function () {
        post(ROUTES.resumeAI, { conversation_id: activeUid })
            .then(data => {
                if (data.success) {
                    updateActionButtons('ai_active');
                    renderMessages([{ sender_type:'system', message:'{{ translate("AI has resumed. The bot will now handle replies.") }}', formatted_time:'', is_internal_note:false }], true);
                }
            });
    };

    // ── Agent status ──────────────────────────────────────
    const statusSel = document.getElementById('agent-status');
    if (statusSel) {
        statusSel.addEventListener('change', function () {
            @if($agent)
            post(ROUTES.setStatus, { chatbot_id: {{ $agent->chatbot_id ?? 0 }}, status: this.value })
                .then(data => { if (!data.success) alert(data.message); });
            @endif
        });
    }
})();
</script>
@endpush

@endsection
