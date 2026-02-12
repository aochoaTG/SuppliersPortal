<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Storage;
use App\Models\Rfq;
use App\Models\Supplier;

class RfqSupplier extends Pivot
{
    /**
     * Tabla asociada
     */
    protected $table = 'rfq_suppliers';

    /**
     * Indica que el modelo usa timestamps
     */
    public $timestamps = true;

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'invited_at',
        'responded_at',
        'quotation_pdf_path',
        'notes',
    ];

    /**
     * Casteo de atributos
     */
    protected $casts = [
        'invited_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * RFQ asociada
     */
    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    /**
     * Proveedor asociado
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // =========================================================================
    // ACCESORIOS
    // =========================================================================

    /**
     * URL pública del PDF de cotización
     */
    public function getQuotationPdfUrlAttribute()
    {
        if ($this->quotation_pdf_path && Storage::disk('public')->exists($this->quotation_pdf_path)) {
            return Storage::disk('public')->url($this->quotation_pdf_path);
        }
        return null;
    }

    /**
     * Verifica si tiene PDF adjunto
     */
    public function hasPdf()
    {
        return !empty($this->quotation_pdf_path) &&
            Storage::disk('public')->exists($this->quotation_pdf_path);
    }

    /**
     * Elimina el PDF adjunto
     */
    public function deletePdf()
    {
        if ($this->quotation_pdf_path && Storage::disk('public')->exists($this->quotation_pdf_path)) {
            Storage::disk('public')->delete($this->quotation_pdf_path);
            $this->update(['quotation_pdf_path' => null]);
            return true;
        }
        return false;
    }
}
