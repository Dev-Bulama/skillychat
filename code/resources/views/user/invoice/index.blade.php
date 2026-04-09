@extends('layouts.master')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="i-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card--title mb-1">
                        <i class="bi bi-receipt me-2 text--primary"></i>
                        {{ translate('Invoices') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">{{ translate('Create, manage, and send invoices to your clients.') }}</p>
                </div>
                <a href="{{ route('user.invoice.create') }}" class="i-btn btn--sm btn--primary">
                    <i class="bi bi-plus-circle me-1"></i>{{ translate('New Invoice') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="col-12">
        <div class="row g-3">
            @php
            $statCards = [
                ['label'=>translate('Total'),'value'=>$stats['total'],'icon'=>'bi-receipt','color'=>'#6366f1'],
                ['label'=>translate('Paid'),'value'=>$stats['paid'],'icon'=>'bi-check-circle','color'=>'#16a34a'],
                ['label'=>translate('Unpaid'),'value'=>$stats['unpaid'],'icon'=>'bi-hourglass-split','color'=>'#d97706'],
                ['label'=>translate('Overdue'),'value'=>$stats['overdue'],'icon'=>'bi-exclamation-circle','color'=>'#dc2626'],
            ];
            @endphp
            @foreach($statCards as $card)
            <div class="col-6 col-md-3">
                <div class="i-card h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px;background:{{ $card['color'] }}1a;">
                            <i class="bi {{ $card['icon'] }} fs-20" style="color:{{ $card['color'] }};"></i>
                        </div>
                        <div>
                            <div class="fs-22 fw-bold lh-1 mb-1">{{ $card['value'] }}</div>
                            <div class="fs-13 text-muted">{{ $card['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="col-12">
        <form method="get" class="d-flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-control form-control-sm" style="max-width:220px;"
                placeholder="{{ translate('Search invoice or client…') }}">
            <select name="status" class="form-select form-select-sm" style="max-width:140px;" onchange="this.form.submit()">
                <option value="">{{ translate('All Statuses') }}</option>
                @foreach(['draft','sent','paid','overdue','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="i-btn btn--sm btn--outline"><i class="bi bi-search me-1"></i>{{ translate('Search') }}</button>
            @if(request('search') || request('status'))
            <a href="{{ route('user.invoice.index') }}" class="i-btn btn--sm btn--outline">{{ translate('Clear') }}</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="col-12">
        <div class="i-card-md">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="fs-13 text-muted">
                                <th>{{ translate('Invoice #') }}</th>
                                <th>{{ translate('Client') }}</th>
                                <th>{{ translate('Amount') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Issue Date') }}</th>
                                <th>{{ translate('Due Date') }}</th>
                                <th class="text-end">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                            <tr>
                                <td class="fw-semibold">
                                    <a href="{{ route('user.invoice.show', $inv->uid) }}">{{ $inv->invoice_number }}</a>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $inv->client_name }}</div>
                                    @if($inv->client_email)
                                    <div class="fs-12 text-muted">{{ $inv->client_email }}</div>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                                <td><span class="badge {{ $inv->statusBadgeClass() }}">{{ ucfirst($inv->status) }}</span></td>
                                <td class="fs-13">{{ $inv->issue_date->format('M d, Y') }}</td>
                                <td class="fs-13">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : '—' }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('user.invoice.show', $inv->uid) }}"
                                            class="i-btn btn--xs btn--outline" title="{{ translate('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('user.invoice.print', $inv->uid) }}" target="_blank"
                                            class="i-btn btn--xs btn--outline" title="{{ translate('Print / PDF') }}">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        @if($inv->isEditable())
                                        <a href="{{ route('user.invoice.edit', $inv->uid) }}"
                                            class="i-btn btn--xs btn--outline" title="{{ translate('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('user.invoice.destroy', $inv->uid) }}" method="post"
                                            onsubmit="return confirm('{{ translate('Delete this invoice?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="i-btn btn--xs btn--danger" title="{{ translate('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-40 d-block mb-2 opacity-25"></i>
                                    {{ translate('No invoices found.') }}
                                    <a href="{{ route('user.invoice.create') }}" class="d-block mt-2">{{ translate('Create your first invoice') }}</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($invoices->hasPages())
            <div class="card-body border-top pt-3">
                {{ $invoices->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
