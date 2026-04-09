@extends('admin.layouts.master')
@section('content')
@php
    $statusEnum   = $order->statusEnum();
    $isStuck      = $order->status === 'pending' && $order->logs->isEmpty();
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        {{-- Order Info --}}
        <div class="i-card-md mb-4">
            <div class="card--header">
                <h4 class="card-title">{{ translate('Order Detail') }} — #{{ Str::upper(Str::substr($order->uid, 0, 8)) }}</h4>
                <a href="{{ route('admin.smm.orders') }}" class="i-btn btn--sm btn--outline">{{ translate('Back') }}</a>
            </div>
            <div class="card-body">

                @if($isStuck)
                <div class="alert mb-3" style="background:#fef3c7;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:8px;">
                    <strong><i class="bi bi-exclamation-triangle me-1 text-warning"></i>{{ translate('Order Stuck') }}:</strong>
                    {{ translate('This order was never sent to the provider. Use the Retry button on the right to send it now.') }}
                </div>
                @endif

                @if($order->remarks)
                <div class="alert mb-3" style="background:#fee2e2;border-left:4px solid #ef4444;padding:12px 16px;border-radius:8px;">
                    <strong><i class="bi bi-x-circle me-1 text-danger"></i>{{ translate('Error') }}:</strong>
                    {{ $order->remarks }}
                </div>
                @endif

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
                        <span class="badge bg-{{ match($order->status) { 'processing'=>'primary','completed'=>'success','failed'=>'danger','refunded'=>'secondary', default=>'warning' } }} ms-1">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="col-md-6"><strong>{{ translate('Provider') }}:</strong>
                        {{ $order->service?->provider?->name ?? '—' }}
                        @if($order->service?->provider)
                        <small class="text-muted">({{ $order->service->provider->api_url }})</small>
                        @endif
                    </div>
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
                            {{ strtoupper($log->action) }} — {{ $log->success ? 'SUCCESS' : 'FAILED' }}
                            @if($log->http_status !== '200') (HTTP {{ $log->http_status }}) @endif
                        </span>
                        <small class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <p class="mb-1 fs-12 fw-semibold text-muted">{{ translate('Request Sent') }}</p>
                            <pre class="bg-light p-2 rounded fs-12 mb-0" style="max-height:150px;overflow:auto;">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 fs-12 fw-semibold text-muted">{{ translate('Provider Response') }}</p>
                            <pre class="bg-light p-2 rounded fs-12 mb-0" style="max-height:150px;overflow:auto;">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-32 text-muted opacity-50 d-block mb-2"></i>
                    <p class="text-muted mb-0">{{ translate('No logs yet.') }}</p>
                    <small class="text-muted">{{ translate('Logs appear here once the order is sent to the provider.') }}</small>
                </div>
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

                {{-- Retry: shown for FAILED orders OR stuck PENDING orders --}}
                @if($order->status === 'failed' || $isStuck)
                <form action="{{ route('admin.smm.orders.retry', $order->id) }}" method="post" class="mb-3"
                    onsubmit="return confirm('{{ translate('Send this order to the provider now?') }}')">
                    @csrf
                    <button type="submit" class="i-btn btn--sm btn--primary w-100">
                        <i class="bi bi-send me-1"></i>{{ translate('Send to Provider Now') }}
                    </button>
                </form>
                @endif

                {{-- Sync Status --}}
                @if($order->isProcessing())
                <form action="{{ route('admin.smm.orders.sync', $order->id) }}" method="post" class="mb-3">
                    @csrf
                    <button type="submit" class="i-btn btn--sm btn--outline w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>{{ translate('Sync Status with Provider') }}
                    </button>
                </form>
                @endif

                {{-- Refund --}}
                @if($order->canBeRefunded())
                <form action="{{ route('admin.smm.orders.refund', $order->id) }}" method="post" class="mb-3"
                    onsubmit="return confirm('{{ translate('Refund this order to the user\'s wallet?') }}')">
                    @csrf
                    <button type="submit" class="i-btn btn--sm btn--danger w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ translate('Refund to Wallet') }}
                    </button>
                </form>
                @endif

                <hr class="my-3">

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
                    <button type="submit" class="i-btn btn--sm btn--outline w-100">
                        {{ translate('Update Status') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Provider Info --}}
        @if($order->service?->provider)
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title">{{ translate('Provider Info') }}</h4>
            </div>
            <div class="card-body">
                <div class="fs-13">
                    <div class="mb-2"><strong>{{ translate('Name') }}:</strong> {{ $order->service->provider->name }}</div>
                    <div class="mb-2"><strong>{{ translate('API URL') }}:</strong><br><code class="fs-12">{{ $order->service->provider->api_url }}</code></div>
                    <div class="mb-2"><strong>{{ translate('Service ID') }}:</strong> {{ $order->service->provider_service_id }}</div>
                    <div><strong>{{ translate('Status') }}:</strong>
                        <span class="badge {{ $order->service->provider->isActive() ? 'bg-success' : 'bg-danger' }} ms-1">
                            {{ $order->service->provider->isActive() ? 'Active' : 'INACTIVE' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
