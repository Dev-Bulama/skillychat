@extends('admin.layouts.master')
@section('content')
<div class="i-card-md">
    <div class="card--header">
        <h4 class="card-title">{{ translate('SMM Boost Orders') }}</h4>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <form method="get" class="row g-2 mb-4">
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ translate('All Statuses') }}</option>
                    @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="platform" class="form-select form-select-sm">
                    <option value="">{{ translate('All Platforms') }}</option>
                    @foreach($platforms as $val => $label)
                    <option value="{{ $val }}" {{ request('platform') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="i-btn btn--sm btn--outline w-100">{{ translate('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle fs-14">
                <thead>
                    <tr>
                        <th>{{ translate('Order ID') }}</th>
                        <th>{{ translate('User') }}</th>
                        <th>{{ translate('Service') }}</th>
                        <th>{{ translate('Platform') }}</th>
                        <th>{{ translate('Qty') }}</th>
                        <th>{{ translate('Charge') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Date') }}</th>
                        <th>{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php $statusEnum = $order->statusEnum(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.smm.orders.detail', $order->id) }}" class="text-primary">
                                #{{ Str::upper(Str::substr($order->uid, 0, 8)) }}
                            </a>
                        </td>
                        <td>{{ $order->user?->name ?? '—' }}</td>
                        <td>{{ Str::limit($order->service?->name ?? '—', 30) }}</td>
                        <td><span class="badge bg-primary">{{ ucfirst($order->platform) }}</span></td>
                        <td>{{ number_format($order->quantity) }}</td>
                        <td>${{ number_format($order->charge, 2) }}</td>
                        <td>
                            <span class="badge {{ $statusEnum->badgeClass() }}">
                                {{ $statusEnum->label() }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.smm.orders.detail', $order->id) }}"
                                class="i-btn btn--sm btn--outline">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            @include('admin.partials.not_found')
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->withQueryString()->links() }}
    </div>
</div>
@endsection
