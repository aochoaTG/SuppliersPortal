@extends('layouts.zircos')

{{-- Título de la página (navegador) --}}
@section('title', 'Listado EFOS 69-B')

{{-- Encabezado de la página --}}
@section('page.title', 'Listado EFOS 69-B')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Listado EFOS 69-B</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">RFC listados en EFOS 69-B</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle w-100" id="efosTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>RFC</th>
                        <th>Razón Social</th>
                        <th>Situación</th>
                        <th>Presunción SAT</th>
                        <th>Presunción DOF</th>
                        <th>Definitivo SAT</th>
                        <th>Definitivo DOF</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Utilidad simple para formatear fechas ISO -> dd/mm/yyyy
        function fmt(d) {
            if (!d) return '—';
            const dt = new Date(d);
            if (isNaN(dt)) return d; // por si llega como string dd/mm/yyyy ya formateado
            const dd = String(dt.getDate()).padStart(2,'0');
            const mm = String(dt.getMonth()+1).padStart(2,'0');
            const yy = dt.getFullYear();
            return `${dd}/${mm}/${yy}`;
        }

        // Badge de situación
        function badge(sit) {
            const s = (sit || '').toString().toUpperCase();
            let cls = 'secondary';
            if (s.includes('DEFINITIVO')) cls = 'danger';
            else if (s.includes('PRESUNTO')) cls = 'warning';
            else if (s.includes('SENTENCIA FAVORABLE')) cls = 'primary';
            return `<span class="badge bg-${cls}">${sit ?? ''}</span>`;
        }

        new DataTable('#efosTable', {
            ajax: {
                url: "{{ route('sat_efos_69b.data') }}",
                dataSrc: 'data'
            },
            paging: true,
            searching: true,
            ordering: true,
            responsive: false,
            lengthMenu: [10, 25, 50, 100],
            pageLength: 100,
            order: [[1, 'asc']], // RFC
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Comunicados_' + new Date().toISOString().split('T')[0]
                }
            ],
            dom: '<"top"Bf>rt<"bottom"lip>',
            columns: [
                { data: 'number',  name: 'number' },
                { data: 'rfc',     name: 'rfc', render: d => `<code>${d ?? ''}</code>` },
                { data: 'company_name', name: 'company_name' },
                { data: 'situation',    name: 'situation', render: badge },
                { data: 'sat_presumption_notice_date', name: 'sat_presumption_notice_date', render: d => d || '—' },
                { data: 'dof_presumption_notice_date', name: 'dof_presumption_notice_date', render: d => d || '—' },
                { data: 'sat_definitive_publication_date', name: 'sat_definitive_publication_date', render: fmt },
                { data: 'dof_definitive_publication_date', name: 'dof_definitive_publication_date', render: fmt },
            ],
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
