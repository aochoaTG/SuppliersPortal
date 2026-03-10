@extends('layouts.zircos')

@section('title', 'Ubicaciones de Recepción')

@section('page.title', 'Ubicaciones de Recepción')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Ubicaciones de Recepción</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ubicaciones de Recepción</h5>
        @can('create', App\Models\ReceivingLocation::class)
            <a href="{{ route('receiving-locations.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Nueva Ubicación
            </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="receiving-locations-table" class="table table-sm table-striped align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Ciudad</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Portal</th>
                        <th>Creado</th>
                        <th>Acciones</th>
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
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var table = $('#receiving-locations-table').DataTable({
        ajax: "{{ route('receiving-locations.data') }}",
        columns: [
            { data: 'id',              name: 'id' },
            { data: 'code',            name: 'code' },
            { data: 'name',            name: 'name' },
            { data: 'type',            name: 'type' },
            { data: 'city',            name: 'city' },
            { data: 'manager_name',    name: 'manager_name' },
            { data: 'is_active',       name: 'is_active' },
            { data: 'portal_blocked',  name: 'portal_blocked' },
            { data: 'created_at',      name: 'created_at' },
            { data: 'action',          name: 'action' }
        ],
        order: [[1, 'asc']],
        // Mostrar los 100 primeros registros
        lengthMenu: [100, 200, 500],
        language: {
            url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        }
    });

    // Eliminar
    $(document).on('click', '.btn-delete', function() {
        var id   = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Eliminar la ubicación "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('receiving-locations') }}/${id}`,
                    type: 'POST',
                    data: { _method: 'DELETE' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Eliminado', text: response.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al eliminar la ubicación', 'error');
                    }
                });
            }
        });
    });

    // Bloquear portal
    $(document).on('click', '.btn-block-portal', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: '¿Bloquear portal?',
            text: 'Los proveedores no podrán registrar nuevas entregas en esta ubicación.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, bloquear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('receiving-locations') }}/${id}/block-portal`,
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Bloqueado', text: response.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // Desbloquear portal
    $(document).on('click', '.btn-unblock-portal', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: '¿Desbloquear portal?',
            text: 'Los proveedores podrán registrar nuevas entregas en esta ubicación.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, desbloquear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('receiving-locations') }}/${id}/unblock-portal`,
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Desbloqueado', text: response.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
