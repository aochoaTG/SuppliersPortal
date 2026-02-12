<?php

namespace App\Models;

use App\Enum\RequisitionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;
use App\Models\QuotationGroup;
use App\Models\Rfq;
use App\Models\QuotationSummary;


class Requisition extends Model
{
    use SoftDeletes;

    protected $table = 'requisitions';

    protected $fillable = [
        'company_id',
        'cost_center_id',
        'department_id',
        'folio',
        'requested_by',
        'required_date',
        'description',
        'status',

        // Pausa (esperando producto del catálogo)
        'pause_reason',
        'paused_by',
        'paused_at',
        'reactivated_by',
        'reactivated_at',

        // Cancelación
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',

        // Rechazo (Lo nuevo, ¡atención!)
        'rejection_reason',
        'rejected_by',
        'rejected_at',

        // ======= NUEVO: Validaciones del Departamento de Compras =======
        'validation_specs_clear',
        'validation_time_feasible',
        'validation_alternatives_evaluated',
        'validated_at',
        'validated_by',

        // Notas de Validación (Compras)
        'purchasing_validation_notes',

        // Auditoría
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'required_date'  => 'date',
        'paused_at'      => 'datetime',
        'reactivated_at' => 'datetime',
        'cancelled_at'   => 'datetime',
        'rejected_at'    => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',

        // ======= NUEVO: Casts para booleanos =======
        'validation_specs_clear' => 'boolean',
        'validation_time_feasible' => 'boolean',
        'validation_alternatives_evaluated' => 'boolean',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Compañía a la que pertenece la requisición.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Centro de costos al que se cargará la requisición.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * Departamento que genera la requisición.
     * NOTA: Campo opcional/legacy - Las nuevas requisiciones no requieren departamento.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class, 'requisition_id');
    }

    /**
     * Usuario que solicitó la requisición (requisitor).
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Usuario que creó el registro en el sistema.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó por última vez el registro.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Usuario que pausó la requisición.
     */
    public function pauser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paused_by');
    }

    /**
     * Usuario que reactivó la requisición.
     */
    public function reactivator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reactivated_by');
    }

    /**
     * Usuario que canceló la requisición.
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Usuario que rechazó la requisición.
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Usuario que validó la requisición (Compras).
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Grupos de cotización de esta requisición.
     */
    public function quotationGroups(): HasMany
    {
        return $this->hasMany(QuotationGroup::class);
    }

    /**
     * RFQs generadas para esta requisición.
     */
    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    /**
     * Resumen de cotización.
     */
    public function quotationSummary(): HasOne
    {
        return $this->hasOne(QuotationSummary::class);
    }

    // =========================================================================
    // MÉTODOS DE LÓGICA DE NEGOCIO
    // =========================================================================

    /**
     * Genera el siguiente folio consecutivo usando el año actual.
     * Formato: REQ-YYYY-###
     */
    public static function nextFolio(): string
    {
        $year = date('Y');
        $prefix = "REQ-{$year}-";
        $last = static::where('folio', 'like', $prefix . '%')
            ->orderBy('folio', 'desc')
            ->value('folio');

        $n = 0;
        if ($last && preg_match('/REQ-\d{4}-(\d+)/', $last, $m)) {
            $n = (int) $m[1];
        }
        $n++;

        return sprintf('%s%03d', $prefix, $n);
    }

    /**
     * Obtiene el label en español del status.
     */
    public function statusLabel(): string
    {
        $statusEnum = is_string($this->status)
            ? RequisitionStatus::tryFrom(strtoupper($this->status))
            : $this->status;

        return $statusEnum?->label() ?? $this->status;
    }

    /**
     * Verifica si la requisición está en borrador.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verifica si la requisición fue enviada a Compras.
     */
    public function isSubmitted(): bool
    {
        return $this->status === RequisitionStatus::PENDING->value;
    }

    /**
     * Verifica si la requisición está pausada.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Verifica si la requisición está cancelada.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verifica si la requisición está rechazada.
     */
    public function isRejected(): bool
    {
        return $this->status === RequisitionStatus::REJECTED;
    }

    /**
     * Verifica si la requisición puede ser editada.
     * Solo se puede editar si está en borrador o pausada.
     */
    public function canBeEdited(): bool
    {
        return $this->status === RequisitionStatus::DRAFT
            || $this->status === RequisitionStatus::PAUSED
            || $this->status === RequisitionStatus::REJECTED;
    }

    /**
     * Verifica si la requisición puede ser enviada a Compras.
     * Debe estar en borrador y tener al menos una partida.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === RequisitionStatus::DRAFT && $this->items()->count() > 0;
    }

    /**
     * Verifica si la requisición puede ser cancelada.
     * No se puede cancelar si ya fue aprobada o si ya se emitieron órdenes.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === RequisitionStatus::DRAFT
            || $this->status === RequisitionStatus::PAUSED
            || $this->status === RequisitionStatus::PENDING;
    }

    /**
     * Determina si la requisición puede ser rechazada.
     */
    public function canBeRejected(): bool
    {
        // Usamos la lógica de transición definida en el Enum
        return $this->status->canTransitionTo(RequisitionStatus::REJECTED);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === RequisitionStatus::DRAFT;
    }

    /**
     * Envía la requisición a Compras (cambia estado a PENDING).
     * Notifica al requisitor y al Departamento de Compras por email.
     */
    public function submitToCompras(): bool
    {
        Log::info('📝 Iniciando submitToCompras()', [
            'requisition_id' => $this->id,
            'folio' => $this->folio,
            'status_actual' => $this->status->value ?? $this->status,
        ]);

        if (!$this->canBeSubmitted()) {
            Log::warning('❌ No se puede enviar - canBeSubmitted() = false');
            return false;
        }

        // Cambiar estado a PENDING
        $this->status = RequisitionStatus::PENDING;

        // Limpiar campos de pausa si venía de ahí
        if ($this->isPaused()) {
            $this->pause_reason = null;
            $this->paused_by = null;
            $this->paused_at = null;
        }

        $saved = $this->save();

        Log::info('💾 Requisición guardada', [
            'saved' => $saved,
            'nuevo_status' => $this->status->value ?? $this->status,
        ]);

        if (!$saved) {
            return false;
        }

        // =====================================================
        // 📧 NOTIFICAR AL REQUISITOR
        // =====================================================
        if ($this->requester) {
            Log::info('👤 Preparando notificación para requisitor', [
                'requester_id' => $this->requester->id,
                'requester_name' => $this->requester->name,
                'requester_email' => $this->requester->email,
            ]);

            try {
                $this->requester->notify(new \App\Notifications\RequisitionSubmittedNotification($this));
                Log::info('✅ Notificación enviada al requisitor');
            } catch (\Exception $e) {
                Log::error('❌ Error al enviar notificación al requisitor', [
                    'requisition_id' => $this->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // =====================================================
        // 📧 NOTIFICAR AL DEPARTAMENTO DE COMPRAS
        // =====================================================
        try {
            // 👇 Usar 'buyer' que es el rol correcto
            $purchasingUsers = \App\Models\User::role('buyer')->get();

            if ($purchasingUsers->isEmpty()) {
                Log::warning('⚠️ No se encontraron usuarios con rol buyer', [
                    'rol_buscado' => 'buyer'
                ]);
            } else {
                Log::info('🛒 Notificando al Departamento de Compras', [
                    'count' => $purchasingUsers->count(),
                    'users' => $purchasingUsers->pluck('email')->toArray(),
                ]);

                foreach ($purchasingUsers as $purchaser) {
                    try {
                        $purchaser->notify(new \App\Notifications\NewRequisitionForPurchasingNotification($this));

                        Log::info('✅ Notificación enviada a comprador', [
                            'purchaser_name' => $purchaser->name,
                            'purchaser_email' => $purchaser->email,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('❌ Error al enviar notificación a comprador', [
                            'purchaser_email' => $purchaser->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ Error general al notificar a Compras', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $saved;
    }

    /**
     * Pausa la requisición (esperando producto del catálogo).
     */
    public function pause(string $reason, int $userId): bool
    {
        if ($this->isPaused()) {
            return false;
        }

        $this->status = 'paused';
        $this->pause_reason = $reason;
        $this->paused_by = $userId;
        $this->paused_at = now();

        return $this->save();
    }

    /**
     * Reactiva la requisición pausada.
     */
    public function reactivate(int $userId): bool
    {
        if (!$this->isPaused()) {
            return false;
        }

        $this->status = RequisitionStatus::DRAFT->value; // Regresa a borrador para revisión
        $this->reactivated_by = $userId;
        $this->reactivated_at = now();

        return $this->save();
    }

    /**
     * Cancelar la requisición.
     * Cambia el estado a CANCELLED y registra motivo y usuario.
     */
    public function cancel(string $reason, int $userId): void
    {
        if (!$this->canBeCancelled()) {
            throw new \RuntimeException(
                'No se puede cancelar una requisición en estado "' . $this->status->label() . '".'
            );
        }

        $this->status = RequisitionStatus::CANCELLED;
        $this->cancellation_reason = $reason;
        $this->cancelled_by = $userId;
        $this->cancelled_at = now();
        $this->updated_by = $userId;

        $this->save();
    }

    /**
     * Ejecuta el rechazo de la requisición.
     */
    public function reject(string $reason, int $userId): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        return $this->update([
            'status' => RequisitionStatus::REJECTED,
            'rejection_reason' => $reason,
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'updated_by' => $userId,
        ]);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Requisiciones en borrador.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', RequisitionStatus::DRAFT->value);
    }

    /**
     * Requisiciones enviadas a Compras (cualquier estado posterior a draft).
     */
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', [RequisitionStatus::PENDING->value, RequisitionStatus::PAUSED->value]);
    }

    /**
     * Requisiciones en proceso de cotización.
     */
    public function scopeInQuotation($query)
    {
        return $query->where('status', RequisitionStatus::IN_QUOTATION->value);
    }

    /**
     * Requisiciones cotizadas (esperando aprobación de cotización).
     */
    public function scopeQuoted($query)
    {
        return $query->where('status', RequisitionStatus::QUOTED->value);
    }

    /**
     * Requisiciones pendientes de ajuste presupuestal.
     */
    public function scopePendingBudgetAdjustment($query)
    {
        return $query->where('status', RequisitionStatus::PENDING_BUDGET_ADJUSTMENT->value);
    }

    /**
     * Requisiciones con cotización aprobada.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', RequisitionStatus::APPROVED->value);
    }

    /**
     * Requisiciones con órdenes emitidas.
     */
    public function scopeOrdersIssued($query)
    {
        return $query->where('status', RequisitionStatus::COMPLETED->value);
    }

    /**
     * Requisiciones pausadas.
     */
    public function scopePaused($query)
    {
        return $query->where('status', RequisitionStatus::PAUSED->value);
    }

    /**
     * Requisiciones canceladas.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', RequisitionStatus::CANCELLED->value);
    }

    /**
     * Requisiciones rechazadas por Compras.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', RequisitionStatus::REJECTED->value);
    }

    /**
     * Requisiciones de un centro de costos específico.
     */
    public function scopeByCostCenter($query, int $costCenterId)
    {
        return $query->where('cost_center_id', $costCenterId);
    }

    /**
     * Requisiciones del usuario autenticado (como requisitor).
     */
    public function scopeMyRequisitions($query, int $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Requisiciones de un año específico (basado en created_at).
     */
    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('created_at', $year);
    }


    /**
     * Scope para el dashboard de proveedores (ver solo lo que les compete).
     */
    public function scopeForSupplierPortal($query)
    {
        // Los proveedores no ven borradores, solo lo que ya está en proceso o terminado
        return $query->whereNotIn('status', [RequisitionStatus::DRAFT->value]);
    }

    // =========================================================================
    // ACCESORES / MUTADORES
    // =========================================================================

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Manejar el default 'draft' que viene de la migración si no está en mayúsculas
                $val = strtoupper($value);
                return RequisitionStatus::tryFrom($val) ?? RequisitionStatus::DRAFT;
            },
            set: fn(RequisitionStatus|string $value) => is_string($value) ? strtoupper($value) : $value->value,
        );
    }
}
