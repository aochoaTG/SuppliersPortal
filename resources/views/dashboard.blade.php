@extends('layouts.zircos')

@section('title', 'Dashboard')
@php
    $labels = [
        'acta_constitutiva'        => 'Acta constitutiva',
        'constancia_fiscal'        => 'Constancia de situación fiscal',
        'opinion_cumplimiento'     => 'Opinión de cumplimiento SAT',
        'constancia_repse'         => 'Constancia REPSE',
        'contrato'                 => 'Contrato firmado',
        'comprobante_domicilio'    => 'Comprobante de domicilio',
        'caratula_bancaria'        => 'Carátula bancaria',
        'opinion_sat'              => 'Opinión positiva del SAT',
        'poder_legal'              => 'Poder legal',
        'identificacion_oficial'   => 'Identificación oficial',
        'opinion_imss'             => 'Opinión positiva del IMSS',
        'opinion_infonavit'        => 'Opinión positiva del Infonavit',
        'solicitud_alta_proveedor' => 'Solicitud de alta de proveedor',
        'acta_confidencialidad'    => 'Acta de confiencialidad',
        'curso_induccion'          => 'Curso de inducción',
        'repse'                    => 'REPSE',
        // 👉 agrega los que uses en tu sistema
    ];
@endphp
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="page-title">Bienvenido al Portal de proveedores 🚀</h4>

            @php($u = auth()->user())
            @hasanyrole('supplier')
                @if ($u && method_exists($u, 'mustFinishSupplierOnboarding') && $u->mustFinishSupplierOnboarding())
                    @php($missing = $u?->supplier?->missingRequiredDocuments() ?? [])

                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <div class="me-2 fs-4 lh-1">
                            <i class="ti ti-file-alert"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Tu registro de proveedor está pendiente</h5>
                            <p class="mb-2">
                                Por favor completa tu <strong>alta</strong> y la <strong>carga de documentos</strong>.
                                Después, el equipo de <strong>TotalGas</strong> validará la información y, al aprobarla,
                                podrás usar todas las funciones del portal.
                            </p>

                            @if(!empty($missing))
                                <div class="mb-2 small text-muted">
                                    <i class="ti ti-checklist me-1"></i>
                                    Documentos pendientes ({{ count($missing) }}):
                                </div>
                                <ul class="mb-2 ps-4">
                                    @foreach($missing as $doc)
                                        <li>{{ $labels[$doc] ?? $doc }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <a href="{{ route('documents.suppliers.index') }}"
                               class="btn btn-sm btn-warning">
                                <i class="ti ti-upload me-1"></i> Completar alta / subir documentos
                            </a>

                        </div>
                    </div>
                @endif
            @endhasanyrole
        </div>
    </div>
</div>
@endsection
