@extends('layouts.master')
@section('content')
@php
    $statusEnum = $order->statusEnum();
    $iconMap = ['instagram'=>'bi-instagram','tiktok'=>'bi-tiktok','youtube'=>'bi-youtube','facebook'=>'bi-facebook','twitter'=>'bi-twitter-x','telegram'=>'bi-telegram','spotify'=>'bi-spotify','linkedin'=>'bi-linkedin','threads'=>'bi-threads'];
    $icon = $iconMap[$order->platform] ?? 'bi-globe';
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="i-card">
            <div class="card--header mb-4">
                <div>
                    <a href="{{ route('user.smm.orders') }}" class="text-muted fs-13 d-block mb-1">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('My Orders') }}
                    </a>
                    <h4 class="card--title mb-0">{{ translate('Order Detail') }}</h4>
                </div>
                <span class="badge {{ $statusEnum->badgeClass() }} fs-13">{{ $statusEnum->label() }}</span>
            </div>

            {{-- Service info --}}
            <div class="p-3 mb-4 border rounded" style="background:#f8f9fa;">
                <div class="d-flex align-items-center gap-3">
                    <span class="icon-btn icon-btn-sm primary circle"><i class="bi {{ $icon }}"></i></span>
                    <div>
                        <strong>{{ $order->service?->name ?? '—' }}</strong><br>
                        <span class="badge bg-primary fs-11">{{ ucfirst($order->platform) }}</span>
                        <span class="badge bg-secondary fs-11 ms-1">{{ ucfirst(str_replace('_',' ',$order->service_type)) }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-3 fs-14">
                <div class="col-md-6">
                    <span class="text-muted">{{ translate('Order ID') }}</span>
                    <div class="fw-semibold">#{{ Str::upper(Str::substr($order->uid, 0, 8)) }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">{{ translate('Ordered At') }}</span>
                    <div class="fw-semibold">{{ $order->created_at->format('M d, Y H:i') }}</div>
                </div>
                <div class="col-12">
                    <span class="text-muted">{{ translate('Target Link') }}</span>
                    <div class="fw-semibold text-break">
                        <a href="{{ $order->target_link }}" target="_blank" rel="noopener">{{ $order->target_link }}</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <span class="text-muted">{{ translate('Quantity Ordered') }}</span>
                    <div class="fw-semibold">{{ number_format($order->quantity) }}</div>
                </div>
                <div class="col-md-4">
                    <span class="text-muted">{{ translate('Start Count') }}</span>
                    <div class="fw-semibold">{{ number_format($order->start_count) }}</div>
                </div>
                <div class="col-md-4">
                    <span class="text-muted">{{ translate('Remaining') }}</span>
                    <div class="fw-semibold">{{ number_format($order->remains) }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">{{ translate('Amount Charged') }}</span>
                    <div class="fw-semibold text--primary">${{ number_format($order->charge, 4) }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">{{ translate('Provider') }}</span>
                    <div class="fw-semibold">{{ $order->service?->provider?->name ?? '—' }}</div>
                </div>
                @if($order->remarks)
                <div class="col-12">
                    <span class="text-muted">{{ translate('Remarks') }}</span>
                    <div class="fw-semibold">{{ $order->remarks }}</div>
                </div>
                @endif
            </div>

            {{-- Progress bar for processing orders --}}
            @if($order->isProcessing() && $order->start_count > 0)
            @php
                $delivered = max(0, $order->quantity - $order->remains);
                $percent   = $order->quantity > 0 ? min(100, round(($delivered / $order->quantity) * 100)) : 0;
            @endphp
            <div class="mt-4">
                <div class="d-flex justify-content-between fs-13 mb-1">
                    <span>{{ translate('Delivery Progress') }}</span>
                    <span>{{ $percent }}%</span>
                </div>
                <div class="progress" style="height:10px;">
                    <div class="progress-bar bg-primary" style="width:{{ $percent }}%"></div>
                </div>
                <div class="fs-12 text-muted mt-1">{{ number_format($delivered) }} / {{ number_format($order->quantity) }} {{ translate('delivered') }}</div>
            </div>
            @endif

        </div>
    </div>

    <div class="col-xl-4">
        <div class="i-card">
            <h5 class="card--title mb-3">{{ translate('Status Timeline') }}</h5>
            <ul class="list-unstyled mb-0">
                @foreach(['pending' => 'Order Accepted', 'processing' => 'Sent to Provider', 'completed' => 'Delivery Complete', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $s => $label)
                @php
                    $statusOrder = ['pending'=>0,'processing'=>1,'completed'=>2,'failed'=>2,'refunded'=>2];
                    $currentOrder = $statusOrder[$order->status] ?? 0;
                    $thisOrder = $statusOrder[$s] ?? 0;
                    $isActive = $order->status === $s;
                    $isDone = $thisOrder < $currentOrder;
                @endphp
                <li class="d-flex align-items-center gap-2 mb-3">
                    <span style="width:24px;height:24px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
                        background:{{ $isActive ? '#6c63ff' : ($isDone ? '#28a745' : '#e9ecef') }};
                        color:{{ ($isActive || $isDone) ? '#fff' : '#adb5bd' }};">
                        <i class="bi bi-{{ $isDone ? 'check' : ($isActive ? 'circle-fill' : 'circle') }}" style="font-size:11px;"></i>
                    </span>
                    <span class="fs-14 {{ $isActive ? 'fw-semibold' : ($isDone ? 'text-muted' : 'text-muted') }}">
                        {{ translate($label) }}
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
