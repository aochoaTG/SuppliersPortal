<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectPurchaseOrderApproval extends Model
{
    use HasFactory;

    protected $table = 'odc_direct_purchase_order_approvals';

    protected $fillable = [
        'direct_purchase_order_id',
        'approval_level',
        'approver_user_id',
        'action',
        'comments',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * =========================================
     * RELACIONES
     * =========================================
     */

    public function directPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(DirectPurchaseOrder::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    /**
     * =========================================
     * MÉTODOS DE NEGOCIO
     * =========================================
     */

    /**
     * Verifica si la acción fue una aprobación
     */
    public function isApproved(): bool
    {
        return $this->action === 'APPROVED';
    }

    /**
     * Verifica si la acción fue un rechazo
     */
    public function isRejected(): bool
    {
        return $this->action === 'REJECTED';
    }

    /**
     * Verifica si la acción fue una devolución
     */
    public function isReturned(): bool
    {
        return $this->action === 'RETURNED';
    }

    /**
     * Obtiene el badge de color según la acción
     */
    public function getActionBadgeClass(): string
    {
        return match ($this->action) {
            'APPROVED' => 'success',
            'REJECTED' => 'danger',
            'RETURNED' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Obtiene el texto legible de la acción
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'APPROVED' => 'Aprobada',
            'REJECTED' => 'Rechazada',
            'RETURNED' => 'Devuelta',
            default => 'Desconocido'
        };
    }

    /**
     * Obtiene el ícono según la acción
     */
    public function getActionIcon(): string
    {
        return match ($this->action) {
            'APPROVED' => 'check-circle',
            'REJECTED' => 'x-circle',
            'RETURNED' => 'arrow-left-circle',
            default => 'help-circle'
        };
    }

    /**
     * =========================================
     * SCOPES
     * =========================================
     */

    /**
     * Scope para filtrar por tipo de acción
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para aprobaciones de un usuario específico
     */
    public function scopeByApprover($query, $userId)
    {
        return $query->where('approver_user_id', $userId);
    }
}
