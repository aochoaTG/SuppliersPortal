<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialProvisionAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_provision_id',
        'supplier_invoice_id',
        'authorized_by',
        'amount',
        'reason',
        'notes',
        'authorized_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'authorized_at' => 'datetime',
    ];

    public function financialProvision(): BelongsTo
    {
        return $this->belongsTo(FinancialProvision::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
