@extends('layouts.zircos')

@section('title', 'RFQs Pendientes - Buzón de Entrada')

@section('page.title', 'RFQs Pendientes')

{{-- CSS ADICIONAL (opcional)  --}}
@push('styles')
    <style>
        /* Definimos la animación de carga */
        @keyframes growProgressBar {
            0% { width: 0%; }
        }

        .progress-bar-animated-load {
            /* Aplicamos la animación al aparecer */
            animation: growProgressBar 1.5s ease-in-out forwards;
        }

        /* Aseguramos que las rayas de Bootstrap se muevan */
        .progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent) !important;
            background-size: 1rem 1rem !important;
        }
    </style>
@endpush

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">RFQs</li>
    <li class="breadcrumb-item active">Pendientes</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 text-primary fw-bold">
                        <i class="ti ti-inbox me-2"></i>Buzón de Solicitudes Pendientes
                    </h5>
                    <p class="text-muted small mb-0">Monitoreo de procesos de cotización en curso.</p>
                </div>
                {{-- Filtros rápidos o botón de nueva RFQ --}}
                <a href="#" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Nueva RFQ
                </a>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="rfq-pending-table" class="table table-hover dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Grupo / Título</th>
                                <th>Requisición</th>
                                <th>Respuestas</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="infoAjaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="modal-loader-content">
            <div class="p-5 text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Recuperando información de inteligencia...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#rfq-pending-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('rfq.inbox.pending.data') }}", // Tu ruta al controlador
            columns: [
                { 
                    data: 'folio', 
                    name: 'folio', 
                    className: 'fw-bold',
                    render: function(data, type, row) {
                        return `<a href="javascript:void(0)" onclick="loadRfqModal(${row.id})" class="text-primary decoration-none">
                                    <i class="ti ti-external-link me-1"></i>${data}
                                </a>`;
                    }
                },
                { 
                    data: 'quotation_group.name', 
                    name: 'quotationGroup.name',
                    render: function(data, type, row) {
                        return `<div class="d-flex flex-column">
                                    <span class="fw-semibold">${data}</span>
                                    <small class="text-muted">RFQ Activa</small>
                                </div>`;
                    }
                },
                { 
                    data: 'requisition.folio', 
                    name: 'requisition.folio',
                    render: function(data, type, row) {
                        if(!data) return 'N/A';
                        return `<a href="javascript:void(0)" onclick="loadReqModal(${row.requisition.id})" class="text-info decoration-none">
                                    <i class="ti ti-clipboard-list me-1"></i>${data}
                                </a>`;
                    }
                },
                { 
                    data: 'progress', 
                    name: 'progress',
                    render: function(data) {
                        let colorClass = data.percent === 100 ? 'bg-success' : (data.percent >= 50 ? 'bg-warning' : 'bg-danger');
                        let stripeAnimation = data.percent < 100 ? 'progress-bar-animated' : '';

                        return `
                            <div class="d-flex align-items-center gap-2" 
                                data-bs-toggle="tooltip" 
                                data-bs-html="true" 
                                title="${data.tooltip}" 
                                style="cursor: help;">
                                <div class="progress flex-grow-1" style="height: 10px; min-width: 80px; background-color: #f1f1f1; border-radius: 10px; overflow: hidden;">
                                    <div class="progress-bar ${colorClass} progress-bar-striped ${stripeAnimation} progress-bar-animated-load" 
                                        role="progressbar" 
                                        style="width: ${data.percent}%;">
                                    </div>
                                </div>
                                <span class="small fw-bold text-dark" style="min-width: 35px;">${data.label}</span>
                            </div>`;
                    }
                },
                { 
                    data: 'response_deadline', 
                    name: 'response_deadline',
                    render: function(data) {
                        let textClass = data.is_past ? 'text-danger' : 'text-warning';
                        return `<div class="d-flex flex-column">
                                    <span>${data.display}</span>
                                    <small class="${textClass} fw-bold">${data.human}</small>
                                </div>`;
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        return `
                            <span class="badge bg-${data.color} bg-opacity-10 text-${data.color} border border-${data.color} border-opacity-25 px-2 py-1"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                data-bs-custom-class="custom-tooltip"
                                title="${data.description}"
                                style="cursor: help;">
                                <i class="ti ${data.icon} me-1"></i>${data.label}
                            </span>`;
                    }
                },

                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        // Coordenadas de los objetivos
                        const viewUrl = `/rfq/${row.id}`;
                        const comparisonUrl = `/rfq/${row.id}/comparison`;

                        return `
                            <div class="btn-group" role="group">
                                <a href="${viewUrl}" 
                                class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="tooltip" 
                                title="Ver Detalle de RFQ">
                                    <i class="ti ti-eye fs-16"></i>
                                </a>
                                
                                <a href="${comparisonUrl}" 
                                class="btn btn-sm btn-outline-success" 
                                data-bs-toggle="tooltip" 
                                title="Abrir Cuadro Comparativo">
                                    <i class="ti ti-scale fs-16"></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            drawCallback: function() {
                // 🎯 DISPARADOR: Inicializa todos los tooltips nuevos en la tabla
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('#rfq-pending-table [data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            },
            order: [[4, 'asc']] // Ordenar por fecha de vencimiento
        });
    });

    function cancelRfq(id) {
        Swal.fire({
            title: '¿Cancelar RFQ?',
            text: "Esta acción notificará a los proveedores invitados y detendrá el proceso.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-trash me-1"></i> Sí, cancelar',
            cancelButtonText: 'Regresar',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-outline-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí tu lógica AJAX o submit de formulario
                Swal.fire('Procesando', 'La solicitud se está cancelando...', 'info');
            }
        });
    }

    function loadRfqModal(id) {
        $('#infoAjaxModal').modal('show');
        $('#modal-loader-content').load(`/rfq/inbox/modal-rfq/${id}`);
    }

    function loadReqModal(id) {
        $('#infoAjaxModal').modal('show');
        $('#modal-loader-content').load(`/rfq/inbox/modal-req/${id}`);
    }
</script>
@endpush