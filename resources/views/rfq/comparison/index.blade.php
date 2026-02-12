@extends('layouts.zircos')

@section('title', 'Análisis Comparativo - ' . $rfq->folio)

@section('page.title', 'Análisis Comparativo')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rfq.index') }}">RFQs</a></li>
    <li class="breadcrumb-item active">Comparativo {{ $rfq->folio }}</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- ENCABEZADO DE OPERACIONES --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="float-end d-flex align-items-center">
                    <span class="badge bg-primary text-white fs-14 shadow-sm">RFQ: {{ $rfq->folio }}</span>
                </div>
                <h4 class="page-title">Análisis Comparativo de Cotizaciones</h4>
            </div>
        </div>
    </div>

    {{-- ⚠️ ALERTA DE RECHAZO PREVIO --}}
    @if($rfq->quotationSummary?->approval_status === 'rejected')
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger border-2 shadow-sm mb-0">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="ti ti-ban fs-30"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading fw-bold mb-1">ADJUDICACIÓN RECHAZADA ANTERIORMENTE</h5>
                            <p class="mb-1 text-dark"><strong>Motivo:</strong> {{ $rfq->quotationSummary->rejection_reason }}</p>
                            <small class="text-muted">Por: <strong>{{ $rfq->quotationSummary->rejectedBy?->name }}</strong> el {{ $rfq->quotationSummary->rejected_at?->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 🎯 VALIDACIÓN PRESUPUESTAL VISUAL --}}
    <div class="row mb-4">
        <div class="col-12">
            <div id="budget-panel" class="card border-3 border-top border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-success">
                                <i class="ti ti-check fs-20"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-success">✓ CONTROL PRESUPUESTAL ACTIVO</h5>
                            <p class="text-muted mb-0 small">
                                Centro de Costos: <strong>{{ $rfq->requisition->costCenter->name }}</strong> | 
                                Periodo: <strong>{{ now()->translatedFormat('F Y') }}</strong>
                            </p>
                        </div>
                        <div class="ms-auto text-end">
                            <small class="text-muted d-block italic">Disponible para esta compra</small>
                            <h4 class="mb-0 text-dark fw-bold">${{ number_format($presupuestoDisponible, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 MATRIZ VISUAL DE COMPARACIÓN --}}
    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-centered mb-0 border-light table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 300px;" class="border-bottom-0 align-middle ps-3">Especificaciones / Partidas</th>
                            @foreach($rfq->suppliers as $supplier)
                                @php 
                                    $hasResponded = !is_null($supplier->pivot->responded_at);
                                    
                                    // Cálculo de montos para nivel dinámico vía ApprovalService
                                    $subtotal = $rfq->rfqResponses->where('supplier_id', $supplier->id)->sum('subtotal');
                                    $iva = $rfq->rfqResponses->where('supplier_id', $supplier->id)->sum('iva_amount');
                                    $totalProyectado = $subtotal + $iva;

                                    $nivelAsignado = $approvalLevels->first(function($lvl) use ($totalProyectado) {
                                        return $totalProyectado >= $lvl->min_amount && 
                                               (is_null($lvl->max_amount) || $totalProyectado <= $lvl->max_amount);
                                    });
                                @endphp
                                
                                <th class="text-center {{ $hasResponded ? 'bg-soft-light' : 'bg-soft-secondary' }}" style="min-width: 250px;">
                                    <div class="fw-bold fs-14 {{ $hasResponded ? 'text-primary' : 'text-muted' }}">
                                        {{ $supplier->company_name }}
                                    </div>
                                    
                                    @if($hasResponded)
                                        @if($nivelAsignado)
                                            <div class="mt-1">
                                                <span class="badge bg-soft-{{ $nivelAsignado->color_tag }} text-{{ $nivelAsignado->color_tag }} border border-{{ $nivelAsignado->color_tag }} border-opacity-25 fs-10" 
                                                      title="{{ $nivelAsignado->description }}">
                                                    <i class="ti ti-shield-check me-1"></i>{{ strtoupper($nivelAsignado->label) }}
                                                </span>
                                            </div>
                                        @endif
                                        <span class="badge bg-success fs-9 mt-1 shadow-sm">OFERTA RECIBIDA</span>
                                    @else
                                        <span class="badge bg-warning fs-9 mt-1">SIN RESPUESTA</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="bg-light ps-3">
                                    <span class="fw-bold text-dark">{{ $item->description }}</span><br>
                                    <small class="text-muted">Cant: {{ number_format($item->quantity, 2) }} {{ $item->unit }}</small>
                                </td>
                                @foreach($rfq->suppliers as $supplier)
                                    @php
                                        $resp = $rfq->rfqResponses->where('supplier_id', $supplier->id)->where('requisition_item_id', $item->id)->first();
                                    @endphp
                                    <td class="{{ $resp ? '' : 'bg-soft-danger text-center' }}">
                                        @if($resp)
                                            {{-- 💰 PRECIO Y MARCA --}}
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fs-15 fw-bold text-dark">${{ number_format($resp->unit_price, 2) }}</span>
                                                <span class="badge bg-soft-info text-info fs-9 border border-info border-opacity-10">
                                                    {{ $resp->brand ?? 'Sin marca' }}
                                                </span>
                                            </div>

                                            {{-- 🛠️ ESPECIFICACIONES TÉCNICAS (NUEVO) --}}
                                            @if($resp->specifications)
                                                <div class="mb-1 p-1 bg-light rounded border-start border-primary border-2">
                                                    <small class="d-block text-dark fw-semibold" style="font-size: 10px;">ESPECIFICACIONES:</small>
                                                    <small class="text-muted d-block lh-sm" style="font-size: 10px;">{{ Str::limit($resp->specifications, 100) }}</small>
                                                </div>
                                            @endif

                                            {{-- 🛡️ GARANTÍA Y ADJUNTO (NUEVO) --}}
                                            <div class="d-flex gap-1 mb-1">
                                                @if($resp->warranty_terms)
                                                    <span class="badge bg-soft-dark text-dark border border-dark border-opacity-10 fs-9" title="Garantía ofrecida">
                                                        <i class="ti ti-shield-check me-1"></i>{{ $resp->warranty_terms }}
                                                    </span>
                                                @endif
                                                
                                                @if($resp->attachment_path)
                                                    <a href="{{ asset('storage/' . $resp->attachment_path) }}" target="_blank" 
                                                       class="badge bg-soft-primary text-primary border border-primary border-opacity-10 fs-9 text-decoration-none" 
                                                       title="Ver archivo adjunto">
                                                        <i class="ti ti-paperclip me-1"></i>VER DOC
                                                    </a>
                                                @endif
                                            </div>

                                            {{-- 📝 NOTAS ADICIONALES (NUEVO) --}}
                                            @if($resp->notes)
                                                <div class="p-1" style="background-color: #fffcf0; border: 1px dashed #f6e05e; border-radius: 4px;">
                                                    <small class="text-muted italic d-block" style="font-size: 10px;">
                                                        <i class="ti ti-message-2 me-1"></i>{{ $resp->notes }}
                                                    </small>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-danger fw-bold fs-11"><i class="ti ti-clock-exclamation me-1"></i>SIN OFERTA</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- CRITERIOS TÉCNICOS GLOBALES --}}
                        <tr class="table-secondary text-dark fw-bold small">
                            <td class="ps-3"><i class="ti ti-wallet me-1 text-primary"></i>Condiciones de Pago</td>
                            @foreach($rfq->suppliers as $supplier)
                                @php $fResp = $rfq->rfqResponses->where('supplier_id', $supplier->id)->first(); @endphp
                                <td class="text-center">{{ $fResp->payment_terms ?? 'Crédito' }}</td>
                            @endforeach
                        </tr>

                        {{-- 💰 TOTALES GLOBALES --}}
                        <tr class="table-dark">
                            <td class="text-end fw-bold ps-3 border-0">TOTAL FINAL (IVA INCLUIDO)</td>
                            @foreach($rfq->suppliers as $supplier)
                                @php 
                                    $hasResponded = !is_null($supplier->pivot->responded_at);
                                    $subtotal = $rfq->rfqResponses->where('supplier_id', $supplier->id)->sum('subtotal');
                                    $iva = $rfq->rfqResponses->where('supplier_id', $supplier->id)->sum('iva_amount');
                                    $total = $subtotal + $iva;
                                    $isOver = $total > $presupuestoDisponible;
                                @endphp
                                <td class="text-center border-0">
                                    @if($hasResponded)
                                        <h4 class="mb-0 text-white">${{ number_format($total, 2) }}</h4>
                                        <small class="text-light fs-10 d-block">Monto IVA: ${{ number_format($iva, 2) }}</small>
                                        @if($isOver)
                                            <span class="badge bg-danger fs-10 mt-1 shadow-sm"><i class="ti ti-alert-triangle me-1"></i>EXCEDE PRESUPUESTO</span>
                                        @endif
                                    @else
                                        <h5 class="text-muted mb-0">---</h5>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="small text-muted italic ps-3 py-3 border-0">
                                * Los precios incluyen impuestos y cargos logísticos configurados por el proveedor.
                            </td>
                            @foreach($rfq->suppliers as $supplier)
                                @php 
                                    $hasResponded = !is_null($supplier->pivot->responded_at);
                                    $sub = $rfq->rfqResponses->where('supplier_id', $supplier->id)->sum('subtotal');
                                    $tax = $rfq->rfqResponses->where('supplier_id', $supplier->id)->sum('iva_amount');
                                    $totalFinal = $sub + $tax;
                                @endphp
                                <td class="text-center py-3 border-0">
                                    @if($hasResponded)
                                        <button type="button" 
                                                class="btn btn-primary btn-sm btn-select-winner shadow-sm px-4 rounded-pill" 
                                                data-supplier-id="{{ $supplier->id }}"
                                                data-supplier-name="{{ $supplier->company_name }}"
                                                data-total="{{ number_format($totalFinal, 2) }}"
                                                {{ ($totalFinal > $presupuestoDisponible) ? 'disabled' : '' }}>
                                            <i class="ti ti-trophy me-1"></i>Adjudicar
                                        </button>
                                    @else
                                        <button class="btn btn-light btn-sm rounded-pill" disabled>En espera</button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE ADJUDICACIÓN --}}
