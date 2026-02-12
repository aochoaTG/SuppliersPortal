<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuotationGroupItem extends Pivot
{
    protected $table = 'quotation_group_items';

    protected $fillable = [
        'quotation_group_id',
        'requisition_item_id',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Grupo al que pertenece.
     */
    public function quotationGroup()
    {
        return $this->belongsTo(QuotationGroup::class);
    }

    /**
     * Partida de la requisición.
     */
    public function requisitionItem()
    {
        return $this->belongsTo(RequisitionItem::class);
    }
}
