<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnnualBudget extends Model
{
    use HasFactory;

    protected $table = 'annual_budgets';

    protected $fillable = [
        'company_id',
        'cost_center_id',
        'fiscal_year',
        'amount_assigned',
        'amount_committed',
        'amount_consumed',
        'amount_released',
        'amount_adjusted',
        'amount_available',
        'is_closed',
        'notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'amount_assigned' => 'decimal:2',
        'amount_committed' => 'decimal:2',
        'amount_consumed' => 'decimal:2',
        'amount_released' => 'decimal:2',
        'amount_adjusted' => 'decimal:2',
        'amount_available' => 'decimal:2',
        'is_closed' => 'boolean',
    ];

    // 🔗 Relaciones
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(\App\Models\CostCenter::class, 'cost_center_id');
    }

    public function movements()
    {
        return $this->hasMany(\App\Models\BudgetMovement::class, 'annual_budget_id');
    }

    // (Opcional) Scopes útiles
    public function scopeYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeForCostCenter($query, int $ccId)
    {
        return $query->where('cost_center_id', $ccId);
    }
}
