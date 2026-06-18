<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'contract_id',
        'invoice_number',
        'title',
        'amount',
        'tax_amount',
        'total_amount',
        'issued_at',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'tax_amount'   => 'integer',
        'total_amount' => 'integer',
        'issued_at'    => 'date',
        'due_date'     => 'date',
    ];

    public const STATUSES = ['draft', 'issued', 'paid', 'cancelled'];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('paid_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InvoiceFile::class)->orderByDesc('created_at');
    }

    // -------------------------------------------------------------------------
    // Payment amount accessors
    // -------------------------------------------------------------------------

    /** Total amount actually paid (sum of all payment records). */
    public function getPaidAmountAttribute(): int
    {
        if ($this->relationLoaded('payments')) {
            return (int) $this->payments->sum('amount');
        }
        return (int) $this->payments()->sum('amount');
    }

    /** Remaining unpaid balance (capped at 0 — never negative). */
    public function getUnpaidAmountAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /** Amount paid over the invoice total (0 when not overpaid). */
    public function getOverpaidAmountAttribute(): int
    {
        return max(0, $this->paid_amount - $this->total_amount);
    }

    // -------------------------------------------------------------------------
    // State predicates
    // -------------------------------------------------------------------------

    /** True when any payment has been received but the invoice is not yet fully paid. */
    public function getIsPartiallyPaidAttribute(): bool
    {
        $paid = $this->paid_amount;
        return $paid > 0 && $paid < $this->total_amount;
    }

    /** True when the total payments exceed the invoice amount. */
    public function getIsOverpaidAttribute(): bool
    {
        return $this->paid_amount > $this->total_amount;
    }

    /** True when payments cover the full invoice amount. */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->total_amount > 0 && $this->paid_amount >= $this->total_amount;
    }

    /**
     * True when the due date has passed, the invoice is not settled,
     * and there is still an outstanding balance.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (! $this->due_date) {
            return false;
        }
        if (in_array($this->status, ['paid', 'cancelled'])) {
            return false;
        }
        // due_date < today (yesterday or earlier) AND still has unpaid balance
        return $this->due_date->lt(today()) && $this->unpaid_amount > 0;
    }

    /**
     * True when the due date is within the next 7 days (inclusive of today),
     * the invoice is not settled, and has an outstanding balance.
     */
    public function getIsDueSoonAttribute(): bool
    {
        if (! $this->due_date || $this->is_overdue) {
            return false;
        }
        if (in_array($this->status, ['paid', 'cancelled'])) {
            return false;
        }
        $daysLeft = today()->diffInDays($this->due_date, false);

        return $daysLeft >= 0 && $daysLeft <= 7 && $this->unpaid_amount > 0;
    }
}
