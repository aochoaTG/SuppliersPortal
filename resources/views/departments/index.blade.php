@extends('layouts.zircos')

@section('title', 'Departamentos')
@section('page.title', 'Departamentos')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departamentos</a></li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-building"></i> Departamentos</h5>
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
                <table id="departmentsTable" class="table-striped table-bordered table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Abreviación</th>
                            <th>Activo</th>
                            <th>Notas</th>
                            <th style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $d)
                            <tr>
                                <td>{{ $d->id }}</td>
                                <td>{{ $d->name }}</td>
                                <td><span class="badge bg-secondary">{{ $d->abbreviated }}</span></td>
                                <td>
                                    @if ($d->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $d->notes }}</td>
                                <td class="text-center">
                                    <a href="{{ route('departments.edit', $d) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('departments.destroy', $d) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este departamento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Abreviación</th>
                            <th>Activo</th>
                            <th>Notas</th>
                            <th>Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#departmentsTable').DataTable({
                responsive: true,
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
