<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'paid_at'    => 'datetime',
        'subtotal'   => 'decimal:2',
        'vat_rate'   => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'discount'   => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->uid = (string) Str::uuid();

            if (empty($model->invoice_number)) {
                $model->invoice_number = static::generateNumber($model->user_id);
            }
        });
    }

    public static function generateNumber(int $userId): string
    {
        $year  = now()->year;
        $count = static::where('user_id', $userId)
                        ->whereYear('created_at', $year)
                        ->count() + 1;
        return 'INV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id')->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity * unit_price'));
        $vatAmount = round($subtotal * ($this->vat_rate / 100), 2);
        $total     = round($subtotal + $vatAmount - $this->discount, 2);

        $this->update([
            'subtotal'   => $subtotal,
            'vat_amount' => $vatAmount,
            'total'      => $total,
        ]);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid'      => 'bg-success',
            'sent'      => 'bg-info',
            'overdue'   => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default     => 'bg-warning text-dark',   // draft
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }
}
