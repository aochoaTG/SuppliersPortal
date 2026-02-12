<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $table = 'stations';

    protected $fillable = [
        'company_id',
        'station_name',
        'country',
        'state',
        'municipality',
        'address',
        'expedition_place',
        'server_ip',
        'database_name',
        'cre_permit',
        'email',
        'source_system',
        'external_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
