<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CostCenter
 *
 * Representa un centro de costo (estación, área, proyecto, etc.).
 * Pertenece a una Category (Fase 1).
 */
class CostCenter extends Model
{
    protected $table = 'cost_centers';

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'company_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación con la categoría (ADMINISTRACION, ENPROYECTO, etc.).
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
