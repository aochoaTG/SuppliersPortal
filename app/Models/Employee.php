<?php

// app/Models/Employee.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id','company','employee_number','full_name','department','job_title',
        'hire_date','is_active','termination_date','rehire_eligible','termination_reason',
        'team','seniority','rfc','imss','curp','gender','vacation_balance','phone','address',
    ];

    protected $casts = [
        'hire_date'         => 'date',
        'termination_date'  => 'date',
        'is_active'         => 'boolean',
        'rehire_eligible'   => 'boolean',
        'vacation_balance'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
