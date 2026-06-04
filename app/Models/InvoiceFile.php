<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceFile extends Model
{
    protected $fillable = [
        'invoice_id',
        'file_name',
        'file_path',
        'file_type',
        'file_hash',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
