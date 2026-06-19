<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'title',
        'type',
        'description',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    public const STATUSES = ['draft', 'active', 'completed', 'cancelled'];
    public const TYPES = ['advertisement', 'consulting', 'other'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->orderByDesc('created_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderByDesc('created_at');
    }
}
