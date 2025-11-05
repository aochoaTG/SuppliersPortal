<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'code',
        'name',
        'legal_name',
        'rfc',
        'locale',
        'timezone',
        'currency_code',
        'phone',
        'email',
        'domain',
        'website',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function stations()
    {
        return $this->hasMany(Station::class);
    }

    /**
     * Scope: empresas visibles para un usuario (todas en las que está dado de alta).
     */
    public function scopeVisibleTo($query, User $user)
    {
        // Si manejas roles con Spatie y quieres superadmin libre, descomenta:
        // if ($user->hasRole('super-admin')) return $query;

        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }
}