<div class="modal fade" id="modalAdjudicar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('rfq.comparison.select', $rfq) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <input type="hidden" name="supplier_id" id="winner_id">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title"><i class="ti ti-shield-check me-2"></i>Confirmar Selección de Proveedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="card bg-light border-0 mb-3 shadow-none">
                    <div class="card-body">
                        <p class="mb-1 text-muted small">Proveedor Ganador Seleccionado:</p>
                        <h5 class="fw-bold text-primary mb-0" id="winner_name"></h5>
                        <hr class="my-2 opacity-10">
                        <p class="mb-0 small text-dark">Monto Total a Comprometer: <strong id="winner_total" class="fs-15"></strong></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="ti ti-message-dots me-1"></i>Justificación Técnica para Aprobación</label>
                    <textarea name="justification" class="form-control border-primary border-opacity-25" rows="4" 
                              placeholder="Especifique el criterio de selección: mejor precio, menor tiempo de entrega, calidad superior..." 
                              required></textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold"><i class="ti ti-notes me-1"></i>Notas Adicionales (Internas)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Observaciones que verá el nivel aprobador">
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 shadow rounded-pill">
                    <i class="ti ti-device-floppy me-1"></i>Enviar a Aprobación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.btn-select-winner').on('click', function() {
            const id = $(this).data('supplier-id');
            const name = $(this).data('supplier-name');
            const total = $(this).data('total');
            
            $('#winner_id').val(id);
            $('#winner_name').text(name);
            $('#winner_total').text('$' + total);
            $('#modalAdjudicar').modal('show');
        });
    });
</script>
@endpush