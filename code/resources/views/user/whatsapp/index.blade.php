@extends('layouts.master')
@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="i-card-md">
            <div class="card--header">
                <div>
                    <h4 class="card-title"><i class="bi bi-whatsapp me-2 text-success"></i>{{ translate('WhatsApp Accounts') }}</h4>
                    <p class="text-muted fs-13 mb-0">{{ translate('Connect your WhatsApp Business API account to send/receive messages.') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('user.whatsapp.documentation') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-book me-1"></i>{{ translate('Setup Guide') }}
                    </a>
                    <a href="{{ route('user.whatsapp.create') }}" class="i-btn btn--sm btn--primary">
                        <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Account') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($accounts->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:80px;height:80px;background:#e8f5e9;">
                            <i class="bi bi-whatsapp fs-36 text-success"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold mb-2">{{ translate('No WhatsApp Account Connected') }}</h5>
                    <p class="text-muted mb-4 fs-14" style="max-width:420px;margin:auto;">
                        {{ translate('Connect your WhatsApp Business API to send automated messages, receive inquiries, and auto-create CRM leads.') }}
                    </p>
                    <a href="{{ route('user.whatsapp.create') }}" class="i-btn btn--md btn--primary capsuled me-2">
                        <i class="bi bi-plus-circle me-1"></i>{{ translate('Connect Account') }}
                    </a>
                    <a href="{{ route('user.whatsapp.documentation') }}" class="i-btn btn--md btn--outline capsuled">
                        <i class="bi bi-book me-1"></i>{{ translate('How to Set Up') }}
                    </a>
                </div>
                @else
                <div class="row g-3">
                    @foreach($accounts as $account)
                    <div class="col-md-6 col-xl-4">
                        <div class="i-card border h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                        style="width:44px;height:44px;background:#e8f5e9;">
                                        <i class="bi bi-whatsapp fs-20 text-success"></i>
                                    </span>
                                    <div>
                                        <h6 class="fw-semibold mb-0">{{ $account->name }}</h6>
                                        <small class="text-muted">{{ $account->phone_number ?: 'No number set' }}</small>
                                    </div>
                                </div>
                                <span class="badge {{ $account->isActive() ? 'bg-success' : 'bg-danger' }}">
                                    {{ $account->isActive() ? translate('Active') : translate('Inactive') }}
                                </span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mb-3 fs-13 text-muted">
                                <span><i class="bi bi-chat-dots me-1"></i>{{ $account->messages_count ?? $account->messages->count() }} {{ translate('messages') }}</span>
                                @if($account->ai_enabled)
                                <span class="badge bg-primary fs-11"><i class="bi bi-robot me-1"></i>AI On</span>
                                @endif
                                @if($account->isConfigured())
                                <span class="badge bg-success fs-11"><i class="bi bi-check-circle me-1"></i>Configured</span>
                                @else
                                <span class="badge bg-warning text-dark fs-11"><i class="bi bi-exclamation-triangle me-1"></i>Incomplete</span>
                                @endif
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('user.whatsapp.messages', $account->uid) }}" class="i-btn btn--sm btn--primary">
                                    <i class="bi bi-chat me-1"></i>{{ translate('Inbox') }}
                                </a>
                                <a href="{{ route('user.whatsapp.edit', $account->uid) }}" class="i-btn btn--sm btn--outline">
                                    <i class="bi bi-pencil me-1"></i>{{ translate('Edit') }}
                                </a>
                                <button class="i-btn btn--sm btn--outline test-btn" data-uid="{{ $account->uid }}">
                                    <i class="bi bi-wifi me-1"></i>{{ translate('Test') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
document.querySelectorAll('.test-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const uid = this.dataset.uid;
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i>Testing…';
        fetch('{{ url("user/whatsapp") }}/' + uid + '/test', {headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>r.json()).then(d => {
                alert(d.success ? '✅ Connected: ' + (d.data?.verified_name || 'OK') : '❌ ' + d.message);
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-wifi me-1"></i>Test';
            }).catch(()=>{this.disabled=false;this.innerHTML='<i class="bi bi-wifi me-1"></i>Test';});
    });
});
</script>
@endpush
