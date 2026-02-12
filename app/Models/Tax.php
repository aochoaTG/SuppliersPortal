<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $table = 'taxes';

    protected $fillable = [
        'name',
        'rate_percent',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_percent' => 'decimal:2',
    ];

    /**
     * Scope para obtener solo impuestos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener solo impuestos inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Verificar si el impuesto está activo
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}