@extends('layouts.zircos')

@section('title', 'Niveles de Autorización')
@section('page.title', 'Configuración de Niveles de Autorización')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Niveles de Autorización</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary"><i class="ti ti-shield-lock me-1"></i>Jerarquía de Aprobación</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-bordered table-hover w-100 table" id="approval-levels-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Nivel</th>
                                <th>Aprobador (Etiqueta)</th>
                                <th>Rango Mínimo</th>
                                <th>Rango Máximo</th>
                                {{-- 👇 NUEVA COLUMNA AGREGADA --}}
                                <th style="min-width: 200px;">Descripción del Rango</th>
                                <th>Estado Visual</th>
                                <th style="width: 80px;">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#approval-levels-table').DataTable({
            processing: true,
            serverSide: false,
            dom: '<"top"Bf>rt<"bottom"lip>',
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
                }
            ],
            data: @json($levels),
            columns: [
                { data: 'level_number', className: 'fw-bold text-center' },
                { data: 'label', className: 'fw-semibold' },
                { 
                    data: 'min_amount', 
                    render: function(data) { 
                        return '$' + parseFloat(data).toLocaleString('es-MX', {minimumFractionDigits: 2}); 
                    }
                },
                { 
                    data: 'max_amount', 
                    render: function(data) { 
                        return data ? '$' + parseFloat(data).toLocaleString('es-MX', {minimumFractionDigits: 2}) : '<span class="text-muted italic">Sin límite</span>'; 
                    }
                },
                // 👇 CONFIGURACIÓN DE LA NUEVA COLUMNA
                { 
                    data: 'description',
                    render: function(data) {
                        return data ? `<small class="text-muted">${data}</small>` : '<em class="text-muted small">Sin descripción</em>';
                    }
                },
                { 
                    data: 'color_tag',
                    render: function(data, type, row) {
                        return `<span class="badge bg-soft-${data} text-${data} border border-${data} border-opacity-25 px-2 py-1">
                                    <i class="ti ti-circle-filled fs-10 me-1"></i>${data.toUpperCase()}
                                </span>`;
                    }
                },
                {
                    data: 'id',
                    render: function(data) {
                        return `<div class="btn-group">
                                    <a href="/approval-levels/${data}/edit" class="btn btn-sm btn-outline-primary" title="Editar Rango">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>`;
                    }
                }
            ],
            language: {
                url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });
    });
</script>
@endpush