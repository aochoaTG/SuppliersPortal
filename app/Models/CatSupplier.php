<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CatSupplier extends Model
{
    use SoftDeletes;

    protected $table = 'cat_suppliers';

    protected $fillable = [
        'source_system',
        'source_company',
        'source_external_id',
        'name',
        'rfc',
        'postal_code',
        'city',
        'state',
        'email',
        'website',
        'bank',
        'account_number',
        'clabe',
        'payment_method',
        'currency',
        'category',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'bool',
    ];

    // Normalizaciones básicas
    public function setRfcAttribute($value): void
    {
        $this->attributes['rfc'] = is_string($value)
            ? strtoupper(trim(preg_replace('/\s+/', '', $value)))
            : $value;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = is_string($value)
            ? strtolower(trim($value))
            : $value;
    }

    public function setClabeAttribute($value): void
    {
        $this->attributes['clabe'] = is_string($value)
            ? preg_replace('/\D+/', '', $value)
            : $value;
    }

    // Scopes útiles
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;

        $term = trim($term);
        return $q->where(function (Builder $u) use ($term) {
            $u->where('name', 'like', "%{$term}%")
              ->orWhere('rfc', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('source_external_id', 'like', "%{$term}%");
        });
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->rfc ?: "[ID {$this->id}]";
    }
}
