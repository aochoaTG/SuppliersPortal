<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviated',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Uppercase y trimming para el abreviado
    public function setAbbreviatedAttribute($value): void
    {
        $this->attributes['abbreviated'] = Str::upper(trim($value));
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
