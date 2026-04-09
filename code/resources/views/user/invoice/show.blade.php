@extends('layouts.master')
@section('content')
<div class="row g-4">

    {{-- Header Actions --}}
    <div class="col-12">
        <div class="i-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('user.invoice.index') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="card--title mb-0">{{ $invoice->invoice_number }}</h5>
                            <span class="badge {{ $invoice->statusBadgeClass() }} ms-1">{{ ucfirst($invoice->status) }}</span>
                        </div>
                        <div class="text-muted fs-13">{{ translate('Issued') }}: {{ $invoice->issue_date->format('M d, Y') }}
                            @if($invoice->due_date) · {{ translate('Due') }}: {{ $invoice->due_date->format('M d, Y') }} @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('user.invoice.print', $invoice->uid) }}" target="_blank"
                        class="i-btn btn--sm btn--outline">
                        <i class="bi bi-printer me-1"></i>{{ translate('Print / PDF') }}
                    </a>
                    @if($invoice->isEditable())
                    <a href="{{ route('user.invoice.edit', $invoice->uid) }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-pencil me-1"></i>{{ translate('Edit') }}
                    </a>
                    @endif
                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                    <form action="{{ route('user.invoice.mark-paid', $invoice->uid) }}" method="post">
                        @csrf
                        <button type="submit" class="i-btn btn--sm btn--primary">
                            <i class="bi bi-check-circle me-1"></i>{{ translate('Mark as Paid') }}
                        </button>
                    </form>
                    @endif
                    @if($invoice->client_email)
                    <form action="{{ route('user.invoice.send-email', $invoice->uid) }}" method="post">
                        @csrf
                        <button type="submit" class="i-btn btn--sm btn--outline">
                            <i class="bi bi-envelope me-1"></i>{{ translate('Send Email') }}
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('user.invoice.destroy', $invoice->uid) }}" method="post"
                        onsubmit="return confirm('{{ translate('Delete this invoice?') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="i-btn btn--sm btn--danger">
                            <i class="bi bi-trash me-1"></i>{{ translate('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Preview --}}
    <div class="col-12">
        <div class="i-card-md p-4 p-md-5" id="invoicePreview">

            {{-- Branding --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    @if($invoice->company_name)
                    <div class="fw-bold fs-20 mb-1">{{ $invoice->company_name }}</div>
                    @endif
                    @if($invoice->company_email)
                    <div class="text-muted fs-13">{{ $invoice->company_email }}</div>
                    @endif
                    @if($invoice->company_phone)
                    <div class="text-muted fs-13">{{ $invoice->company_phone }}</div>
                    @endif
                    @if($invoice->company_address)
                    <div class="text-muted fs-13">{!! nl2br(e($invoice->company_address)) !!}</div>
                    @endif
                </div>
                <div class="text-md-end">
                    <div class="fs-28 fw-bold text--primary mb-1">{{ translate('INVOICE') }}</div>
                    <div class="fs-15 fw-semibold"># {{ $invoice->invoice_number }}</div>
                    <div class="text-muted fs-13 mt-1">{{ translate('Issued') }}: {{ $invoice->issue_date->format('M d, Y') }}</div>
                    @if($invoice->due_date)
                    <div class="text-muted fs-13">{{ translate('Due') }}: {{ $invoice->due_date->format('M d, Y') }}</div>
                    @endif
                    @if($invoice->paid_at)
                    <div class="text-success fs-13">{{ translate('Paid') }}: {{ $invoice->paid_at->format('M d, Y') }}</div>
                    @endif
                </div>
            </div>

            {{-- Bill To --}}
            <div class="p-3 rounded-3 mb-4" style="background:#f8f9fa;border-left:4px solid var(--base-color, #6366f1);">
                <div class="fs-12 text-muted fw-semibold text-uppercase mb-2">{{ translate('Bill To') }}</div>
                <div class="fw-bold fs-16">{{ $invoice->client_name }}</div>
                @if($invoice->client_email)<div class="fs-13 text-muted">{{ $invoice->client_email }}</div>@endif
                @if($invoice->client_phone)<div class="fs-13 text-muted">{{ $invoice->client_phone }}</div>@endif
                @if($invoice->client_address)<div class="fs-13 text-muted">{!! nl2br(e($invoice->client_address)) !!}</div>@endif
            </div>

            {{-- Items Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered fs-14 mb-0">
                    <thead style="background:var(--base-color, #6366f1);color:#fff;">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Description') }}</th>
                            <th class="text-end" style="width:100px">{{ translate('Qty') }}</th>
                            <th class="text-end" style="width:130px">{{ translate('Unit Price') }}</th>
                            <th class="text-end" style="width:130px">{{ translate('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end fw-semibold">{{ $invoice->currency }} {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="row justify-content-end mb-4">
                <div class="col-md-4">
                    <table class="table table-sm fs-14 mb-0">
                        <tr>
                            <td class="text-muted">{{ translate('Subtotal') }}</td>
                            <td class="text-end">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->vat_rate > 0)
                        <tr>
                            <td class="text-muted">{{ translate('VAT') }} ({{ $invoice->vat_rate }}%)</td>
                            <td class="text-end">{{ $invoice->currency }} {{ number_format($invoice->vat_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->discount > 0)
                        <tr>
                            <td class="text-muted">{{ translate('Discount') }}</td>
                            <td class="text-end text-danger">− {{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td class="fs-15">{{ translate('TOTAL') }}</td>
                            <td class="text-end fs-15">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
            <div class="border-top pt-3 mt-2">
                <div class="fs-12 text-muted fw-semibold text-uppercase mb-1">{{ translate('Notes') }}</div>
                <p class="fs-14 mb-0">{!! nl2br(e($invoice->notes)) !!}</p>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
