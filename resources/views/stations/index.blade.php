{{-- resources/views/stations/index.blade.php --}}
@extends('layouts.zircos')

@section('title', 'Estaciones')

@push('styles')
    <style>
        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
    </style>
@endpush

@section('page.title', 'Estaciones')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
    <li class="breadcrumb-item active">Estaciones</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-gas-station me-1"></i> Listado de Estaciones</h5>
        </div>
        <div class="card-body">
            <table class="table-sm table-striped w-100 table align-middle" id="stationsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estación</th>
                        <th>Empresa</th>
                        <th>Estado/Municipio</th>
                        <th>Permiso CRE</th>
                        <th>Sistema / ID</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Modal genérico --}}
    <div class="modal fade" id="stationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" id="stationModalContent">
                {{-- Aquí se inyecta stations.partials.form vía AJAX --}}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#stationsTable').DataTable({
                responsive: false,
                processing: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                buttons: [{
                        text: '<i class="ti ti-gas-station me-1"></i> Nueva estación',
                        className: 'btn btn-primary btn-sm',
                        attr: {
                            id: 'btnCreateStation',
                            title: 'Crear nueva estación'
                        },
                        action: function() {
                            openStationModal("{{ route('stations.create') }}");
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
                    url: "{{ route('stations.datatable') }}",
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
                        data: 'station_name',
                        name: 'station_name'
                    },
                    {
                        data: 'company',
                        name: 'company',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'state_mun',
                        name: 'state_mun',
                        orderable: false
                    },
                    {
                        data: 'cre_permit',
                        name: 'cre_permit'
                    },
                    {
                        data: 'sys_external',
                        name: 'sys_external',
                        orderable: false
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            });

            // Crear
            $(document).on('click', '#btnCreateStation', function(e) {
                e.preventDefault();
                openStationModal("{{ route('stations.create') }}");
            });

            // Editar (botón con data-url)
            $(document).on('click', '.js-open-station-modal', function(e) {
                e.preventDefault();
                openStationModal($(this).data('url'));
            });

            function openStationModal(url) {
                const el = document.getElementById('stationModal');
                const modal = bootstrap.Modal.getOrCreateInstance(el);

                $('#stationModalContent').html('<div class="p-5 text-center">Cargando...</div>');
                modal.show();

                $.get(url)
                    .done(function(html) {
                        $('#stationModalContent').html(html);
                        initEnhancers();
                    })
                    .fail(function() {
                        $('#stationModalContent').html(
                            '<div class="p-5 text-danger">No se pudo cargar el formulario.</div>');
                    });
            }

            // Submit del form (create/update)
            $(document).on('submit', '#stationForm', function(e) {
                e.preventDefault();
                const $form = $(this);
                const action = $form.attr('action');
                const method = ($form.attr('method') || 'POST').toUpperCase();
                const data = $form.serialize();

                $form.find('button[type="submit"]').prop('disabled', true);
                $('#formErrors').addClass('d-none').empty();

                $.ajax({
                        url: action,
                        type: method,
                        data: data
                    })
                    .done(function() {
                        const el = document.getElementById('stationModal');
                        const modal = bootstrap.Modal.getInstance(el);
                        if (modal) modal.hide();

                        $('#stationModal').one('hidden.bs.modal', function() {
                            $('#stationModalContent').empty();
                            table.ajax.reload(null, false);
                            if (typeof toastOk === 'function') toastOk(
                                'Guardado correctamente');
                            cleanupModalBackdrops();
                        });
                    })
                    .fail(function(xhr) {
                        $form.find('button[type="submit"]').prop('disabled', false);
                        if (xhr.status === 422) {
                            const res = xhr.responseJSON;
                            let html = '<div class="alert alert-danger"><ul class="mb-0">';
                            (res.errors ? Object.values(res.errors) : [
                                ['Error de validación']
                            ])
                            .forEach(arr => arr.forEach(msg => html += `<li>${msg}</li>`));
                            html += '</ul></div>';
                            $('#formErrors').html(html).removeClass('d-none');
                        } else {
                            $('#formErrors').html(
                                    '<div class="alert alert-danger">Error inesperado.</div>')
                                .removeClass('d-none');
                        }
                    });
            });

            // Desactivar (delete)
            $(document).on('click', '.js-delete-station', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                const name = $(this).data('name') || 'esta estación';

                Swal.fire({
                    title: `¿Desactivar ${name}?`,
                    text: "Se marcará como inactiva.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                                url,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE'
                                }
                            })
                            .done(function() {
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Listo',
                                    text: 'Estación desactivada',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            })
                            .fail(function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo desactivar.'
                                });
                            });
                    }
                });
            });

            // Toggle activo
            $(document).on('submit', '.js-toggle-station-active', function(e) {
                e.preventDefault();
                const $form = $(this);
                const url = $form.attr('action') || $form.find('button[formaction]').attr(
                    'formaction');
                $.ajax({
                        url,
                        type: 'POST',
                        data: $form.serialize()
                    })
                    .done(function() {
                        table.ajax.reload(null, false);
                        if (typeof toastOk === 'function') toastOk('Estado cambiado');
                    })
                    .fail(function() {
                        alert('No se pudo cambiar el estado.');
                    });
            });

            // Limpieza de backdrops
            $('#stationModal').on('hidden.bs.modal', function() {
                cleanupModalBackdrops();
            });

            function cleanupModalBackdrops() {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                $('.modal-backdrop').remove();
            }

            // Helpers
            window.toastOk = window.toastOk || function(msg = 'Operación exitosa') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: msg
                });
            };

            function initEnhancers() {
                $('.select2').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endpush
