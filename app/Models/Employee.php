<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'archivo_origen',
        'employee_number',
        'full_name',
        'department',
        'job_title',
        'hire_date',
        'is_active',
        'termination_date',
        'rehire_eligible',
        'termination_reason',
        'team',
        'seniority',
        'rfc',
        'imss',
        'curp',
        'gender',
        'phone',
        'address',
        'email',
        'education',
        'company',
        'responsible',
        'leader',
        'vacation_balance',
        'savings_fund',
        'daily_salary',
        'severance_bonus',
        'indemnization',
        'seniority_premium',
    ];

    protected $casts = [
        'hire_date'         => 'date',
        'termination_date'  => 'date',
        'vacation_balance'  => 'decimal:4',
        'savings_fund'      => 'decimal:4',
        'daily_salary'      => 'decimal:4',
        'severance_bonus'   => 'decimal:4',
        'indemnization'     => 'decimal:4',
        'seniority_premium' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
