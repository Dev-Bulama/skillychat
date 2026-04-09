<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #6366f1; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; opacity: .85; font-size: 13px; }
        .body { padding: 28px 32px; }
        table { width: 100%; border-collapse: collapse; margin: 18px 0; }
        th { background: #6366f1; color: #fff; padding: 9px 12px; text-align: left; font-size: 13px; }
        td { padding: 9px 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .totals td { border: none; }
        .total-row td { font-weight: 700; font-size: 15px; padding-top: 12px; border-top: 2px solid #333; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .paid { background: #d1fae5; color: #065f46; }
        .sent { background: #dbeafe; color: #1e40af; }
        .draft { background: #fef3c7; color: #92400e; }
        .footer { background: #f8f9fa; padding: 18px 32px; font-size: 12px; color: #888; text-align: center; }
        .btn { display: inline-block; padding: 10px 24px; background: #6366f1; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Invoice {{ $invoice->invoice_number }}</h1>
        <p>From {{ $invoice->company_name ?: 'Your Vendor' }}</p>
    </div>
    <div class="body">
        <p>Hi {{ $invoice->client_name }},</p>
        <p>Please find your invoice details below. If you have any questions, feel free to reply to this email.</p>

        <p>
            <strong>Status:</strong>
            <span class="badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span> &nbsp;
            <strong>Amount Due:</strong> {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
            @if($invoice->due_date)&nbsp;<strong>Due:</strong> {{ $invoice->due_date->format('M d, Y') }}@endif
        </p>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td><strong>{{ $invoice->currency }} {{ number_format($item->total, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals" style="width:260px;margin-left:auto;">
            <tr><td>Subtotal</td><td style="text-align:right;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
            @if($invoice->vat_rate > 0)
            <tr><td>VAT ({{ $invoice->vat_rate }}%)</td><td style="text-align:right;">{{ $invoice->currency }} {{ number_format($invoice->vat_amount, 2) }}</td></tr>
            @endif
            @if($invoice->discount > 0)
            <tr><td>Discount</td><td style="text-align:right;color:#dc2626;">− {{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</td></tr>
            @endif
            <tr class="total-row"><td>TOTAL</td><td style="text-align:right;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td></tr>
        </table>

        @if($invoice->notes)
        <p style="border-top:1px solid #eee;padding-top:14px;color:#555;font-size:13px;"><strong>Notes:</strong><br>{{ $invoice->notes }}</p>
        @endif
    </div>
    <div class="footer">
        This invoice was sent by {{ $invoice->company_name ?: site_settings('site_name') }}. Please do not reply if you have already paid.
    </div>
</div>
</body>
</html>
