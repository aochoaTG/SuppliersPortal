<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalLevel extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'approval_levels';

    /**
     * Los atributos que son asignables masivamente.
     * Metadata incluida para reportes y auditoría.
     */
    protected $fillable = [
        'level_number',
        'label',
        'min_amount',
        'max_amount',
        'color_tag',
        'description',
    ];

    /**
     * Casting de tipos para asegurar que SQL Server no nos devuelva strings
     */
    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'level_number' => 'integer',
    ];
}
