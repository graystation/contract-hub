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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('paid_at');
    }
}
