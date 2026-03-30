@extends('layouts.zircos')

@section('title', 'Departamentos')
@section('page.title', 'Departamentos')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Departamentos</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-building me-1"></i> Departamentos</h5>
        </div>

        <div class="card-body">
            {{-- Flash --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="departmentsTable" class="table-bordered table-hover w-100 table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Nombre</th>
                            <th>Abreviación</th>
                            <th class="text-center">Activo</th>
                            <th>Notas</th>
                            <th class="text-end" style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.js-delete-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const form = btn.closest('.js-delete-form');
            const entity = btn.data('entity') || 'este registro';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminará: ${entity}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ti ti-trash me-1"></i>Sí, eliminar',
                cancelButtonText: '<i class="ti ti-x me-1"></i>Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        $(function() {
            $('#departmentsTable').DataTable({
                responsive: false,
                processing: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                buttons: [{
                        text: '<i class="ti ti-building-plus me-1"></i> Nuevo departamento',
                        className: 'btn btn-primary btn-sm',
                        attr: {
                            id: 'btnCreateDepartment',
                            title: 'Crear nuevo departamento'
                        },
                        action: function() {
                            window.location.href = "{{ route('departments.create') }}";
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
                    url: "{{ route('departments.datatable') }}",
                    type: "GET",
                    error: function(xhr) {
                        console.error('Error en DataTable:', xhr.responseText);
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        width: '60px'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'abbreviated',
                        name: 'abbreviated'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                language: {
                    url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            });
        });
    </script>
@endpush
