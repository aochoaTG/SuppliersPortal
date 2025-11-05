@extends('layouts.zircos')

@section('title', 'Categorias - CC')
@section('page.title', 'Categorias - CC')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categorias</a></li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@push('styles')
    {{-- Estilos de DataTables --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-category"></i> Categorias</h5>
        </div>

        <div class="card-body">
            {{-- Mensaje flash de éxito --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Tabla DataTable --}}
            <div class="table-responsive">
                <table id="categoriesTable" class="table-bordered table-hover w-100 table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Activo</th>
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
        $(document).on('click', '.delete-confirm', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });


        $(function() {
            // === Inicialización del DataTable ===
            const table = $('#categoriesTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                buttons: [{
                        text: '<i class="ti ti-category me-1"></i> Nueva categoria',
                        className: 'btn btn-primary btn-sm',
                        attr: {
                            id: 'btnCreateCategory',
                            title: 'Nueva categoria'
                        },
                        action: function() {
                            window.location.href = "{{ route('categories.create') }}";
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'copy',
                        text: '<i class="ti ti-copy me-1"></i> Copy',
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
                    url: "{{ route('categories.datatable') }}",
                    type: "GET",
                    error: function(xhr) {
                        console.error('Error en DataTable:', xhr.responseText);
                        alert('Error al cargar los datos. Revisa la consola.');
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
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
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
