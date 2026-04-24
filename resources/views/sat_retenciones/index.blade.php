@extends('layouts.zircos')

@section('title', 'Retenciones SAT')
@section('page.title', 'Retenciones SAT')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Retenciones SAT</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ti ti-receipt-tax me-1"></i> Retenciones SAT</h5>
    </div>

    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table id="satRetencionesTable" class="table table-bordered table-hover w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px;">Clave</th>
                        <th>Nombre</th>
                        <th style="width: 80px;" class="text-center">Impuesto</th>
                        <th style="width: 160px;">Porcentaje</th>
                        <th style="width: 90px;" class="text-center">CFDI Ret.</th>
                        <th style="width: 80px;" class="text-center">Activo</th>
                        <th style="width: 100px;" class="text-end">Acciones</th>
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
    $(document).on('click', '.js-delete-btn', function (e) {
        e.preventDefault();
        const btn    = $(this);
        const form   = btn.closest('.js-delete-form');
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

    $(function () {
        $('#satRetencionesTable').DataTable({
            responsive: false,
            processing: true,
            dom: '<"top"Bf>rt<"bottom"lip>',
            pageLength: 50,
            buttons: [
                {
                    text: '<i class="ti ti-plus me-1"></i> Nueva retención',
                    className: 'btn btn-primary btn-sm',
                    action: function () {
                        window.location.href = "{{ route('sat-retenciones.create') }}";
                    }
                },
                { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'copy',  text: '<i class="ti ti-copy me-1"></i> Copy',              className: 'btn btn-warning btn-sm' },
                { extend: 'pdf',   text: '<i class="ti ti-file-text me-1"></i> PDF',           className: 'btn btn-info btn-sm', orientation: 'landscape', pageSize: 'A4' }
            ],
            ajax: {
                url: "{{ route('sat-retenciones.datatable') }}",
                type: 'GET',
                error: function (xhr) {
                    console.error('Error en DataTable:', xhr.responseText);
                }
            },
            columns: [
                { data: 'clave',                   name: 'clave',                   width: '90px' },
                { data: 'nombre',                  name: 'nombre' },
                { data: 'impuesto',                name: 'impuesto',                orderable: false, searchable: false, className: 'text-center' },
                { data: 'porcentaje_display',      name: 'porcentaje_display',      width: '160px' },
                { data: 'requiere_cfdi_retencion', name: 'requiere_cfdi_retencion', orderable: false, searchable: false, className: 'text-center' },
                { data: 'activo',                  name: 'activo',                  orderable: false, searchable: false, className: 'text-center' },
                { data: 'actions',                 name: 'actions',                 orderable: false, searchable: false, className: 'text-end' }
            ],
            language: { url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}" },
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });
    });
</script>
@endpush
