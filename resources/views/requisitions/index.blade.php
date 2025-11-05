@extends('layouts.zircos')

@section('title', 'Requisiciones')
@section('page.title', 'Requisiciones')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisiciones</a></li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-file-dollar"></i> Requisiciones</h5>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="requisitionsTable" class="table-bordered table-hover w-100 table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Folio</th>
                            <th>Año</th>
                            <th>Centro de costo</th>
                            <th>Solicitante</th>
                            <th>Fecha requerida</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Estatus</th>
                            <th class="text-end" style="width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/pdfmake/vfs_fonts.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            const table = $('#requisitionsTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                buttons: [{
                        text: '<i class="ti ti-file-dollar me-1"></i> Nueva requisición',
                        className: 'btn btn-primary btn-sm',
                        action: function() {
                            window.location.href = "{{ route('requisitions.create') }}";
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
                    url: "{{ route('requisitions.datatable') }}",
                    type: "GET",
                    error: function(xhr) {
                        console.error('Error en DataTable:', xhr.responseText);
                        alert('Error al cargar los datos.');
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        width: '60px'
                    },
                    {
                        data: 'folio',
                        name: 'folio'
                    },
                    {
                        data: 'fiscal_year',
                        name: 'fiscal_year'
                    },
                    {
                        data: 'cost_center',
                        name: 'costCenter.name'
                    },
                    {
                        data: 'requester',
                        name: 'requested_by'
                    },
                    {
                        data: 'required_date',
                        name: 'required_date'
                    },
                    {
                        data: 'amount_requested',
                        name: 'amount_requested'
                    },
                    {
                        data: 'currency_code',
                        name: 'currency_code'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
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

            // SweetAlert para eliminar
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-delete-btn');
                if (!btn) return;
                e.preventDefault();
                const form = btn.closest('form');
                const nombre = btn.getAttribute('data-entity') || 'esta requisición';
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
                }).then((res) => {
                    if (res.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush
