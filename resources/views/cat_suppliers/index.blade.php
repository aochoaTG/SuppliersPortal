@extends('layouts.zircos')

{{-- Título de la página (navegador) --}}
@section('title', 'Catálogo de Proveedores')

{{-- Encabezado de la página --}}
@section('page.title', 'Catálogo de Proveedores')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Proveedores</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Proveedores</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table-bordered table-hover w-100 table" id="suppliersTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Origen</th>
                        <th>Empresa</th>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Código Postal</th>
                        <th>Correo electrónico</th>
                        <th>Banco</th>
                        <th>Número de Cuenta</th>
                        <th>CLABE</th>
                        <th>Moneda</th>
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
document.addEventListener('DOMContentLoaded', function () {

    function badgeActivo(val) {
        return val
            ? '<span class="badge bg-success">Sí</span>'
            : '<span class="badge bg-secondary">No</span>';
    }

    new DataTable('#suppliersTable', {
        ajax: {
            url: "{{ route('cat-suppliers.datatable') }}", // 👉 necesitarás esta ruta en el controlador
            dataSrc: 'data'
        },
        paging: true,
        searching: true,
        ordering: true,
        responsive: false,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
        order: [[0, 'asc']], // ID

        columns: [
            { data: 'id', name: 'id'},
            { data: 'source_system', name: 'source_system'},
            { data: 'source_company', name: 'source_company'},
            { data: 'name', name: 'name'},
            { data: 'rfc', name: 'rfc', render: d => `<code>${d ?? ''}</code>`},
            { data: 'postal_code', name: 'postal_code'},
            { data: 'email', name: 'email'},
            { data: 'bank', name: 'bank'},
            { data: 'account_number', name: 'account_number'},
            { data: 'clabe', name: 'clabe'},
            { data: 'currency', name: 'currency'},
        ],
        dom: '<"top"Bf>rt<"bottom"lip>',
        language: {
            url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
        },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        }
    });
});
</script>
@endpush
