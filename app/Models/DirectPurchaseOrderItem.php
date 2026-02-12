<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectPurchaseOrderItem extends Model
{
    protected $table = 'odc_direct_purchase_order_items';

    protected $fillable = [
        'direct_purchase_order_id',
        'description',
        'quantity',
        'unit_price',
        'iva_rate',        // ← NUEVO CAMPO
        'subtotal',
        'iva_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'iva_rate' => 'decimal:2',    // ← NUEVO CAST
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot del modelo - Auto-calcular montos
     */
    protected static function booted(): void
    {
        static::saving(function (DirectPurchaseOrderItem $item) {
            $item->calculateAmounts();
        });

        // Actualizar totales de la OCD cuando se crea/actualiza/elimina un item
        static::saved(function (DirectPurchaseOrderItem $item) {
            $item->directPurchaseOrder->updateTotals();
        });

        static::deleted(function (DirectPurchaseOrderItem $item) {
            $item->directPurchaseOrder->updateTotals();
        });
    }

    /**
     * Calcular montos basados en cantidad, precio unitario y tasa de IVA
     */
    public function calculateAmounts(): void
    {
        // Asegurar que iva_rate tenga valor por defecto
        if ($this->iva_rate === null) {
            $this->iva_rate = 16.00;
        }

        $this->subtotal = round($this->quantity * $this->unit_price, 2);

        // Calcular IVA según la tasa especificada (0%, 8% o 16%)
        $this->iva_amount = round($this->subtotal * ($this->iva_rate / 100), 2);

        $this->total = round($this->subtotal + $this->iva_amount, 2);
    }

    /**
     * Actualizar montos del item
     */
    public function updateAmounts(): void
    {
        $this->calculateAmounts();
        $this->save();
    }

    /**
     * Relación con DirectPurchaseOrder
     */
    public function directPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(DirectPurchaseOrder::class, 'direct_purchase_order_id');
    }

    /**
     * Obtener etiqueta de la tasa de IVA
     */
    public function getIvaRateLabel(): string
    {
        $rate = (float) $this->iva_rate;

        return match ($rate) {
            0.00 => 'Exento (0%)',
            8.00 => 'Fronteriza (8%)',
            16.00 => 'General (16%)',
            default => $rate . '%',
        };
    }

    /**
     * Scope para items con IVA general
     */
    public function scopeWithGeneralIva($query)
    {
        return $query->where('iva_rate', 16.00);
    }

    /**
     * Scope para items con IVA fronterizo
     */
    public function scopeWithBorderIva($query)
    {
        return $query->where('iva_rate', 8.00);
    }

    /**
     * Scope para items exentos
     */
    public function scopeExempt($query)
    {
        return $query->where('iva_rate', 0.00);
    }
}
