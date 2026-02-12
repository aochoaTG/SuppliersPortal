@extends('layouts.zircos')

@section('page.title', 'Buzón de Autorizaciones')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary"><i class="ti ti-clipboard-check me-1"></i>Adjudicaciones Pendientes de Firma</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0" id="approvals-table">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha Req.</th>
                                <th>Folio Req.</th>
                                <th>Proveedor Elegido</th>
                                <th>Monto Total</th>
                                <th>Nivel Requerido</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $summary)
                                @php
                                    // 1. Buscamos las respuestas del proveedor ganador para esta requisición
                                    // Pasamos por las RFQs vinculadas a la Requisición del sumario
                                    $winningResponses = $summary->requisition->rfqs->flatMap->rfqResponses
                                                        ->where('supplier_id', $summary->selected_supplier_id);

                                    // 2. Preparamos el JSON de partidas para el botón
                                    $itemsJson = $winningResponses->map(function($resp) {
                                        return [
                                            'desc'  => $resp->requisitionItem->description ?? 'Sin descripción',
                                            'qty'   => number_format($resp->quantity, 2),
                                            'price' => number_format($resp->unit_price, 2),
                                            'total' => number_format($resp->total, 2)
                                        ];
                                    })->values()->toJson();

                                    // 3. Datos globales de la cotización
                                    $maxDelivery = $winningResponses->max('delivery_days') ?? 0;
                                    $paymentTerms = $winningResponses->first()->payment_terms ?? 'No especificado';
                                @endphp
                                <tr>
                                    <td>{{ $summary->created_at->format('d/m/Y') }}</td>
                                    <td><span class="fw-bold">{{ $summary->requisition->folio }}</span></td>
                                    <td>{{ $summary->selectedSupplier?->company_name ?? 'N/A' }}</td>
                                    <td><span class="text-dark fw-bold">${{ number_format($summary->total, 2) }}</span></td>
                                    <td>
                                        <span class="badge bg-soft-{{ $summary->approvalLevel?->color_tag ?? 'secondary' }} text-{{ $summary->approvalLevel?->color_tag ?? 'secondary' }}">
                                            {{ strtoupper($summary->approvalLevel?->label ?? 'Nivel no definido') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-review" 
                                                data-id="{{ $summary->id }}"
                                                data-folio="{{ $summary->requisition->folio }}"
                                                data-total="{{ number_format($summary->total, 2) }}"
                                                data-justification="{{ $summary->justification }}"
                                                data-supplier="{{ $summary->selectedSupplier->company_name ?? 'N/A' }}"
                                                data-payment="{{ $paymentTerms }}"
                                                data-delivery="{{ $maxDelivery }}"
                                                data-items="{{ $itemsJson }}">
                                            <i class="ti ti-eye me-1"></i>Revisar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE REVISIÓN INTEGRAL --}}
