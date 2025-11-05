<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetMovement extends Model
{
    protected $table = 'budget_movements';

    protected $fillable = [
        'fiscal_year',
        'cost_center_id',
        'annual_budget_id',
        'requisition_id',
        'movement_type',
        'amount',
        'currency_code',
        'created_by',
        'source',
        'note',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(AnnualBudget::class, 'annual_budget_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
