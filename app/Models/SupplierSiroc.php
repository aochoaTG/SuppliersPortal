<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierSiroc extends Model
{
    use HasFactory;

    protected $table = 'supplier_sirocs';

    /**
     * Campos que se pueden asignar en masa
     */
    protected $fillable = [
        'supplier_id',
        'siroc_number',
        'contract_number',
        'work_name',
        'work_location',
        'start_date',
        'end_date',
        'siroc_file',
        'status',
        'observations',
    ];

    /**
     * Casts para fechas y enumeraciones
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Relación: un registro SIROC pertenece a un proveedor
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