<div class="modal fade" id="modalReview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="approval-form" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <input type="hidden" name="status" id="decision_status">
            
            <div class="modal-header bg-dark text-white border-0">
                <div class="d-flex align-items-center">
                    <i class="ti ti-shield-check fs-24 me-2"></i>
                    <div>
                        <h5 class="modal-title mb-0">Revisión de Adjudicación</h5>
                        <small class="text-light opacity-75" id="modal_folio_req"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                {{-- SECCIÓN 1: PANEL PRESUPUESTAL --}}
                <div class="p-3 bg-soft-success border-bottom">
                    <div class="row align-items-center text-center">
                        <div class="col-md-4 border-end">
                            <small class="text-muted d-block italic">Presupuesto Mensual</small>
                            <span class="fw-bold text-dark">$200,000.00</span>
                        </div>
                        <div class="col-md-4 border-end">
                            <small class="text-muted d-block text-primary fw-bold">Monto Compra</small>
                            <span class="fw-bold text-primary fs-18" id="modal_total"></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block italic">Saldo Resultante</small>
                            <span class="fw-bold text-success" id="modal_remaining_budget"></span>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    {{-- SECCIÓN 2: CONDICIONES COMERCIALES --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase fw-bold fs-11 mb-2">Proveedor Seleccionado</h6>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center text-white">
                                    <i class="ti ti-building-store fs-20"></i>
                                </div>
                                <h5 class="fw-bold text-primary mb-0" id="modal_supplier"></h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted text-uppercase fw-bold fs-11 mb-2">Pago (Global)</h6>
                            <span class="badge bg-soft-info text-info border border-info border-opacity-25 py-1 px-2" id="modal_payment_terms"></span>
                        </div>
                        <div class="col-md-3 text-end">
                            <h6 class="text-muted text-uppercase fw-bold fs-11 mb-2">Entrega (Máx)</h6>
                            <span class="text-dark fw-bold fs-14" id="modal_delivery_days"></span>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: TABLA DE PARTIDAS --}}
                    <h6 class="text-muted text-uppercase fw-bold fs-11 mb-2">Desglose de Partidas</h6>
                    <div class="table-responsive border rounded mb-4 shadow-sm" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0 table-centered">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">Descripción</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">P. Unit</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modal_items_table">
                                {{-- Llenado vía JavaScript --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- SECCIÓN 4: JUSTIFICACIÓN --}}
                    <div class="card bg-light border-0 shadow-none">
                        <div class="card-body p-3 border-start border-primary border-3">
                            <h6 class="fw-bold mb-2 text-dark"><i class="ti ti-message-2 me-1 text-primary"></i>Justificación del Comprador</h6>
                            <p class="mb-0 text-muted italic small" id="modal_justification"></p>
                        </div>
                    </div>

                    {{-- ÁREA DE RECHAZO --}}
                    <div class="mt-4 animated fadeIn" id="rejection_area" style="display: none;">
                        <label class="form-label fw-bold text-danger"><i class="ti ti-alert-triangle me-1"></i>Motivo del Rechazo (Obligatorio):</label>
                        <textarea name="reason" id="rejection_reason" class="form-control border-danger" rows="3" placeholder="Indique qué debe ajustar el comprador..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <div class="me-auto">
                    <button type="button" class="btn btn-outline-danger" onclick="setDecision('rejected')">
                        <i class="ti ti-arrow-back-up me-1"></i>Rechazar
                    </button>
                </div>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success px-4 shadow-sm" onclick="setDecision('approved')">
                    <i class="ti ti-signature me-1"></i>Autorizar Adjudicación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function setDecision(status) {
        const reasonField = $('#rejection_reason');
        
        if(status === 'rejected') {
            if ($('#rejection_area').is(':hidden')) {
                $('#rejection_area').slideDown();
                Swal.fire('Atención', 'Por favor escriba el motivo del rechazo en el campo rojo.', 'info');
                return;
            }
            
            if(reasonField.val().trim().length < 10) {
                Swal.fire('Atención', 'Debe proporcionar un motivo de rechazo detallado (mín. 10 caracteres).', 'warning');
                reasonField.addClass('is-invalid');
                return;
            }
        }

        $('#decision_status').val(status);
        
        Swal.fire({
            title: status === 'approved' ? '¡Confirmar Adjudicación!' : '¿Rechazar Adjudicación?',
            html: status === 'approved' 
                ? `Al autorizar, el sistema realizará las siguientes acciones:
                <ul class="text-start mt-3 small">
                    <li>Se generará la <b>Orden de Compra</b> oficial.</li>
                    <li>Se notificará al <b>proveedor seleccionado</b> para iniciar el suministro.</li>
                    <li>Se cerrará el proceso de cotización para esta requisición.</li>
                </ul>`
                : `Al rechazar, la adjudicación actual se cancelará:
                <ul class="text-start mt-3 small">
                    <li>El comprador será notificado del rechazo.</li>
                    <li>La requisición volverá a la fase de <b>Evaluación</b>.</li>
                    <li>Deberá seleccionarse un proveedor distinto o solicitar nuevas cotizaciones.</li>
                </ul>`,
            icon: status === 'approved' ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonText: status === 'approved' ? 'Sí, Generar Orden de Compra' : 'Sí, Rechazar y Devolver',
            cancelButtonText: 'Regresar',
            confirmButtonColor: status === 'approved' ? '#1bb99a' : '#f05050',
            cancelButtonColor: '#4c5667',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ 
                    title: status === 'approved' ? 'Generando Orden de Compra...' : 'Procesando rechazo...', 
                    allowOutsideClick: false, 
                    didOpen: () => { Swal.showLoading(); }
                });
                $('#approval-form').submit();
            }
        });
    }

    $('.btn-review').on('click', function() {
        const data = $(this).data();
        
        // 1. Cálculos de presupuesto
        const rawTotal = parseFloat(data.total.replace(/[^0-9.-]+/g,""));
        const budget = 200000;
        const remaining = budget - rawTotal;

        // 2. Llenar cabecera e info comercial
        $('#modal_folio_req').text('Folio: ' + data.folio);
        $('#modal_supplier').text(data.supplier);
        $('#modal_total').text('$' + data.total);
        $('#modal_payment_terms').text(data.payment);
        $('#modal_delivery_days').text(data.delivery + ' días');
        $('#modal_justification').text(data.justification || 'Sin justificación registrada.');
        
        const remainingBudgetElem = $('#modal_remaining_budget');
        remainingBudgetElem.text('$' + remaining.toLocaleString('en-US', {minimumFractionDigits: 2}));
        remainingBudgetElem.removeClass('text-success text-danger').addClass(remaining >= 0 ? 'text-success' : 'text-danger');

        // 3. DESEMPAQUE DE PARTIDAS DESDE JSON
        let itemsHtml = '';
        const items = data.items;

        if(items && items.length > 0) {
            items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td class="ps-3"><div class="fw-semibold text-dark">${item.desc}</div></td>
                        <td class="text-center"><code>${item.qty}</code></td>
                        <td class="text-end">$${item.price}</td>
                        <td class="text-end pe-3 fw-bold text-primary">$${item.total}</td>
                    </tr>
                `;
            });
        } else {
            itemsHtml = '<tr><td colspan="4" class="text-center py-3 text-muted italic">No se encontró detalle de partidas.</td></tr>';
        }

        $('#modal_items_table').html(itemsHtml);

        // 4. Configuración final
        $('#approval-form').attr('action', `/approvals/quotations/${data.id}/handle`);
        $('#rejection_area').hide();
        $('#rejection_reason').val('').removeClass('is-invalid');
        
        $('#modalReview').modal('show');
    });
</script>
@endpush