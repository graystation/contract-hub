<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    protected $fillable = ['title', 'type', 'body'];

    /**
     * Return templates applicable to the given contract type.
     * Templates with type = null match any type.
     */
    public static function forType(?string $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::where(function ($q) use ($type) {
            $q->whereNull('type');
            if ($type) {
                $q->orWhere('type', $type);
            }
        })->orderBy('title')->get();
    }
}
