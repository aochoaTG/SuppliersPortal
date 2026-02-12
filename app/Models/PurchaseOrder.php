<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folio',
        'requisition_id',
        'supplier_id',
        'quotation_summary_id',
        'subtotal',
        'iva_amount',
        'total',
        'currency',
        'payment_terms',
        'estimated_delivery_days',
        'status',
        'created_by'
    ];

    // Relación con el creador (el Superadmin que autorizó)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relación con el proveedor a quien le compramos
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relación con la requisición origen
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    // El corazón de la OC: sus partidas
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
