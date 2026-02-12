<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatEfos69b extends Model
{
    // Tabla explícita (no plural estándar)
    protected $table = 'sat_efos_69b';

    // Si tu PK no es "id" o no es auto-increment, aquí se ajusta.
    protected $primaryKey = 'id';
    public $incrementing = true;

    // Campos asignables en masa
    protected $fillable = [
        'number',
        'rfc',
        'company_name',
        'situation',
        'sat_presumption_notice_date',
        'sat_presumed_publication_date',
        'dof_presumption_notice_date',
        'dof_presumed_pub_date',
        'sat_definitive_publication_date',
        'dof_definitive_publication_date',
        'updated_at',
        'created_at',
    ];

    // Casts de fechas
    protected $casts = [
        'sat_presumed_publication_date'   => 'date',
        'dof_presumed_pub_date'           => 'date',
        'sat_definitive_publication_date' => 'date',
        'dof_definitive_publication_date' => 'date',
        'created_at'                      => 'datetime',
        'updated_at'                      => 'datetime',
    ];

    // Deshabilitamos timestamps automáticos porque controlamos created_at/updated_at manualmente arriba
    public $timestamps = false;
}
