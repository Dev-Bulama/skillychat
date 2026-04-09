@extends('admin.layouts.master')
@section('content')
@php $statusEnum = $order->statusEnum(); @endphp

<div class="row g-4">
    <div class="col-xl-8">
        {{-- Order Info --}}
        <div class="i-card-md mb-4">
            <div class="card--header">
                <h4 class="card-title">{{ translate('Order Detail') }} — #{{ Str::upper(Str::substr($order->uid, 0, 8)) }}</h4>
                <a href="{{ route('admin.smm.orders') }}" class="i-btn btn--sm btn--outline">{{ translate('Back') }}</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>{{ translate('User') }}:</strong> {{ $order->user?->name }} ({{ $order->user?->email }})</div>
                    <div class="col-md-6"><strong>{{ translate('Service') }}:</strong> {{ $order->service?->name }}</div>
                    <div class="col-md-6"><strong>{{ translate('Platform') }}:</strong> {{ ucfirst($order->platform) }}</div>
                    <div class="col-md-6"><strong>{{ translate('Type') }}:</strong> {{ ucfirst(str_replace('_',' ',$order->service_type)) }}</div>
                    <div class="col-md-6"><strong>{{ translate('Target Link') }}:</strong>
                        <a href="{{ $order->target_link }}" target="_blank" rel="noopener" class="text-break">{{ $order->target_link }}</a>
                    </div>
                    <div class="col-md-6"><strong>{{ translate('Quantity') }}:</strong> {{ number_format($order->quantity) }}</div>
                    <div class="col-md-6"><strong>{{ translate('Charge') }}:</strong> ${{ number_format($order->charge, 4) }}</div>
                    <div class="col-md-6"><strong>{{ translate('Provider Order ID') }}:</strong> {{ $order->provider_order_id ?? '—' }}</div>
                    <div class="col-md-6"><strong>{{ translate('Start Count') }}:</strong> {{ number_format($order->start_count) }}</div>
                    <div class="col-md-6"><strong>{{ translate('Remains') }}:</strong> {{ number_format($order->remains) }}</div>
                    <div class="col-md-6">
                        <strong>{{ translate('Status') }}:</strong>
                        <span class="badge {{ $statusEnum->badgeClass() }} ms-1">{{ $statusEnum->label() }}</span>
                    </div>
                    <div class="col-md-6"><strong>{{ translate('Provider') }}:</strong> {{ $order->service?->provider?->name ?? '—' }}</div>
                    @if($order->remarks)
                    <div class="col-12"><strong>{{ translate('Remarks') }}:</strong> {{ $order->remarks }}</div>
                    @endif
                    <div class="col-md-6"><strong>{{ translate('Ordered At') }}:</strong> {{ $order->created_at->format('M d, Y H:i') }}</div>
                    @if($order->sent_to_provider_at)
                    <div class="col-md-6"><strong>{{ translate('Sent to Provider') }}:</strong> {{ $order->sent_to_provider_at->format('M d, Y H:i') }}</div>
                    @endif
                    @if($order->completed_at)
                    <div class="col-md-6"><strong>{{ translate('Completed At') }}:</strong> {{ $order->completed_at->format('M d, Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- API Logs --}}
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title">{{ translate('API Communication Logs') }}</h4>
            </div>
            <div class="card-body">
                @forelse($order->logs->sortByDesc('created_at') as $log)
                <div class="mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge {{ $log->success ? 'bg-success' : 'bg-danger' }}">
                            {{ strtoupper($log->action) }}
                        </span>
                        <small class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <p class="mb-1 fs-12 fw-semibold text-muted">{{ translate('Request') }}</p>
                            <pre class="bg-light p-2 rounded fs-12 mb-0" style="max-height:150px;overflow:auto;">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 fs-12 fw-semibold text-muted">{{ translate('Response') }}</p>
                            <pre class="bg-light p-2 rounded fs-12 mb-0" style="max-height:150px;overflow:auto;">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">{{ translate('No logs yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        {{-- Admin Actions --}}
        <div class="i-card-md mb-4">
            <div class="card--header">
                <h4 class="card-title">{{ translate('Admin Actions') }}</h4>
            </div>
            <div class="card-body">

                {{-- Sync Status --}}
                @if($order->isProcessing())
                <form action="{{ route('admin.smm.orders.sync', $order->id) }}" method="post" class="mb-3">
                    @csrf
                    <button type="submit" class="i-btn btn--sm btn--outline w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>{{ translate('Sync Status with Provider') }}
                    </button>
                </form>
                @endif

                {{-- Retry --}}
                @if($order->status === 'failed')
                <form action="{{ route('admin.smm.orders.retry', $order->id) }}" method="post" class="mb-3"
                    onsubmit="return confirm('{{ translate('Retry sending this order to the provider?') }}')">
                    @csrf
                    <button type="submit" class="i-btn btn--sm btn--primary w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>{{ translate('Retry — Resend to Provider') }}
                    </button>
                </form>
                @endif

                {{-- Refund --}}
                @if($order->canBeRefunded())
                <form action="{{ route('admin.smm.orders.refund', $order->id) }}" method="post" class="mb-3"
                    onsubmit="return confirm('{{ translate('Refund this order?') }}')">
                    @csrf
                    <button type="submit" class="i-btn btn--sm btn--danger w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ translate('Refund to Wallet') }}
                    </button>
                </form>
                @endif

                {{-- Manual Status Update --}}
                <form action="{{ route('admin.smm.orders.update-status', $order->id) }}" method="post">
                    @csrf
                    <div class="form-inner mb-2">
                        <label class="fs-13">{{ translate('Set Status Manually') }}</label>
                        <select name="status" class="form-select form-select-sm">
                            @foreach($statuses as $val => $label)
                            <option value="{{ $val }}" {{ $order->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-inner mb-2">
                        <textarea name="remarks" rows="2" class="form-control form-control-sm"
                            placeholder="{{ translate('Optional remarks') }}">{{ $order->remarks }}</textarea>
                    </div>
                    <button type="submit" class="i-btn btn--sm btn--primary w-100">
                        {{ translate('Update Status') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
