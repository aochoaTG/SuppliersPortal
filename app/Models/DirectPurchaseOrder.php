<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectPurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'odc_direct_purchase_orders';

    protected $fillable = [
        'folio',
        'supplier_id',
        'cost_center_id',
        'application_month',
        'justification',
        'subtotal',
        'iva_amount',
        'total',
        'currency',
        'payment_terms',
        'estimated_delivery_days',
        'required_approval_level',
        'assigned_approver_id',
        'status',
        'pdf_path',
        'reception_notes',
        'created_by',
        'approved_by',
        'rejected_by',
        'returned_by',
        'received_by',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'returned_at',
        'issued_at',
        'received_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'returned_at' => 'datetime',
        'issued_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * =========================================
     * RELACIONES
     * =========================================
     */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_approver_id');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DirectPurchaseOrderItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DirectPurchaseOrderApproval::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DirectPurchaseOrderDocument::class);
    }

    public function budgetCommitment(): HasOne
    {
        return $this->hasOne(BudgetCommitment::class);
    }

    /**
     * =========================================
     * MÉTODOS DE NEGOCIO
     * =========================================
     */

    /**
     * Genera el siguiente folio disponible para OCD
     */
    public static function generateNextFolio(): string
    {
        $year = now()->year;
        $lastOrder = self::whereYear('created_at', $year)
            ->whereNotNull('folio')
            ->orderBy('folio', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastOrder && preg_match('/OCD-\d{4}-(\d{4})/', $lastOrder->folio, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        return sprintf('OCD-%d-%04d', $year, $nextNumber);
    }

    /**
     * Determina el nivel de aprobación requerido según el monto total
     */
    public function determineRequiredApprovalLevel(): int
    {
        $total = $this->total;
        $level = ApprovalLevel::where('min_amount', '<=', $total)
            ->where(function ($query) use ($total) {
                $query->where('max_amount', '>=', $total)
                    ->orWhereNull('max_amount');
            })
            ->first();

        return $level ? $level->level_number : 1;
    }

    /**
     * Envía la OCD a aprobación
     */
    public function submit(): bool
    {
        if (!$this->canBeSubmitted()) {
            return false;
        }

        return $this->update([
            'folio' => $this->folio ?? self::generateNextFolio(),
            'status' => 'PENDING_APPROVAL',
            'required_approval_level' => $this->determineRequiredApprovalLevel(),
            'submitted_at' => now(),
        ]);
    }

    /**
     * =========================================
     * MÉTODOS DE ESTADO (STATUS CHECKS)
     * =========================================
     */

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'PENDING_APPROVAL';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    public function isReturned(): bool
    {
        return $this->status === 'RETURNED';
    }

    public function isIssued(): bool
    {
        return $this->status === 'ISSUED';
    }

    public function isReceived(): bool
    {
        return $this->status === 'RECEIVED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * =========================================
     * VALIDACIONES DE PERMISOS Y ACCIONES
     * =========================================
     */

    public function isCreatedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function isApproverFor(User $user): bool
    {
        return $this->assigned_approver_id === $user->id;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['DRAFT', 'RETURNED']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'DRAFT' && $this->items()->count() > 0;
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'PENDING_APPROVAL';
    }

    public function canBeReceived(): bool
    {
        return $this->status === 'ISSUED';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'DRAFT' => 'secondary',
            'PENDING_APPROVAL' => 'warning',
            'APPROVED' => 'success',
            'REJECTED' => 'danger',
            'RETURNED' => 'info',
            'ISSUED' => 'primary',
            'RECEIVED' => 'success',
            'CANCELLED' => 'dark',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'DRAFT' => 'Borrador',
            'PENDING_APPROVAL' => 'Pendiente de Aprobación',
            'APPROVED' => 'Aprobada',
            'REJECTED' => 'Rechazada',
            'RETURNED' => 'Devuelta para Corrección',
            'ISSUED' => 'Emitida',
            'RECEIVED' => 'Recibida',
            'CANCELLED' => 'Cancelada',
            default => 'Desconocido'
        };
    }

    /**
     * Calcula el total de la OCD sumando de forma segura sus items
     */
    public function calculateTotals(): array
    {
        // Usamos items() como query builder para asegurar que tomamos los datos frescos de la BD
        $subtotal = (float) $this->items()->sum('subtotal');
        $iva = (float) $this->items()->sum('iva_amount');
        $total = (float) $this->items()->sum('total');

        return [
            'subtotal' => round($subtotal, 2),
            'iva_amount' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Actualiza los totales de la OCD basándose en sus items
     */
    public function updateTotals(): void
    {
        $totals = $this->calculateTotals();

        $this->update([
            'subtotal' => $totals['subtotal'],
            'iva_amount' => $totals['iva_amount'],
            'total' => $totals['total'],
        ]);
    }

    /**
     * =========================================
     * SCOPES
     * =========================================
     */

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'PENDING_APPROVAL');
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeAssignedToApprover($query, $userId)
    {
        return $query->where('assigned_approver_id', $userId)
            ->where('status', 'PENDING_APPROVAL');
    }

    public function scopeByCostCenter($query, $costCenterId)
    {
        return $query->where('cost_center_id', $costCenterId);
    }

    public function scopeByMonth($query, string $month)
    {
        return $query->where('application_month', $month);
    }
}
