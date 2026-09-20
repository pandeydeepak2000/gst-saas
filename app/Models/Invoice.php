<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ScopedBy([TenantScope::class])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'created_by',
        'invoice_number',
        'public_uuid',
        'type', // 'tax_invoice' or 'proforma'
        'invoice_date',
        'due_date',
        'sale_type', // LOCAL or CENTRAL
        'tax_mode',  // simple or detailed
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status', // draft, sent, unpaid, partially_paid, paid, cancelled
        'payment_method',
        'transaction_ref',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'taxable_amount' => 'float',
        'cgst_amount' => 'float',
        'sgst_amount' => 'float',
        'igst_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'balance_amount' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->public_uuid)) {
                $invoice->public_uuid = (string) Str::uuid();
            }
            if (!isset($invoice->paid_amount)) {
                $invoice->paid_amount = 0;
            }
            if (!isset($invoice->balance_amount)) {
                $invoice->balance_amount = max(0, ($invoice->total_amount ?? 0) - $invoice->paid_amount);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
    }

    public function getEffectiveTaxModeAttribute(): string
    {
        if (!empty($this->tax_mode)) {
            return $this->tax_mode;
        }
        return $this->company?->tax_mode ?: 'simple';
    }

    public function isProforma(): bool
    {
        return $this->type === 'proforma';
    }

    /**
     * Recalculate and update paid and balance amounts
     */
    public function recalculatePaymentStatus(): void
    {
        $totalPaid = (float) $this->transactions()->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->balance_amount = max(0, (float) $this->total_amount - $totalPaid);

        if ($this->balance_amount <= 0 && $this->total_amount > 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0 && $this->balance_amount > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->paid_amount == 0 && $this->status === 'paid') {
            $this->status = 'unpaid';
        }

        $this->saveQuietly();
    }
}