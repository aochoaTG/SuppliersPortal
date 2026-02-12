<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use \App\Models\SupplierDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory; // 1. Importar el trait
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'user_id',
        'company_name',
        'rfc',
        'address',
        'phone_number',
        'email',
        'contact_person',
        'contact_phone',
        'supplier_type',
        'tax_regime',
        'bank_name',
        'account_number',
        'clabe',
        'currency',
        'status',
        'swift_bic',
        'iban',
        'bank_address',
        'aba_routing',
        'us_bank_name',
        // Nuevos campos REPSE
        'provides_specialized_services',
        'repse_registration_number',
        'repse_expiry_date',
        'specialized_services_types',
        'economic_activity',
    ];

    protected $casts = [
        'provides_specialized_services' => 'boolean',
        'repse_expiry_date' => 'date',
        'specialized_services_types' => 'array', // Para almacenar JSON
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SupplierDocument::class);
    }

    // Helper para estado de “completitud” (simple)
    public function missingRequiredDocuments(): array
    {
        $present = $this->documents()
            ->whereIn('doc_type', SupplierDocument::REQUIRED_TYPES)
            ->pluck('doc_type')
            ->unique()
            ->all();

        return array_values(array_diff(SupplierDocument::REQUIRED_TYPES, $present));
    }

    // Nuevos helpers para REPSE
    public function requiresRepseRegistration(): bool
    {
        return $this->provides_specialized_services === true;
    }

    public function hasValidRepseRegistration(): bool
    {
        if (!$this->requiresRepseRegistration()) {
            return true; // No requiere REPSE
        }

        return !empty($this->repse_registration_number)
            && $this->repse_expiry_date
            && $this->repse_expiry_date->isFuture();
    }

    public function repseExpiresIn(): ?int
    {
        if (!$this->repse_expiry_date) {
            return null;
        }

        return $this->repse_expiry_date->diffInDays(now(), false);
    }

    public function sirocs(): HasMany
    {
        return $this->hasMany(SupplierSiroc::class);
    }

    /**
     * Scope: excluir proveedores cuyo RFC esté en sat_efos_69b
     * con situación Definitivo o Presunto.
     */
    public function scopeNotEfos69b(\Illuminate\Database\Eloquent\Builder $q): \Illuminate\Database\Eloquent\Builder
    {
        return $q->whereNotExists(function ($sub) {
            $sub->from('sat_efos_69b as e')
                ->whereColumn('e.rfc', 'suppliers.rfc')
                ->whereIn('e.situation', ['Definitivo', 'Presunto']);
        });
    }

    /**
     * Scope: búsqueda por nombre o RFC.
     */
    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $q, ?string $term): \Illuminate\Database\Eloquent\Builder
    {
        if (!filled($term))
            return $q;

        return $q->where(function ($qq) use ($term) {
            $qq->where('company_name', 'like', "%{$term}%")   // 👈 aquí
                ->orWhere('rfc', 'like', "%{$term}%");
        });
    }

    /**
     * (Opcional) Scope de activos si manejas un flag.
     */
    public function scopeActive(Builder $q): Builder
    {
        return $q->when($this->getTableColumnsCached()['is_active'] ?? false, function ($qq) {
            $qq->where('is_active', 1);
        });
    }

    /**
     * Accessor: devuelve la situación EFOS actual (o null si no está en lista).
     * Útil para mostrar badges/advertencias en UI.
     */
    public function getEfosStatusAttribute(): ?string
    {
        return DB::table('sat_efos_69b as e')
            ->where('e.rfc', $this->rfc)
            ->orderByRaw("
                CASE
                  WHEN e.situation = 'Definitivo' THEN 1
                  WHEN e.situation = 'Presunto' THEN 2
                  ELSE 3
                END
            ")
            ->value('situation');
    }

    /**
     * Accessor booleano: true si es EFOS (Definitivo/Presunto).
     */
    public function getIsEfosAttribute(): bool
    {
        $status = $this->efos_status; // usa accessor anterior
        return in_array($status, ['Definitivo', 'Presunto'], true);
    }

    /**
     * (Opcional avanzado) cacheo simple de columnas de la tabla para scopes condicionales.
     */
    protected function getTableColumnsCached(): array
    {
        static $cache = null;
        if ($cache !== null)
            return $cache;

        $connection = $this->getConnection();
        $table = $this->getTable();

        // SQL Server: consulta INFORMATION_SCHEMA
        $cols = $connection->table('INFORMATION_SCHEMA.COLUMNS')
            ->select('COLUMN_NAME')
            ->where('TABLE_NAME', $table)
            ->pluck('COLUMN_NAME')
            ->mapWithKeys(fn($c) => [$c => true])
            ->all();

        return $cache = $cols;
    }

    // En app/Models/Supplier.php

    /**
     * RFQs enviadas a este proveedor.
     */
    public function rfqs()
    {
        return $this->belongsToMany(Rfq::class, 'rfq_suppliers')
            ->using(RfqSupplier::class) // 👈 Especificar modelo pivot personalizado
            ->withPivot([
                'invited_at',
                'responded_at',
                'quotation_pdf_path', // 👈 Agregar campo
                'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Cotizaciones respondidas por este proveedor.
     */
    public function rfqResponses(): HasManyThrough
    {
        return $this->hasManyThrough(
            RfqResponse::class,
            Rfq::class,
            'supplier_id',    // Foreign key en rfqs
            'rfq_id',         // Foreign key en rfq_responses
            'id',             // Local key en suppliers
            'id'              // Local key en rfqs
        );
    }
}
