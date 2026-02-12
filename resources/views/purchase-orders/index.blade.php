@extends('layouts.zircos')

@section('page.title', 'Órdenes de Compra')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            {{-- Header con botón de Nueva OCD --}}
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="ti ti-shopping-cart me-1"></i>Órdenes de Compra
                </h5>
                <a href="{{ route('direct-purchase-orders.create') }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus me-1"></i>Nueva Orden de Compra Directa
                </a>
            </div>

            <div class="card-body">
                {{-- Tabs de Navegación --}}
                <ul class="nav nav-tabs nav-bordered mb-3" id="orderTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" 
                                id="regular-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#regular" 
                                type="button" 
                                role="tab" 
                                aria-controls="regular" 
                                aria-selected="true">
                            <i class="ti ti-list me-1"></i>
                            Órdenes Regulares 
                            <span class="badge bg-soft-primary text-primary ms-1">{{ $regularCount }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" 
                                id="direct-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#direct" 
                                type="button" 
                                role="tab" 
                                aria-controls="direct" 
                                aria-selected="false">
                            <i class="ti ti-bolt me-1"></i>
                            Órdenes Directas 
                            <span class="badge bg-soft-warning text-warning ms-1">{{ $directCount }}</span>
                        </button>
                    </li>
                </ul>

                {{-- Contenido de los Tabs --}}
                <div class="tab-content" id="orderTabsContent">
                    
                    {{-- TAB 1: ÓRDENES REGULARES --}}
                    <div class="tab-pane fade show active" 
                         id="regular" 
                         role="tabpanel" 
                         aria-labelledby="regular-tab">
                        
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="ti ti-info-circle me-1"></i>
                            <strong>Órdenes Regulares:</strong> Generadas desde el proceso de Requisiciones → Cotizaciones → Aprobación.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-centered table-nowrap mb-0" 
                                   id="regular-orders-table" 
                                   style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Folio</th>
                                        <th>Fecha Emisión</th>
                                        <th>Proveedor</th>
                                        <th>Requisición</th>
                                        <th>Total (MXN)</th>
                                        <th>Estado</th>
                                        <th width="100px">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 2: ÓRDENES DIRECTAS --}}
                    <div class="tab-pane fade" 
                         id="direct" 
                         role="tabpanel" 
                         aria-labelledby="direct-tab">
                        
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="ti ti-bolt me-1"></i>
                            <strong>Órdenes Directas:</strong> Compras directas a proveedor específico sin proceso de cotización competitiva.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-centered table-nowrap mb-0" 
                                   id="direct-orders-table" 
                                   style="width: 100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Folio</th>
                                        <th>Fecha Solicitud</th>
                                        <th>Proveedor</th>
                                        <th>Solicitante</th>
                                        <th>Centro de Costo</th>
                                        <th>Total (MXN)</th>
                                        <th>Estado</th>
                                        <th width="100px">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // ==========================================
    // DataTable: ÓRDENES REGULARES
    // ==========================================
    const regularTable = $('#regular-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('purchase-orders.datatable.regular') }}",
            type: 'GET'
        },
        columns: [
            { data: 'folio', name: 'folio', orderable: true },
            { data: 'fecha_emision', name: 'created_at', orderable: true },
            { data: 'proveedor', name: 'supplier.company_name', orderable: true },
            { data: 'requisicion', name: 'requisition.folio', orderable: true },
            { data: 'total', name: 'total', orderable: true },
            { data: 'status', name: 'status', orderable: true },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']], // Ordenar por fecha descendente
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
        },
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    });

    // ==========================================
    // DataTable: ÓRDENES DIRECTAS
    // ==========================================
    let directTable = null; // Se inicializa cuando se activa el tab

    // Inicializar DataTable de OCD cuando se activa su tab
    $('#direct-tab').on('shown.bs.tab', function (e) {
        if (directTable === null) {
            directTable = $('#direct-orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('purchase-orders.datatable.direct') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'folio', name: 'folio', orderable: true },
                    { data: 'fecha_solicitud', name: 'created_at', orderable: true },
                    { data: 'proveedor', name: 'supplier.company_name', orderable: true },
                    { data: 'solicitante', name: 'creator.name', orderable: true },
                    { data: 'centro_costo', name: 'costCenter.name', orderable: true },
                    { data: 'total', name: 'total', orderable: true },
                    { data: 'status', name: 'status', orderable: true },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']], // Ordenar por fecha descendente
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
                },
                pageLength: 25,
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            });
        } else {
            // Si ya existe, solo redibujamos
            directTable.draw();
        }
    });

    // Ajustar columnas cuando cambia de tab
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

});
</script>
@endpush