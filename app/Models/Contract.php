<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'project_id',
        'contract_number',
        'contract_type',
        'signed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'signed_at' => 'date',
    ];

    public const STATUSES = ['draft', 'sent', 'signed', 'cancelled'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
