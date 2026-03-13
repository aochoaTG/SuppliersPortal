<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReceptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reception_id',
        'receivable_item_type',
        'receivable_item_id',
        'quantity_received',
        'quantity_rejected',
        'rejection_reason',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'quantity_rejected' => 'decimal:3',
    ];

    /**
     * =========================================
     * RELACIONES
     * =========================================
     */

    public function reception(): BelongsTo
    {
        return $this->belongsTo(Reception::class);
    }

    /**
     * El ítem de orden que se recibió en esta línea.
     * Puede ser PurchaseOrderItem o DirectPurchaseOrderItem.
     */
    public function receivableItem(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * =========================================
     * ACCESORES Y LÓGICA
     * =========================================
     */

    /**
     * Cantidad neta aceptada: lo recibido menos lo rechazado.
     */
    public function getQuantityAcceptedAttribute(): float
    {
        return max(0, (float) $this->quantity_received - (float) $this->quantity_rejected);
    }

    public function hasRejections(): bool
    {
        return (float) $this->quantity_rejected > 0;
    }

    public function isFullyRejected(): bool
    {
        return (float) $this->quantity_rejected >= (float) $this->quantity_received;
    }
}
