<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    /**
     * List all invoices for the authenticated user.
     */
    public function index(Request $request): View
    {
        $invoices = Invoice::where('user_id', $this->user->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('invoice_number', 'like', '%' . $request->search . '%')
                       ->orWhere('client_name', 'like', '%' . $request->search . '%')
                       ->orWhere('client_email', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(paginateNumber());

        $stats = [
            'total'   => Invoice::where('user_id', $this->user->id)->count(),
            'paid'    => Invoice::where('user_id', $this->user->id)->where('status', 'paid')->count(),
            'unpaid'  => Invoice::where('user_id', $this->user->id)->whereIn('status', ['draft', 'sent'])->count(),
            'overdue' => Invoice::where('user_id', $this->user->id)->where('status', 'overdue')->count(),
        ];

        return view('user.invoice.index', [
            'meta_data' => $this->metaData(['title' => translate('Invoices')]),
            'invoices'  => $invoices,
            'stats'     => $stats,
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('user.invoice.form', [
            'meta_data' => $this->metaData(['title' => translate('New Invoice')]),
            'invoice'   => null,
        ]);
    }

    /**
     * Store a new invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_name'     => 'required|string|max:200',
            'client_email'    => 'nullable|email|max:200',
            'client_phone'    => 'nullable|string|max:50',
            'client_address'  => 'nullable|string',
            'company_name'    => 'nullable|string|max:200',
            'company_email'   => 'nullable|email|max:200',
            'company_phone'   => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'issue_date'      => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:issue_date',
            'vat_rate'        => 'nullable|numeric|min:0|max:100',
            'discount'        => 'nullable|numeric|min:0',
            'currency'        => 'nullable|string|max:10',
            'notes'           => 'nullable|string',
            'status'          => 'required|in:draft,sent,paid,overdue,cancelled',
            'items'           => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'user_id'         => $this->user->id,
                'client_name'     => $data['client_name'],
                'client_email'    => $data['client_email'] ?? null,
                'client_phone'    => $data['client_phone'] ?? null,
                'client_address'  => $data['client_address'] ?? null,
                'company_name'    => $data['company_name'] ?? null,
                'company_email'   => $data['company_email'] ?? null,
                'company_phone'   => $data['company_phone'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date'] ?? null,
                'vat_rate'        => $data['vat_rate'] ?? 0,
                'discount'        => $data['discount'] ?? 0,
                'currency'        => $data['currency'] ?? 'USD',
                'notes'           => $data['notes'] ?? null,
                'status'          => $data['status'],
                'subtotal'        => 0,
                'vat_amount'      => 0,
                'total'           => 0,
                'paid_at'         => $data['status'] === 'paid' ? now() : null,
            ]);

            foreach ($data['items'] as $i => $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'sort_order'  => $i,
                ]);
            }

            $invoice->recalculate();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with(response_status('Failed to create invoice: ' . $e->getMessage(), 'error'));
        }

        return redirect()->route('user.invoice.show', $invoice->uid)
            ->with(response_status('Invoice ' . $invoice->invoice_number . ' created.'));
    }

    /**
     * Show a single invoice.
     */
    public function show(string $uid): View
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->with('items')
            ->firstOrFail();

        return view('user.invoice.show', [
            'meta_data' => $this->metaData(['title' => $invoice->invoice_number]),
            'invoice'   => $invoice,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(string $uid): View
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->with('items')
            ->firstOrFail();

        return view('user.invoice.form', [
            'meta_data' => $this->metaData(['title' => translate('Edit') . ' ' . $invoice->invoice_number]),
            'invoice'   => $invoice,
        ]);
    }

    /**
     * Update an existing invoice.
     */
    public function update(Request $request, string $uid): RedirectResponse
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->firstOrFail();

        $data = $request->validate([
            'client_name'     => 'required|string|max:200',
            'client_email'    => 'nullable|email|max:200',
            'client_phone'    => 'nullable|string|max:50',
            'client_address'  => 'nullable|string',
            'company_name'    => 'nullable|string|max:200',
            'company_email'   => 'nullable|email|max:200',
            'company_phone'   => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'issue_date'      => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:issue_date',
            'vat_rate'        => 'nullable|numeric|min:0|max:100',
            'discount'        => 'nullable|numeric|min:0',
            'currency'        => 'nullable|string|max:10',
            'notes'           => 'nullable|string',
            'status'          => 'required|in:draft,sent,paid,overdue,cancelled',
            'items'           => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice->update([
                'client_name'     => $data['client_name'],
                'client_email'    => $data['client_email'] ?? null,
                'client_phone'    => $data['client_phone'] ?? null,
                'client_address'  => $data['client_address'] ?? null,
                'company_name'    => $data['company_name'] ?? null,
                'company_email'   => $data['company_email'] ?? null,
                'company_phone'   => $data['company_phone'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'issue_date'      => $data['issue_date'],
                'due_date'        => $data['due_date'] ?? null,
                'vat_rate'        => $data['vat_rate'] ?? 0,
                'discount'        => $data['discount'] ?? 0,
                'currency'        => $data['currency'] ?? 'USD',
                'notes'           => $data['notes'] ?? null,
                'status'          => $data['status'],
                'paid_at'         => $data['status'] === 'paid' ? ($invoice->paid_at ?? now()) : null,
            ]);

            // Replace items
            $invoice->items()->delete();
            foreach ($data['items'] as $i => $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'sort_order'  => $i,
                ]);
            }

            $invoice->recalculate();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with(response_status('Failed to update invoice: ' . $e->getMessage(), 'error'));
        }

        return redirect()->route('user.invoice.show', $invoice->uid)
            ->with(response_status('Invoice updated.'));
    }

    /**
     * Delete an invoice.
     */
    public function destroy(string $uid): RedirectResponse
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->firstOrFail();

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('user.invoice.index')
            ->with(response_status('Invoice deleted.'));
    }

    /**
     * Printable / PDF view (no layout).
     */
    public function print(string $uid): View
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->with('items', 'user')
            ->firstOrFail();

        return view('user.invoice.print', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Mark invoice as paid.
     */
    public function markPaid(string $uid): RedirectResponse
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->firstOrFail();

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        return back()->with(response_status('Invoice marked as paid.'));
    }

    /**
     * Send invoice via email to the client.
     */
    public function sendEmail(string $uid): RedirectResponse
    {
        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('uid', $uid)
            ->with('items')
            ->firstOrFail();

        if (empty($invoice->client_email)) {
            return back()->with(response_status('Client email is not set.', 'error'));
        }

        try {
            Mail::send('user.invoice.email', ['invoice' => $invoice], function ($msg) use ($invoice) {
                $msg->to($invoice->client_email, $invoice->client_name)
                    ->subject(translate('Invoice') . ' ' . $invoice->invoice_number . ' from ' . ($invoice->company_name ?: site_settings('site_name')));
            });

            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'sent']);
            }

            return back()->with(response_status('Invoice sent to ' . $invoice->client_email . '.'));
        } catch (\Throwable $e) {
            return back()->with(response_status('Failed to send email: ' . $e->getMessage(), 'error'));
        }
    }

}
