@extends('layouts.master')
@section('content')
<div class="row g-0" style="height:calc(100vh - 160px);min-height:500px;">

    {{-- Contact list sidebar --}}
    <div class="col-md-4 col-xl-3 border-end" style="overflow-y:auto;">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h6 class="fw-bold mb-0"><i class="bi bi-whatsapp text-success me-1"></i>{{ $account->name }}</h6>
                <small class="text-muted">{{ $account->phone_number }}</small>
            </div>
            <a href="{{ route('user.whatsapp.index') }}" class="i-btn btn--sm btn--outline">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
        <div class="p-2">
            <input type="text" class="form-control form-control-sm" id="contactSearch" placeholder="{{ translate('Search contacts…') }}">
        </div>
        <div id="contactList">
            @forelse($contacts as $contact)
            <a href="{{ route('user.whatsapp.messages', [$account->uid, 'contact' => $contact->from_number]) }}"
                class="d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none contact-item {{ $selectedNumber == $contact->from_number ? 'active' : '' }}"
                style="{{ $selectedNumber == $contact->from_number ? 'background:#f0fdf4;' : '' }}">
                <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 fw-bold"
                    style="width:40px;height:40px;background:#e8f5e9;color:#16a34a;font-size:16px;">
                    {{ strtoupper(substr($contact->contact_name ?: $contact->from_number, 0, 1)) }}
                </span>
                <div class="overflow-hidden">
                    <p class="fw-semibold mb-0 fs-14 text-dark text-truncate">{{ $contact->contact_name ?: $contact->from_number }}</p>
                    <p class="fs-12 text-muted mb-0 text-truncate">{{ $contact->from_number }}</p>
                </div>
            </a>
            @empty
            <div class="text-center py-5 text-muted fs-14">{{ translate('No conversations yet') }}</div>
            @endforelse
        </div>
    </div>

    {{-- Message thread --}}
    <div class="col-md-8 col-xl-9 d-flex flex-column">
        @if($selectedNumber)
        <div class="p-3 border-bottom d-flex align-items-center gap-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                style="width:40px;height:40px;background:#e8f5e9;color:#16a34a;font-size:16px;">
                {{ strtoupper(substr($thread->first()?->contact_name ?: $selectedNumber, 0, 1)) }}
            </span>
            <div>
                <p class="fw-semibold mb-0">{{ $thread->first()?->contact_name ?: $selectedNumber }}</p>
                <small class="text-muted">{{ $selectedNumber }}</small>
            </div>
        </div>
        <div class="flex-grow-1 p-3 overflow-y-auto" id="threadMessages" style="overflow-y:auto;">
            @forelse($thread as $msg)
            <div class="d-flex {{ $msg->isOutbound() ? 'justify-content-end' : 'justify-content-start' }} mb-2">
                <div class="p-2 px-3 rounded-3 fs-14" style="max-width:70%;{{ $msg->isOutbound() ? 'background:#dcf8c6;' : 'background:#fff;border:1px solid #e0e0e0;' }}">
                    {{ $msg->content }}
                    <div class="fs-11 text-muted mt-1 text-end">{{ $msg->created_at->format('H:i') }}
                        @if($msg->isOutbound())
                        <i class="bi bi-check-all {{ $msg->status == 'read' ? 'text-primary' : '' }}"></i>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">{{ translate('No messages in this thread') }}</div>
            @endforelse
        </div>
        <div class="p-3 border-top">
            <div class="input-group">
                <input type="text" id="replyMsg" class="form-control" placeholder="{{ translate('Type a message…') }}" autocomplete="off">
                <button class="btn btn-success" id="sendBtn" data-uid="{{ $account->uid }}" data-to="{{ $selectedNumber }}">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
        @else
        <div class="flex-grow-1 d-flex align-items-center justify-content-center text-center p-4">
            <div>
                <i class="bi bi-chat-dots fs-50 text-muted d-block mb-3"></i>
                <h5 class="fw-semibold">{{ translate('Select a Conversation') }}</h5>
                <p class="text-muted fs-14">{{ translate('Choose a contact on the left to view messages.') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
// Auto-scroll to bottom of thread
const thread = document.getElementById('threadMessages');
if(thread) thread.scrollTop = thread.scrollHeight;

// Contact search
document.getElementById('contactSearch')?.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('.contact-item').forEach(el => {
        el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Send reply
document.getElementById('sendBtn')?.addEventListener('click', sendMessage);
document.getElementById('replyMsg')?.addEventListener('keydown', e => { if(e.key === 'Enter') sendMessage(); });

function sendMessage() {
    const input  = document.getElementById('replyMsg');
    const btn    = document.getElementById('sendBtn');
    const msg    = input.value.trim();
    const uid    = btn.dataset.uid;
    const to     = btn.dataset.to;
    if(!msg) return;
    btn.disabled = true;
    input.disabled = true;

    fetch('{{ url("user/whatsapp") }}/' + uid + '/send', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({to, message: msg})
    }).then(r=>r.json()).then(d => {
        if(d.success) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-end mb-2';
            div.innerHTML = `<div class="p-2 px-3 rounded-3 fs-14" style="max-width:70%;background:#dcf8c6;">${msg}<div class="fs-11 text-muted mt-1 text-end">${new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})} <i class="bi bi-check-all"></i></div></div>`;
            thread.appendChild(div);
            thread.scrollTop = thread.scrollHeight;
            input.value = '';
        } else { alert('Failed: ' + d.message); }
        btn.disabled = false;
        input.disabled = false;
        input.focus();
    }).catch(()=>{btn.disabled=false;input.disabled=false;});
}
</script>
@endpush
