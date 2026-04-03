@extends('layouts.master')
@section('content')
<div class="row g-4">

    <div class="col-12">
        <div class="i-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card--title mb-1">{{ translate('My Boost Orders') }}</h4>
                    <p class="text-muted mb-0 fs-14">{{ translate('Track all your social media boost orders here.') }}</p>
                </div>
                <a href="{{ route('user.smm.index') }}" class="i-btn btn--sm btn--primary">
                    <i class="bi bi-plus-circle me-1"></i>{{ translate('New Order') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Status Filter --}}
    <div class="col-12">
        <div class="i-card">
            <form method="get" class="d-flex gap-2 flex-wrap">
                <select name="status" class="form-select form-select-sm" style="max-width:200px;">
                    <option value="">{{ translate('All Statuses') }}</option>
                    @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="i-btn btn--sm btn--outline">{{ translate('Filter') }}</button>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="i-card">
            <div class="table-responsive">
                <table class="table table-bordered align-middle fs-14">
                    <thead>
                        <tr>
                            <th>{{ translate('Order') }}</th>
                            <th>{{ translate('Service') }}</th>
                            <th>{{ translate('Platform') }}</th>
                            <th>{{ translate('Qty') }}</th>
                            <th>{{ translate('Charge') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Detail') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        @php $statusEnum = $order->statusEnum(); @endphp
                        <tr>
                            <td>
                                <span class="text-muted fs-12">#{{ Str::upper(Str::substr($order->uid, 0, 8)) }}</span>
                            </td>
                            <td>{{ Str::limit($order->service?->name ?? '—', 35) }}</td>
                            <td>
                                <span class="badge bg-primary">{{ ucfirst($order->platform) }}</span>
                            </td>
                            <td>{{ number_format($order->quantity) }}</td>
                            <td>${{ number_format($order->charge, 2) }}</td>
                            <td>
                                <span class="badge {{ $statusEnum->badgeClass() }}">
                                    {{ $statusEnum->label() }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('user.smm.order-detail', $order->uid) }}"
                                    class="i-btn btn--sm btn--outline">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-box fs-40 text-muted d-block mb-2"></i>
                                <p class="text-muted">{{ translate('No orders yet.') }}
                                    <a href="{{ route('user.smm.index') }}">{{ translate('Browse services') }}</a>
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>

</div>
@endsection
