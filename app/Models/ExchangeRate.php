<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
        'rate'       => 'decimal:4',
    ];

    public static function current(string $from, string $to): ?self
    {
        return static::where('currency_from', $from)
            ->where('currency_to', $to)
            ->first();
    }
}
