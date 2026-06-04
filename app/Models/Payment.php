<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount',
        'paid_at',
        'method',
        'memo',
    ];

    protected $casts = [
        'amount'  => 'integer',
        'paid_at' => 'date',
    ];

    public const METHODS = ['bank_transfer', 'cash', 'credit_card', 'other'];

    public const METHOD_LABELS = [
        'bank_transfer' => '銀行振込',
        'cash'          => '現金',
        'credit_card'   => 'クレジットカード',
        'other'         => 'その他',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
