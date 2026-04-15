<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeEvent extends Model
{
    protected $fillable = [
        'employee_id',
        'campo',
        'evento',
        'valor_anterior',
        'valor_nuevo',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
