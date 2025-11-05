<?php

// app/Models/SupplierDocument.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDocument extends Model
{
    protected $fillable = [
        'supplier_id','uploaded_by','doc_type','path_file','size_bytes','mime_type',
        'status','rejection_reason','uploaded_at','reviewed_by','reviewed_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Lista simple y centralizada de tipos requeridos
    public const REQUIRED_TYPES = [
        'constancia_fiscal',
        'comprobante_domicilio',
        'caratula_bancaria',
        'opinion_sat',
        'acta_constitutiva',
        'poder_legal',
        'identificacion_oficial',
        'opinion_imss',
        'opinion_infonavit',
        'solicitud_alta_proveedor',
        'repse',
        'acta_confidencialidad',
        'curso_induccion',
    ];

    // 👉 Tipos con tope 50MB
    public const LARGE_TYPES = ['acta_constitutiva','poder_legal'];

    public const STATUS = ['pending_review','accepted','rejected'];

    public static function maxKbFor(string $docType): int
    {
        return in_array($docType, self::LARGE_TYPES, true) ? 51200 /* 50MB */ : 10240 /* 10MB */;
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
