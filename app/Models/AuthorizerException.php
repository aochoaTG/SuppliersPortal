<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorizerException extends Model
{
    protected $fillable = [
        'user_id',
        'approval_limit',
        'reason',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'approval_limit' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($subQuery) {
                $subQuery->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($subQuery) {
                $subQuery->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }
}
