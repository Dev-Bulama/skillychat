@extends('layouts.master')
@section('content')
@php
    $editing = !is_null($invoice);
    $formAction = $editing
        ? route('user.invoice.update', $invoice->uid)
        : route('user.invoice.store');
    $title = $editing ? translate('Edit Invoice') . ' — ' . $invoice->invoice_number : translate('New Invoice');
@endphp

<div class="row g-4 justify-content-center">
    <div class="col-xl-10">

        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-receipt me-2"></i>{{ $title }}</h4>
                <a href="{{ route('user.invoice.index') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form action="{{ $formAction }}" method="post" id="invoiceForm">
                    @csrf
                    @if($editing) @method('PUT') @endif

                    <div class="row g-4">

                        {{-- ── From / Company ──────────────────────────── --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3 pb-2 border-bottom">{{ translate('From (Your Details)') }}</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Company / Your Name') }}</label>
                                    <input type="text" name="company_name" class="form-control"
                                        value="{{ old('company_name', $invoice?->company_name ?? site_settings('site_name')) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Company Email') }}</label>
                                    <input type="email" name="company_email" class="form-control"
                                        value="{{ old('company_email', $invoice?->company_email ?? site_settings('site_email')) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Phone') }}</label>
                                    <input type="text" name="company_phone" class="form-control"
                                        value="{{ old('company_phone', $invoice?->company_phone) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Address') }}</label>
                                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $invoice?->company_address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ── Bill To / Client ─────────────────────────── --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3 pb-2 border-bottom">{{ translate('Bill To (Client)') }}</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Client Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="client_name" class="form-control @error('client_name') is-invalid @enderror"
                                        value="{{ old('client_name', $invoice?->client_name) }}" required>
                                    @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Client Email') }}</label>
                                    <input type="email" name="client_email" class="form-control @error('client_email') is-invalid @enderror"
                                        value="{{ old('client_email', $invoice?->client_email) }}">
                                    @error('client_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Client Phone') }}</label>
                                    <input type="text" name="client_phone" class="form-control"
                                        value="{{ old('client_phone', $invoice?->client_phone) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Client Address') }}</label>
                                    <textarea name="client_address" class="form-control" rows="2">{{ old('client_address', $invoice?->client_address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ── Invoice Meta ─────────────────────────────── --}}
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3 pb-2 border-bottom">{{ translate('Invoice Details') }}</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Issue Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                                        value="{{ old('issue_date', $invoice?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                                    @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Due Date') }}</label>
                                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                        value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d')) }}">
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Currency') }}</label>
                                    <input type="text" name="currency" class="form-control"
                                        value="{{ old('currency', $invoice?->currency ?? 'USD') }}" maxlength="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">{{ translate('Status') }}</label>
                                    <select name="status" class="form-select">
                                        @foreach(['draft'=>translate('Draft'),'sent'=>translate('Sent'),'paid'=>translate('Paid'),'overdue'=>translate('Overdue'),'cancelled'=>translate('Cancelled')] as $val => $label)
                                        <option value="{{ $val }}" {{ old('status', $invoice?->status ?? 'draft') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ── Line Items ────────────────────────────────── --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="fw-semibold mb-0">{{ translate('Items / Services') }}</h6>
                                <button type="button" class="i-btn btn--xs btn--outline" id="addItemBtn">
                                    <i class="bi bi-plus me-1"></i>{{ translate('Add Row') }}
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="itemsTable">
                                    <thead class="table-light fs-13">
                                        <tr>
                                            <th>{{ translate('Description') }}</th>
                                            <th style="width:110px">{{ translate('Qty') }}</th>
                                            <th style="width:140px">{{ translate('Unit Price') }}</th>
                                            <th style="width:130px" class="text-end">{{ translate('Total') }}</th>
                                            <th style="width:48px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @php
                                        $existingItems = $invoice?->items ?? collect();
                                        if ($existingItems->isEmpty()) {
                                            $existingItems = collect([null]);
                                        }
                                        @endphp
                                        @foreach($existingItems as $i => $item)
                                        <tr class="item-row">
                                            <td>
                                                <input type="text" name="items[{{ $i }}][description]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old('items.'.$i.'.description', $item?->description) }}"
                                                    placeholder="{{ translate('Service or product description') }}" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $i }}][quantity]"
                                                    class="form-control form-control-sm item-qty"
                                                    value="{{ old('items.'.$i.'.quantity', $item?->quantity ?? 1) }}"
                                                    step="0.01" min="0.01" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $i }}][unit_price]"
                                                    class="form-control form-control-sm item-price"
                                                    value="{{ old('items.'.$i.'.unit_price', $item?->unit_price ?? 0) }}"
                                                    step="0.01" min="0" required>
                                            </td>
                                            <td class="text-end fw-semibold item-line-total fs-13">
                                                {{ number_format(($item?->quantity ?? 1) * ($item?->unit_price ?? 0), 2) }}
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm text-danger remove-item-btn">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ── Totals ────────────────────────────────────── --}}
                        <div class="col-12">
                            <div class="row justify-content-end">
                                <div class="col-md-5">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted fs-13">{{ translate('Subtotal') }}</td>
                                            <td class="text-end fw-semibold" id="summarySubtotal">0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fs-13">
                                                {{ translate('VAT') }} (
                                                <input type="number" name="vat_rate" id="vatRateInput"
                                                    class="form-control form-control-sm d-inline-block"
                                                    style="width:65px;"
                                                    value="{{ old('vat_rate', $invoice?->vat_rate ?? 0) }}"
                                                    step="0.01" min="0" max="100"> %)
                                            </td>
                                            <td class="text-end" id="summaryVat">0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fs-13">
                                                {{ translate('Discount') }}
                                                <input type="number" name="discount" id="discountInput"
                                                    class="form-control form-control-sm d-inline-block"
                                                    style="width:80px;"
                                                    value="{{ old('discount', $invoice?->discount ?? 0) }}"
                                                    step="0.01" min="0">
                                            </td>
                                            <td class="text-end" id="summaryDiscount">0.00</td>
                                        </tr>
                                        <tr class="fw-bold">
                                            <td>{{ translate('Total') }}</td>
                                            <td class="text-end fs-16" id="summaryTotal">0.00</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- ── Notes ────────────────────────────────────── --}}
                        <div class="col-12">
                            <label class="form-label fs-13 fw-semibold">{{ translate('Notes / Terms') }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="{{ translate('Payment terms, bank details, thank you note…') }}">{{ old('notes', $invoice?->notes) }}</textarea>
                        </div>

                        {{-- ── Submit ───────────────────────────────────── --}}
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{ $editing ? translate('Update Invoice') : translate('Create Invoice') }}
                            </button>
                            <a href="{{ route('user.invoice.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    let rowIndex = {{ $invoice?->items->count() ?? 1 }};

    function rowTemplate(i) {
        return `<tr class="item-row">
            <td><input type="text" name="items[${i}][description]" class="form-control form-control-sm" placeholder="{{ translate('Description') }}" required></td>
            <td><input type="number" name="items[${i}][quantity]" class="form-control form-control-sm item-qty" value="1" step="0.01" min="0.01" required></td>
            <td><input type="number" name="items[${i}][unit_price]" class="form-control form-control-sm item-price" value="0" step="0.01" min="0" required></td>
            <td class="text-end fw-semibold item-line-total fs-13">0.00</td>
            <td class="text-center"><button type="button" class="btn btn-sm text-danger remove-item-btn"><i class="bi bi-x"></i></button></td>
        </tr>`;
    }

    document.getElementById('addItemBtn').addEventListener('click', function () {
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', rowTemplate(rowIndex++));
        attachRemove();
        recalc();
    });

    function attachRemove() {
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.onclick = function () {
                if (document.querySelectorAll('.item-row').length > 1) {
                    btn.closest('tr').remove();
                    recalc();
                }
            };
        });
    }

    function recalc() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty').value)   || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const line  = qty * price;
            row.querySelector('.item-line-total').textContent = line.toFixed(2);
            subtotal += line;
        });

        const vatRate  = parseFloat(document.getElementById('vatRateInput').value)  || 0;
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const vat      = subtotal * vatRate / 100;
        const total    = Math.max(subtotal + vat - discount, 0);

        document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('summaryVat').textContent      = vat.toFixed(2);
        document.getElementById('summaryDiscount').textContent = discount.toFixed(2);
        document.getElementById('summaryTotal').textContent    = total.toFixed(2);
    }

    // Wire live events
    document.getElementById('itemsBody').addEventListener('input', recalc);
    document.getElementById('vatRateInput').addEventListener('input', recalc);
    document.getElementById('discountInput').addEventListener('input', recalc);
    attachRemove();
    recalc();
})();
</script>
@endpush
