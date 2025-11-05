@extends('layouts.zircos')

@section('title', 'Presupuestos Anuales')
@section('page.title', 'Presupuestos Anuales')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('annual-budgets.index') }}">Presupuestos</a></li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-cash"></i> Presupuestos Anuales</h5>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="annualBudgetsTable" class="table-striped table">
                    <thead>
                        <tr>
                            <th>Compañía</th>
                            <th>Centro de costo</th>
                            <th>Año</th>
                            <th>Asignado</th>
                            <th>Disponible</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- DataTables --}}

    <script>
        $(function() {
            const table = $('#annualBudgetsTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                buttons: [{
                        text: '<i class="ti ti-cash me-1"></i> Nuevo presupuesto',
                        className: 'btn btn-primary btn-sm',
                        attr: {
                            id: 'btnCreateAnnualBudget',
                            title: 'Crear nuevo presupuesto'
                        },
                        action: function() {
                            window.location.href = "{{ route('annual-budgets.create') }}";
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'copy',
                        text: '<i class="ti ti-copy me-1"></i> Copiar',
                        className: 'btn btn-warning btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="ti ti-file-text me-1"></i> PDF',
                        className: 'btn btn-info btn-sm',
                        orientation: 'portrait',
                        pageSize: 'A4'
                    }
                ],
                ajax: {
                    url: "{{ route('annual-budgets.datatable') }}",
                    type: "GET",
                    error: function(xhr) {
                        console.error('Error en DataTable:', xhr.responseText);
                        alert('Error al cargar los datos. Revisa la consola.');
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
                        data: 'amount_assigned',
                        name: 'amount_assigned'
                    },
                    {
                        data: 'amount_available',
                        name: 'amount_available',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                order: [
                    [2, 'desc']
                ], // por año
                language: {
                    url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            });

            // Delegación para SweetAlert en eliminación
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-delete-btn');
                if (!btn) return;
                e.preventDefault();

                const form = btn.closest('form');
                const nombre = btn.getAttribute('data-entity') || 'este presupuesto';

                Swal.fire({
                    title: '¿Eliminar?',
                    text: `Vas a eliminar ${nombre}. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush
