<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'requisition_item_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'iva_amount',
        'total'
    ];

    // Relación con su cabecera
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Relación con el item original de la requisición
    public function requisitionItem()
    {
        return $this->belongsTo(RequisitionItem::class);
    }
}
