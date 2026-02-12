<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationGroup extends Model
{
    use SoftDeletes;

    protected $table = 'quotation_groups';

    protected $fillable = [
        'requisition_id',
        'name',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requisition_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Requisición a la que pertenece este grupo.
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Partidas que pertenecen a este grupo (relación muchos a muchos).
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            RequisitionItem::class,
            'quotation_group_items',
            'quotation_group_id',
            'requisition_item_id'
        )
            ->withPivot(['notes', 'sort_order'])
            ->withTimestamps()
            ->orderBy('quotation_group_items.sort_order');
    }

    /**
     * RFQs generadas para este grupo.
     */
    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    /**
     * Usuario que creó el grupo.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó el grupo.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =========================================================================
    // MÉTODOS DE LÓGICA DE NEGOCIO
    // =========================================================================

    /**
     * Calcula el subtotal del grupo (suma de todas las partidas).
     */
    public function calculateSubtotal(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    /**
     * Obtiene las categorías únicas en este grupo.
     */
    public function getCategories(): array
    {
        return $this->items->pluck('product.category.name')->unique()->toArray();
    }

    /**
     * Verifica si el grupo tiene categorías mixtas.
     */
    public function hasMixedCategories(): bool
    {
        return count($this->getCategories()) > 1;
    }

    /**
     * Obtiene el conteo de partidas en el grupo.
     */
    public function getItemsCount(): int
    {
        return $this->items()->count();
    }

    /**
     * Verifica si el grupo está vacío.
     */
    public function isEmpty(): bool
    {
        return $this->getItemsCount() === 0;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Grupos de una requisición específica.
     */
    public function scopeByRequisition($query, int $requisitionId)
    {
        return $query->where('requisition_id', $requisitionId);
    }

    /**
     * Grupos con partidas (no vacíos).
     */
    public function scopeWithItems($query)
    {
        return $query->has('items');
    }

    /**
     * Grupos vacíos.
     */
    public function scopeEmpty($query)
    {
        return $query->doesntHave('items');
    }

    // =========================================================================
    // ACCESORES
    // =========================================================================

    /**
     * Obtiene el subtotal formateado.
     */
    public function getSubtotalFormattedAttribute(): string
    {
        return '$' . number_format($this->calculateSubtotal(), 2);
    }
}
