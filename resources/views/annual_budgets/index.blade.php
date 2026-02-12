@extends('layouts.zircos')

@section('title', 'Presupuestos Anuales')

@section('page.title', 'Presupuestos Anuales')

@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item active">Presupuestos Anuales</li>
@endsection

@section('content')
<!-- ===== HEADER CON BOTÓN =====  -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">
            <i class="ti ti-calendar-dollar me-2"></i>
            Gestión de presupuestos por año fiscal
        </p>
    </div>
    <div>
        <a href="{{ route('annual_budgets.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Nuevo Presupuesto
        </a>
    </div>
</div>

<!-- ===== FILTROS ===== -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Año Fiscal</label>
                <select id="filterFiscalYear" class="form-select-sm form-select">
                    <option value="">-- Todos --</option>
                    @for ($year = now()->year - 1; $year <= now()->year + 5; $year++)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select id="filterStatus" class="form-select-sm form-select">
                    <option value="">-- Todos --</option>
                    <option value="PLANIFICACION">En Planificación</option>
                    <option value="APROBADO">Aprobado</option>
                    <option value="CERRADO">Cerrado</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" id="btnReset">
                    <i class="ti ti-refresh me-1"></i> Limpiar Filtros
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABLA DATATABLE ===== -->
<div class="card">
    <div class="card-body table-responsive">
        <table id="tableBudgets" class="table-hover table-striped mb-0 table w-100">
            <thead class="table-light">
                <tr>
                    <th>Empresa</th>
                    <th>Centro de Costo</th>
                    <th>Año</th>
                    <th>Monto Anual</th>
                    <th>Estado</th>
                    <th>Aprobado Por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = new DataTable('#tableBudgets', {
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('annual_budgets.datatable')),
                data: function(d) {
                    d.fiscal_year = document.getElementById('filterFiscalYear').value;
                    d.status = document.getElementById('filterStatus').value;
                }
            },
            columns: [{
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'cost_center_label',
                    name: 'cost_center_label'
                },
                {
                    data: 'fiscal_year',
                    name: 'fiscal_year'
                },
                {
                    data: 'total_annual_amount',
                    name: 'total_annual_amount'
                },
                {
                    data: 'status_label',
                    name: 'status'
                },
                {
                    data: 'approved_by_name',
                    name: 'approved_by_name'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                },
            ],
            order: [
                [2, 'desc'],
                [0, 'asc']
            ],
            pageLength: 25,
            language: {
                url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
            },
            initComplete: function() {
                // Agregar estilos personalizados después de inicializar
                $('.dataTables_wrapper').addClass('mt-3');
                $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').addClass('mb-3');
            },
            drawCallback: function() {
                // Asegurar que los tooltips se inicialicen después de cada dibujo
                $('[data-bs-toggle="tooltip"]').tooltip({
                    boundary: document.body,
                    html: true,
                    sanitize: false
                });
            }
        });

        // ===== FILTROS =====
        document.getElementById('filterFiscalYear').addEventListener('change', () => table.draw());
        document.getElementById('filterStatus').addEventListener('change', () => table.draw());

        document.getElementById('btnReset').addEventListener('click', function() {
            document.getElementById('filterFiscalYear').value = '';
            document.getElementById('filterStatus').value = '';
            table.draw();
        });

        // ===== ELIMINAR CON CONFIRMACIÓN =====
        document.addEventListener('click', function(e) {
            if (e.target.closest('.js-delete-btn')) {
                const btn = e.target.closest('.js-delete-btn');
                const entity = btn.dataset.entity;
                const form = btn.closest('.js-delete-form');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Se eliminará: ${entity}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="ti ti-trash me-1"></i>Sí, eliminar',
                    cancelButtonText: '<i class="ti ti-x me-1"></i>Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    });
</script>
@endpush