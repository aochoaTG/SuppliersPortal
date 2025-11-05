<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $table = 'requisition_items';

    protected $fillable = [
        'requisition_id',
        'line_number',
        'item_category',
        'product_code',
        'suggested_vendor_id',
        'notes',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'tax_id',
        'line_total',
        'tax_rate',
        'tax_amount',
        'total_with_tax',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'line_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_with_tax' => 'decimal:2',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'suggested_vendor_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function calcLineTotals(): void
    {
        $subtotal = (float) $this->quantity * (float) $this->unit_price;
        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);
        $this->line_total = round($subtotal, 2);
        $this->tax_amount = $taxAmount;
        $this->total_with_tax = round($subtotal + $taxAmount, 2);
    }

}
