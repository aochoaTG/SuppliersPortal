<?php

namespace App\Models;

use App\Enum\RequisitionStatus;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';

    protected $fillable = [
        'company_id',
        'cost_center_id',
        'department_id',
        'fiscal_year',
        'folio',
        'requested_by',
        'required_date',
        'description',
        'justification',
        'amount_requested',
        'currency_code',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',

        // On hold
        'on_hold_reason',
        'on_hold_by',
        'on_hold_at',

        // Rechazo
        'rejection_reason',
        'rejected_by',
        'rejected_at',

        // Cancelación
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',

        // Auditoría
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'amount_requested' => 'decimal:2',
        'required_date' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'on_hold_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Lógica

    public function onHoldUser()
    {
        return $this->belongsTo(User::class, 'on_hold_by');
    }

    public function recalcTotals(): void
    {
        $sum = $this->items()->sum('line_total'); // cambia a total_with_tax si tu tope incluye IVA
        $this->amount_requested = $sum;
        $this->save();
    }

    /** Genera folio 'REQ-YYYY-###' incremental por año. */
    public static function nextFolio(int $year): string
    {
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

    /** Etiqueta legible del estado (desde Enum). */
    public function statusLabel(): string
    {
        $opts = RequisitionStatus::options();
        return $opts[$this->status] ?? $this->status;
    }

    public function scopePendingReview($q)
    {
        return $q->where('status', 'in_review')
            ->whereNull('reviewed_by');
    }

    public function scopePendingApproval($q)
    {
        return $q->where('status', 'in_review')
            ->whereNotNull('reviewed_by')
            ->whereNull('approved_by');
    }

    public function scopeRejected($q)
    {
        return $q->where('status', 'rejected');
    }
}
