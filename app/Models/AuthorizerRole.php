<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorizerRole extends Model
{
    protected $fillable = [
        'name',
        'approval_limit',
        'matrix_sheet',
        'matrix_reference',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'approval_limit' => 'decimal:2',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(UserAuthorizerRole::class);
    }

    public function quotationSummaries(): HasMany
    {
        return $this->hasMany(QuotationSummary::class);
    }
}
