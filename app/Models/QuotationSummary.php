<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationSummary extends Model
{
    use SoftDeletes;

    protected $table = 'quotation_summaries';

    protected $fillable = [
        'requisition_id',
        'subtotal',
        'iva_amount',
        'total',
        'approval_level_id',
        'selected_supplier_id',
        'approval_status',
        'justification',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva_rate' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'total_suppliers_selected' => 'integer',
        'requires_multiple_pos' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Requisición a la que pertenece este resumen.
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Usuario que aprobó.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Usuario que rechazó.
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function selectedSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'selected_supplier_id');
    }

    // =========================================================================
    // MÉTODOS DE LÓGICA DE NEGOCIO
    // =========================================================================

    /**
     * Calcula el resumen basado en cotizaciones aprobadas.
     */
    public function calculateFromApprovedQuotations(): void
    {
        // Obtener todas las cotizaciones aprobadas de la requisición
        $approvedResponses = RfqResponse::whereHas('rfq', function ($query) {
            $query->where('requisition_id', $this->requisition_id);
        })
            ->where('status', 'approved')
            ->get();

        // Calcular subtotal
        $subtotal = $approvedResponses->sum('subtotal');

        // Calcular IVA
        $ivaAmount = $subtotal * ($this->iva_rate / 100);

        // Calcular total
        $total = $subtotal + $ivaAmount;

        // Contar proveedores únicos
        $suppliersCount = $approvedResponses->pluck('rfq.supplier_id')->unique()->count();

        // Determinar si requiere múltiples OCs
        $requiresMultiplePOs = $suppliersCount > 1;

        // Determinar nivel de aprobación según monto
        $approvalLevel = $this->determineApprovalLevel($total);

        // Actualizar
        $this->update([
            'subtotal' => $subtotal,
            'iva_amount' => $ivaAmount,
            'total' => $total,
            'approval_level_id' => $approvalLevel,
            'selected_supplier_id' => $approvedResponses->first()->rfq->supplier_id,
        ]);
    }

    /**
     * Determina el nivel de aprobación según el monto.
     */
    protected function determineApprovalLevel(float $total): string
    {
        if ($total < 10000) {
            return 'buyer';
        } elseif ($total < 50000) {
            return 'manager';
        } elseif ($total < 100000) {
            return 'director';
        } else {
            return 'ceo';
        }
    }

    /**
     * Aprobar resumen.
     */
    public function approve(int $userId, ?string $notes = null): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'justification' => $notes,
        ]);
    }

    /**
     * Rechazar resumen.
     */
    public function reject(int $userId, string $reason): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Verifica si está aprobado.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Verifica si está rechazado.
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    /**
     * Verifica si está pendiente.
     */
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Resúmenes aprobados.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Resúmenes rechazados.
     */
    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Resúmenes pendientes.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Por nivel de aprobación.
     */
    public function scopeByApprovalLevel($query, string $level)
    {
        return $query->where('approval_level_id', $level);
    }

    // =========================================================================
    // ACCESORES
    // =========================================================================

    /**
     * Subtotal formateado.
     */
    public function getSubtotalFormattedAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * IVA formateado.
     */
    public function getIvaAmountFormattedAttribute(): string
    {
        return '$' . number_format($this->iva_amount, 2);
    }

    /**
     * Total formateado.
     */
    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * Label del nivel de aprobación.
     */
    public function getApprovalLevelLabelAttribute(): string
    {
        return match ($this->approval_level_id) {
            1 => 'Comprador',
            2 => 'Gerente',
            3 => 'Director',
            4 => 'Director General',
            default => 'N/A',
        };
    }

    /**
     * Label del estado de aprobación.
     */
    public function getApprovalStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            default => 'N/A',
        };
    }
}
