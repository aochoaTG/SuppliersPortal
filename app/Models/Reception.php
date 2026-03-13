<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reception extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING   = 'PENDING';    // Iniciada, aún no finalizada
    const STATUS_PARTIAL   = 'PARTIAL';    // Guardada con cantidades menores a las ordenadas
    const STATUS_COMPLETED = 'COMPLETED';  // Todos los ítems de este evento fueron procesados

    protected $fillable = [
        'folio',
        'receivable_type',
        'receivable_id',
        'receiving_location_id',
        'received_by',
        'status',
        'delivery_reference',
        'notes',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    /**
     * =========================================
     * RELACIONES
     * =========================================
     */

    /**
     * La orden a la que pertenece esta recepción.
     * Puede ser PurchaseOrder o DirectPurchaseOrder.
     */
    public function receivable(): MorphTo
    {
        return $this->morphTo();
    }

    public function receivingLocation(): BelongsTo
    {
        return $this->belongsTo(ReceivingLocation::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceptionItem::class);
    }

    /**
     * =========================================
     * GENERACIÓN DE FOLIO
     * =========================================
     */

    public static function generateNextFolio(): string
    {
        $year = now()->year;
        $last = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/REC-\d{4}-(\d{4})/', $last->folio, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return sprintf('REC-%d-%04d', $year, $nextNumber);
    }

    /**
     * =========================================
     * VERIFICADORES DE ESTADO
     * =========================================
     */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Pendiente',
            self::STATUS_PARTIAL   => 'Parcial',
            self::STATUS_COMPLETED => 'Completada',
            default                => 'Desconocido',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'warning',
            self::STATUS_PARTIAL   => 'primary',
            self::STATUS_COMPLETED => 'success',
            default                => 'secondary',
        };
    }

    /**
     * =========================================
     * SCOPES
     * =========================================
     */

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForLocation($query, int $locationId)
    {
        return $query->where('receiving_location_id', $locationId);
    }

    public function scopeReceivedBy($query, int $userId)
    {
        return $query->where('received_by', $userId);
    }
}
