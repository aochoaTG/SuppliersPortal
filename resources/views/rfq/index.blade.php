@extends('layouts.zircos')

@section('title', 'Solicitudes de Cotización (RFQ)')

{{-- Breadcrumbs personalizados --}}
@section('page.breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">Inicio</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('requisitions.inbox.validation') }}">Buzón de Validación</a>
    </li>
    <li class="breadcrumb-item active">Solicitudes de Cotización</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="ti ti-file-invoice me-2"></i>
                        Solicitudes de Cotización
                    </h2>
                    <p class="text-muted mb-0">
                        Gestión de RFQs enviadas a proveedores
                    </p>
                </div>
                <div>
                    <a href="{{ route('requisitions.inbox.validation') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Volver al Buzón
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETAS DE RESUMEN --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="ti ti-clock fs-1 text-warning mb-2"></i>
                    <h3 class="mb-1" id="draftCount">-</h3>
                    <p class="text-muted mb-0">Borradores</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="ti ti-send fs-1 text-info mb-2"></i>
                    <h3 class="mb-1" id="sentCount">-</h3>
                    <p class="text-muted mb-0">Enviadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="ti ti-check fs-1 text-success mb-2"></i>
                    <h3 class="mb-1" id="respondedCount">-</h3>
                    <p class="text-muted mb-0">Con Respuestas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="ti ti-alert-triangle fs-1 text-danger mb-2"></i>
                    <h3 class="mb-1" id="expiredCount">-</h3>
                    <p class="text-muted mb-0">Vencidas</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Todos</option>
                                <option value="DRAFT">Borrador</option>
                                <option value="SENT">Enviada</option>
                                <option value="RESPONSES_RECEIVED">Con Respuestas</option>
                                <option value="EVALUATED">Evaluada</option>
                                <option value="CANCELLED">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="filterDateTo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" id="applyFilters">
                                    <i class="ti ti-filter"></i> Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- GLOSARIO DE ESTADOS --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-light border mb-0 py-2" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ti ti-info-circle me-2 text-primary"></i>
                    <strong class="me-3">Estados:</strong>
                    <div class="d-flex flex-wrap gap-3 small">
                        <span>
                            <span class="badge bg-secondary" data-bs-toggle="tooltip" data-bs-title="No enviado">Borrador</span> 
                        </span>
                        <span class="text-muted">|</span>
                        <span>
                            <span class="badge bg-info" data-bs-toggle="tooltip" data-bs-title="Esperando respuesta">Enviada</span> 
                        </span>
                        <span class="text-muted">|</span>
                        <span>
                            <span class="badge bg-primary" data-bs-toggle="tooltip" data-bs-title="Proveedor cotizó">Con Respuestas</span>
                        </span>
                        <span class="text-muted">|</span>
                        <span>
                            <span class="badge bg-warning" data-bs-toggle="tooltip" data-bs-title="En análisis">Evaluada</span> 
                        </span>
                        <span class="text-muted">|</span>
                        <span>
                            <span class="badge bg-success" data-bs-toggle="tooltip" data-bs-title="Proceso finalizado">Completada</span> 
                        </span>
                        <span class="text-muted">|</span>
                        <span>
                            <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-title="Sin efecto">Cancelada</span> 
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE RFQs --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="ti ti-list me-2"></i>
                        Listado de Solicitudes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="rfqsTable" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Folio</th>
                                    <th width="10%">Requisición</th>
                                    <th width="10%">Grupo/Partida</th>
                                    <th width="10%">Proveedores</th>
                                    <th width="10%">Listado</th>
                                    <th width="10%">Estado</th>
                                    <th width="10%">Fecha Envío</th>
                                    <th width="10%">Fecha Límite</th>
                                    <th width="10%">Días Restantes</th>
                                    <th width="10%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables carga aquí --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

<style>
.status-badge {
    font-size: 0.85rem;
    padding: 0.35rem 0.65rem;
    font-weight: 500;
}

.days-remaining {
    font-weight: 600;
}

.days-remaining.text-danger {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    console.log('🎨 Inicializando RFQs DataTable');

    // ================================================================
    // DATATABLE
    // ================================================================
    const table = $('#rfqsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('rfq.datatable') }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
            }
        },
        columns: [
            { 
                data: 'folio', 
                name: 'folio',
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'requisition_folio', 
                name: 'requisition.folio',
                render: function(data, type, row) {
                    const url = `/requisitions/${row.requisition_id}`;
                    return `<a href="${url}" class="text-decoration-none">${data || 'N/A'}</a>`;
                }
            },
            { 
                data: 'group_or_item', 
                name: 'quotation_group_id',
                orderable: false,
                searchable: false
            },
            { 
                data: 'suppliers_count', 
                name: 'suppliers_count',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-info">${data}</span>`;
                }
            },
            { 
                data: 'suppliers_list', 
                name: 'suppliers_list',
                className: 'text-center',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'status_badge', 
                name: 'status',
                className: 'text-center'
            },
            { 
                data: 'sent_at', 
                name: 'sent_at',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'response_deadline', 
                name: 'response_deadline',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'days_remaining', 
                name: 'days_remaining',
                className: 'text-center',
                orderable: false
            },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
        },
        responsive: true,
        drawCallback: function() {
            // Activar tooltips después de cada redibujado
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Cada vez que la tabla se dibuja, activamos los tooltips de los nuevos elementos
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            console.log("Sargento: Tooltips de proveedores desplegados con éxito.");
        }
    });

    // ================================================================
    // APLICAR FILTROS
    // ================================================================
    $('#applyFilters').on('click', function() {
        table.draw();
    });

    // También filtrar al presionar Enter en los campos de fecha
    $('#filterDateFrom, #filterDateTo').on('keypress', function(e) {
        if (e.which === 13) {
            table.draw();
        }
    });

    // ================================================================
    // CARGAR RESUMEN DE CONTADORES
    // ================================================================
    loadSummary();

    function loadSummary() {
        $.ajax({
            url: '{{ route('rfq.summary') }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#draftCount').text(response.data.draft || 0);
                    $('#sentCount').text(response.data.sent || 0);
                    $('#respondedCount').text(response.data.responded || 0);
                    $('#expiredCount').text(response.data.expired || 0);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar resumen:', xhr);
            }
        });
    }

    // Recargar resumen cada 60 segundos
    setInterval(loadSummary, 60000);

    // ================================================================
    // ACCIONES EN LA TABLA
    // ================================================================
    
    // Enviar RFQ
    $(document).on('click', '.btn-send-rfq', function() {
        const rfqId = $(this).data('rfq-id');
        const folio = $(this).data('folio');
        const emailsRaw = $(this).data('emails'); // Capturamos los correos

        // Convertimos la cadena de correos en una lista HTML para el SWAL
        let emailsHtml = '';
        if (emailsRaw) {
            const emailList = emailsRaw.split(', ');
            emailsHtml = '<div class="mt-3 text-start"><p class="mb-1 text-muted small">Se notificará a:</p><ul class="list-group list-group-flush border rounded" style="max-height: 150px; overflow-y: auto;">';
            emailList.forEach(email => {
                emailsHtml += `<li class="list-group-item py-1 small"><i class="ti ti-mail me-2"></i>${email}</li>`;
            });
            emailsHtml += '</ul></div>';
        } else {
            emailsHtml = '<div class="alert alert-warning mt-3 small">⚠️ No hay correos registrados para estos proveedores.</div>';
        }

        Swal.fire({
            icon: 'question',
            title: '¿Confirmar envío?',
            html: `¿Estás seguro de enviar la solicitud <strong>${folio}</strong>?<br>
                ${emailsHtml}`, // Insertamos la lista aquí
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-send me-2"></i>Sí, enviar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#198754', // Color success de Bootstrap
            width: '450px' // Un poco más ancho para que la lista luzca bien
        }).then((result) => {
            if (result.isConfirmed) {
                sendRFQ(rfqId);
            }
        });
    });

    function sendRFQ(rfqId) {
        Swal.fire({
            title: 'Enviando...',
            html: 'Por favor espera un momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/rfq/${rfqId}/send-single`,  // ✅ Cambiado
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        table.draw();
                        loadSummary();
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'No se pudo enviar la RFQ'
                });
            }
        });
    }

    // Cancelar RFQ
    $(document).on('click', '.btn-cancel-rfq', function() {
        const rfqId = $(this).data('rfq-id');
        const folio = $(this).data('folio');

        Swal.fire({
            icon: 'warning',
            title: '¿Cancelar RFQ?',
            html: `¿Estás seguro de cancelar la solicitud <strong>${folio}</strong>?<br><br>
                   <small class="text-muted">Esta acción no se puede deshacer.</small>`,
            input: 'textarea',
            inputPlaceholder: 'Motivo de cancelación (opcional)',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                cancelRFQ(rfqId, result.value);
            }
        });
    });

    function cancelRFQ(rfqId, reason) {
        $.ajax({
            url: `/rfq/${rfqId}/cancel`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelada',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        table.draw();
                        loadSummary();
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'No se pudo cancelar la RFQ'
                });
            }
        });
    }
});
</script>
@endpush