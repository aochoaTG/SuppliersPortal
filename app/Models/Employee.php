<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'archivo_origen',
        'employee_number',
        'first_name',
        'last_name',
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
        'photo',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'vacation_balance' => 'decimal:4',
        'savings_fund' => 'decimal:4',
        'daily_salary' => 'decimal:4',
        'severance_bonus' => 'decimal:4',
        'indemnization' => 'decimal:4',
        'seniority_premium' => 'decimal:4',
    ];

    /**
     * Nombre completo calculado: "Nombre(s) Apellido(s)".
     * Útil para mostrar en vistas sin tener que concatenar manualmente.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
