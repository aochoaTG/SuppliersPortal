<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    // Días naturales antes de cierre automático por inactividad
    const INACTIVITY_DAYS = 10;

    protected $fillable = [
        'folio',
        'requisition_id',
        'supplier_id',
        'quotation_summary_id',
        'subtotal',
        'iva_amount',
        'total',
        'currency',
        'payment_terms',
        'estimated_delivery_days',
        'status',
        'created_by',
        'approved_at',
        'closed_at',
        'inactivity_warning_sent_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
        'inactivity_warning_sent_at' => 'datetime',
    ];

    // Relación con el creador (el Superadmin que autorizó)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relación con el proveedor a quien le compramos
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relación con la requisición origen
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    // El corazón de la OC: sus partidas
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }

    public function isClosedByInactivity(): bool
    {
        return $this->status === 'CLOSED_BY_INACTIVITY';
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'OPEN' => 'Abierta',
            'RECEIVED' => 'Recibida',
            'CANCELLED' => 'Cancelada',
            'PAID' => 'Pagada',
            'CLOSED_BY_INACTIVITY' => 'Cerrada por Inactividad',
            default => 'Desconocido',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'OPEN' => 'warning',
            'RECEIVED' => 'success',
            'CANCELLED' => 'danger',
            'PAID' => 'primary',
            'CLOSED_BY_INACTIVITY' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Fecha límite de aprobación (created_at + 10 días naturales).
     */
    public function getAutoCloseDeadline(): \Carbon\Carbon
    {
        return $this->created_at->copy()->addDays(self::INACTIVITY_DAYS);
    }

    /**
     * Días naturales restantes antes del cierre automático.
     * Negativo si ya venció.
     */
    public function getDaysUntilAutoClose(): int
    {
        return (int) now()->diffInDays($this->getAutoCloseDeadline(), false);
    }
}
