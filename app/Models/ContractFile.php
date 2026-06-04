<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractFile extends Model
{
    protected $fillable = [
        'contract_id',
        'file_name',
        'file_path',
        'file_type',
        'file_hash',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
