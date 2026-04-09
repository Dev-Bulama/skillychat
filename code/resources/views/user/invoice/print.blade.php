<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { max-width: 780px; margin: 0 auto; padding: 40px 48px; }

        /* Header */
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; gap: 16px; }
        .company-block .company-name { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .company-block p { color: #555; line-height: 1.6; }
        .invoice-meta { text-align: right; }
        .invoice-meta .word-invoice { font-size: 30px; font-weight: 800; color: #6366f1; letter-spacing: 1px; }
        .invoice-meta .inv-number { font-size: 15px; font-weight: 600; margin-top: 2px; }
        .invoice-meta p { color: #555; font-size: 12px; margin-top: 2px; }

        /* Bill To */
        .bill-to { background: #f4f4f8; border-left: 4px solid #6366f1; padding: 14px 18px; border-radius: 6px; margin-bottom: 28px; }
        .bill-to .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #888; margin-bottom: 6px; }
        .bill-to .client-name { font-size: 16px; font-weight: 700; }
        .bill-to p { color: #555; line-height: 1.5; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #6366f1; color: #fff; }
        thead th { padding: 10px 12px; font-size: 12px; font-weight: 600; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }
        .text-muted { color: #777; }

        /* Totals */
        .totals-table { width: 260px; margin-left: auto; margin-bottom: 28px; }
        .totals-table td { padding: 6px 8px; }
        .totals-table .total-row td { font-weight: 700; font-size: 15px; border-top: 2px solid #1a1a1a; padding-top: 8px; }

        /* Notes */
        .notes { border-top: 1px solid #e5e7eb; padding-top: 18px; }
        .notes .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #888; margin-bottom: 6px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-paid      { background:#d1fae5; color:#065f46; }
        .status-sent      { background:#dbeafe; color:#1e40af; }
        .status-draft     { background:#fef3c7; color:#92400e; }
        .status-overdue   { background:#fee2e2; color:#991b1b; }
        .status-cancelled { background:#f3f4f6; color:#6b7280; }

        /* Print controls */
        .print-controls { text-align: center; padding: 20px; border-top: 1px solid #e5e7eb; margin-top: 32px; }
        .print-controls button { padding: 10px 28px; background: #6366f1; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; margin: 0 6px; }
        .print-controls .close-btn { background: #6b7280; }

        @media print {
            .print-controls { display: none; }
            body { background: #fff; }
            .page { padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="invoice-header">
        <div class="company-block">
            @if($invoice->company_name)
            <div class="company-name">{{ $invoice->company_name }}</div>
            @endif
            @if($invoice->company_email || $invoice->company_phone || $invoice->company_address)
            <p>
                @if($invoice->company_email){{ $invoice->company_email }}<br>@endif
                @if($invoice->company_phone){{ $invoice->company_phone }}<br>@endif
                @if($invoice->company_address){!! nl2br(e($invoice->company_address)) !!}@endif
            </p>
            @endif
        </div>
        <div class="invoice-meta">
            <div class="word-invoice">INVOICE</div>
            <div class="inv-number"># {{ $invoice->invoice_number }}</div>
            <p>Issued: {{ $invoice->issue_date->format('M d, Y') }}</p>
            @if($invoice->due_date)<p>Due: {{ $invoice->due_date->format('M d, Y') }}</p>@endif
            @if($invoice->paid_at)<p style="color:#16a34a;">Paid: {{ $invoice->paid_at->format('M d, Y') }}</p>@endif
            <p>
                <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </p>
        </div>
    </div>

    {{-- Bill To --}}
    <div class="bill-to">
        <div class="label">Bill To</div>
        <div class="client-name">{{ $invoice->client_name }}</div>
        <p>
            @if($invoice->client_email){{ $invoice->client_email }}<br>@endif
            @if($invoice->client_phone){{ $invoice->client_phone }}<br>@endif
            @if($invoice->client_address){!! nl2br(e($invoice->client_address)) !!}@endif
        </p>
    </div>

    {{-- Items --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right fw-bold">{{ $invoice->currency }} {{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals-table">
        <tr>
            <td class="text-muted">Subtotal</td>
            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->vat_rate > 0)
        <tr>
            <td class="text-muted">VAT ({{ $invoice->vat_rate }}%)</td>
            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->vat_amount, 2) }}</td>
        </tr>
        @endif
        @if($invoice->discount > 0)
        <tr>
            <td class="text-muted">Discount</td>
            <td class="text-right" style="color:#dc2626;">− {{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="notes">
        <div class="label">Notes</div>
        <p>{!! nl2br(e($invoice->notes)) !!}</p>
    </div>
    @endif

</div>

{{-- Print Controls (hidden on print) --}}
<div class="print-controls">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <button class="close-btn" onclick="window.close()">Close</button>
</div>

</body>
</html>
