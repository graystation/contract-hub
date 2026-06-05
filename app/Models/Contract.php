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
        'body',
        'sign_token',
        'sign_token_expires_at',
        'signer_name',
        'signer_email',
        'signer_ip_address',
        'signer_user_agent',
    ];

    protected $casts = [
        'signed_at'             => 'datetime',
        'sign_token_expires_at' => 'datetime',
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
