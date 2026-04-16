@extends('layouts.zircos')

@section('title', 'Empleados')
@section('page.title', 'Empleados')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Empleados</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-id-badge me-1"></i> Catálogo de Empleados</h5>
            <small class="text-muted">Sincronizado diariamente desde TRESS</small>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="employeesTable" class="table table-bordered table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>No. Empleado</th>
                            <th>Nombre Completo</th>
                            <th>Empresa</th>
                            <th>Departamento</th>
                            <th>Puesto</th>
                            <th>Líder</th>
                            <th class="text-center" style="width: 80px;">Activo</th>
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
        $(function () {
            $('#employeesTable').DataTable({
                responsive: false,
                processing: true,
                serverSide: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                order: [[1, 'asc']],
                buttons: [
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
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ],
                ajax: {
                    url: "{{ route('employees.datatable') }}",
                    type: "GET",
                    error: function (xhr) {
                        console.error('Error en DataTable:', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'id',              name: 'id',              width: '60px' },
                    { data: 'employee_number', name: 'employee_number' },
                    { data: 'full_name',       name: 'full_name',       searchable: false, orderable: false },
                    { data: 'company',         name: 'company' },
                    { data: 'department',      name: 'department' },
                    { data: 'job_title',       name: 'job_title' },
                    { data: 'leader',          name: 'leader' },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            });
        });
    </script>
@endpush
