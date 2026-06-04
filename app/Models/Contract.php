<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;
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

    public function files(): HasMany
    {
        return $this->hasMany(ContractFile::class)->orderByDesc('created_at');
    }
}
