<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinancialProvision extends Model
{
    use HasFactory;

    public const STATUS_PENDING_INVOICE = 'PENDING_INVOICE';
    public const STATUS_INVOICED = 'INVOICED';
    public const STATUS_DISCREPANCY_REVIEW = 'DISCREPANCY_REVIEW';
    public const STATUS_CLOSED_WITH_ADJUSTMENT = 'CLOSED_WITH_ADJUSTMENT';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'reception_id',
        'receivable_type',
        'receivable_id',
        'supplier_id',
        'supplier_invoice_id',
        'cost_center_id',
        'application_month',
        'provision_amount',
        'invoice_amount',
        'difference_amount',
        'currency',
        'status',
        'provisioned_at',
        'invoiced_at',
        'closed_at',
    ];

    protected $casts = [
        'provision_amount' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'provisioned_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function reception(): BelongsTo
    {
        return $this->belongsTo(Reception::class);
    }

    public function receivable(): MorphTo
    {
        return $this->morphTo();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(FinancialProvisionAdjustment::class);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_INVOICE => 'Pendiente de Factura',
            self::STATUS_INVOICED => 'Facturada',
            self::STATUS_DISCREPANCY_REVIEW => 'En Revisión por Discrepancia',
            self::STATUS_CLOSED_WITH_ADJUSTMENT => 'Cerrada con Ajuste',
            self::STATUS_CANCELLED => 'Cancelada',
            default => 'Desconocido',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_INVOICE => 'warning',
            self::STATUS_INVOICED => 'success',
            self::STATUS_DISCREPANCY_REVIEW => 'danger',
            self::STATUS_CLOSED_WITH_ADJUSTMENT => 'info',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }
}
