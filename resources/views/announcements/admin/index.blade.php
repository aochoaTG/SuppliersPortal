@extends('layouts.zircos')

{{-- TÍTULO DE LA PÁGINA       --}}
@section('title', 'Gestión de Comunicados')

{{-- CSS ADICIONAL (opcional)  --}}
@push('styles')
    {{-- Estilos específicos para comunicados si los necesitas --}}
    <style>
        .comunicado-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .priority-badge {
            font-size: 0.75rem;
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
@endpush

@section('page.title', 'Gestión de Comunicados')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Portal Proveedores</a></li>
    <li class="breadcrumb-item active">Comunicados</li>
@endsection

{{-- CONTENIDO PRINCIPAL       --}}
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            {{-- Título del listado --}}
            <h5 class="mb-0">
                <i class="ti ti-speakerphone me-2"></i>
                Comunicados del Portal
            </h5>
            {{-- Stats rápidas (opcional) --}}
            <div class="d-flex gap-2">
                <span class="badge bg-primary" id="totalComunicados">0 Totales</span>
            </div>
        </div>
        <div class="card-body">
            {{-- Filtros rápidos --}}
            <div class="row mb-3">
                {{-- Estado --}}
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                {{-- Prioridad --}}
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="filtroPrioridad">
                        <option value="">Todas las prioridades</option>
                        <option value="baja">Baja 🟢</option>
                        <option value="normal">Normal 🔵</option>
                        <option value="alta">Alta 🟡</option>
                        <option value="urgente">Urgente 🔴</option>
                    </select>
                </div>

                {{-- Botón limpiar --}}
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarFiltros">
                        <i class="ti ti-filter-off me-1"></i>
                        Limpiar filtros
                    </button>
                </div>
            </div>

            {{-- Tabla de comunicados --}}
            <table class="table table-sm table-striped align-middle w-100" id="comunicadosTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th width="250">Título</th>
                        <th width="300">Contenido</th>
                        <th width="100">Prioridad</th>
                        <th width="100">Estado</th>
                        <th width="120">Fecha</th>
                        <th width="80">Vistas</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Los datos se cargan vía DataTable AJAX --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal genérico para crear comunicados --}}
    <div class="modal fade" id="comunicadoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="comunicadoModalContent">
                {{-- aquí se inyecta comunicados.partials.form vía AJAX --}}
            </div>
        </div>
    </div>

    {{-- Modal genérico para editar comunicados --}}
    <div class="modal fade" id="comunicadoEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="comunicadoEditModalContent">
                {{-- aquí se inyecta comunicados.partials.form vía AJAX --}}
            </div>
        </div>
    </div>


    {{-- Modal para vista previa --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-eye me-2"></i>
                        Vista Previa del Comunicado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    {{-- Contenido de vista previa --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

{{-- JS ADICIONAL   --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // CSRF para AJAX
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // DataTable de comunicados
    const table = $('#comunicadosTable').DataTable({
        responsive: false,
        processing: true,
        dom: '<"top"Bf>rt<"bottom"lip>',
        pageLength: 25,
        buttons: [
            {
                text: '<i class="ti ti-speakerphone me-1"></i> Nuevo Comunicado',
                className: 'btn btn-primary btn-sm',
                attr: { id: 'btnCreateComunicado', title: 'Crear nuevo comunicado' },
                action: function (e, dt, node, config) {
                    openComunicadoModal("{{ route('admin.announcements.create') }}");
                }
            },
            {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Comunicados_' + new Date().toISOString().split('T')[0]
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
                pageSize: 'A4',
                title: 'Listado de Comunicados'
            }
        ],
        ajax: {
            url: "{{ route('admin.announcements.datatable') }}",
            data: function (d) {
                d.estado = $('#filtroEstado').val();
                d.prioridad = $('#filtroPrioridad').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            {
                data: 'titulo',
                name: 'titulo',
                render: function(data, type, row) {
                    let html = `<div class="fw-bold">${data}</div>`;
                    if (row.es_importante) {
                        html += '<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>Importante</small>';
                    }
                    return html;
                }
            },
            {
                data: 'contenido',
                name: 'contenido',
                render: function(data) {
                    return `<div class="comunicado-preview" title="${data}">${data}</div>`;
                }
            },
            {
                data: 'prioridad',
                name: 'prioridad',
                render: function(data) {
                    const colors = {
                        'baja': 'success',   // verde
                        'normal': 'primary', // azul
                        'alta': 'warning',   // amarillo
                        'urgente': 'danger'  // rojo
                    };
                    const icons = {
                        'baja': 'info-circle',
                        'normal': 'circle',
                        'alta': 'alert-triangle',
                        'urgente': 'flame'
                    };
                    const key   = (data || '').toLowerCase();
                    const label = key.toUpperCase();
                    const color = colors[key] || 'secondary';
                    const icon  = icons[key] || 'help-circle';
                    return `<span class="badge bg-${color} priority-badge">
                        <i class="ti ti-${icon} me-1"></i>${label}
                    </span>`;
                }
            },
            {
                data: 'estado',
                name: 'estado',
                render: function(data) {
                    const configs = {
                        'activo':   { color: 'success', icon: 'check-circle', text: 'Activo' },
                        'inactivo': { color: 'danger',  icon: 'x-circle',     text: 'Inactivo' }
                    };
                    const config = configs[data] || { color: 'secondary', icon: 'help-circle', text: data };
                    return `<span class="badge bg-${config.color}">
                        <i class="ti ti-${config.icon} me-1"></i>${config.text}
                    </span>`;
                }
            },
            {
                data: 'fecha_publicacion',
                name: 'fecha_publicacion',
                render: function(data) {
                    if (!data) return '<small class="text-muted">Sin fecha</small>';
                    const fecha = new Date(data);
                    return `<small>${fecha.toLocaleDateString('es-MX', {
                        day: '2-digit', month: '2-digit', year: 'numeric'
                    })}</small>`;
                }
            },
            {
                data: 'vistas',
                name: 'vistas',
                render: function(data) {
                    return `<span class="badge bg-light text-dark">
                        <i class="ti ti-eye me-1"></i>${data || 0}
                    </span>`;
                }
            },
            {
                data: 'acciones',
                name: 'acciones',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
        },
        drawCallback: function (settings) {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");

            // Actualizar badges del header
            const info = this.api().page.info();
            $('#totalComunicados').text(`${info.recordsTotal} Totales`);

            // TODO: Actualizar contadores por estado desde el servidor
            // $('#comunicadosActivos').text('X Activos');
            // $('#comunicadosPendientes').text('Y Pendientes');
        },
        order: [[0, 'desc']] // Ordenar por ID descendente (más recientes primero)
    });

    // Manejo del modal
    $('#comunicadoModal').on('hidden.bs.modal', function () {
        cleanupModalBackdrops();
    });

    // Filtros
    $('#filtroEstado, #filtroPrioridad').on('change', function() {
        table.draw();
    });

    $('#btnLimpiarFiltros').on('click', function() {
        $('#filtroEstado, #filtroPrioridad').val('').trigger('change');
    });

    // Abrir modal Crear
    $(document).on('click', '#btnCreateComunicado', function (e) {
        e.preventDefault();
        openComunicadoModal("{{ route('admin.announcements.create') }}");
    });

    // Abrir modal Editar
    $(document).on('click', '.js-open-comunicado-modal', function (e) {
        e.preventDefault();
        openComunicadoModal($(this).data('url'));
    });

    // Vista previa
    $(document).on('click', '.js-preview-comunicado', function (e) {
        e.preventDefault();
        const url = $(this).data('url');

        $('#previewContent').html('<div class="text-center p-4"><div class="spinner-border"></div><div class="mt-2">Cargando...</div></div>');

        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();

        $.get(url)
            .done(function (html) {
                $('#previewContent').html(html);
            })
            .fail(function () {
                $('#previewContent').html('<div class="alert alert-danger">No se pudo cargar la vista previa.</div>');
            });
    });

    function openComunicadoModal(url) {
        const el = document.getElementById('comunicadoModal');
        const modal = bootstrap.Modal.getOrCreateInstance(el);

        $('#comunicadoModalContent').html('<div class="p-5 text-center"><div class="spinner-border"></div><div class="mt-2">Cargando formulario...</div></div>');
        modal.show();

        $.get(url)
            .done(function (html) {
                $('#comunicadoModalContent').html(html);
            })
            .fail(function () {
                $('#comunicadoModalContent').html('<div class="p-5 text-danger">No se pudo cargar el formulario.</div>');
            });
    }

    // Submit del formulario
    $(document).on('submit', '#comunicadoForm', function (e) {
        e.preventDefault();
        const $form = $(this);
        const action = $form.attr('action');
        const formData = new FormData(this); // Para manejar archivos si los hay

        $form.find('button[type="submit"]').prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Guardando...');
        $('#formErrors').addClass('d-none').empty();

        $.ajax({
            url: action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
        .done(function (response) {
            const el = document.getElementById('comunicadoModal');
            const modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();

            $('#comunicadoModal').one('hidden.bs.modal', function () {
                $('#comunicadoModalContent').empty();
                table.ajax.reload(null, false);
                if (typeof toastOk === 'function') {
                    toastOk(response.message || 'Comunicado guardado correctamente');
                } else {
                    alert(response.message || 'Comunicado guardado correctamente');
                }
                cleanupModalBackdrops();
            });
        })
        .fail(function (xhr) {
            $form.find('button[type="submit"]').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Guardar');

            if (xhr.status === 422) {
                const res = xhr.responseJSON;
                let html = '<div class="alert alert-danger"><ul class="mb-0">';
                Object.values(res.errors || {}).forEach(arr =>
                    arr.forEach(msg => html += `<li>${msg}</li>`)
                );
                html += '</ul></div>';
                $('#formErrors').html(html).removeClass('d-none');
            } else {
                $('#formErrors').html('<div class="alert alert-danger">Error inesperado al guardar el comunicado.</div>').removeClass('d-none');
            }
        });
    });

    // Toggle estado activo
    $(document).on('click', '.js-toggle-estado', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $btn = $(this);
        const originalHtml = $btn.html();

        $btn.html('<i class="ti ti-loader"></i>').prop('disabled', true);

        $.ajax({ url, type: 'PATCH' })
            .done(function (response) {
                table.ajax.reload(null, false);
                if (typeof toastOk === 'function') {
                    toastOk(response.message || 'Estado actualizado');
                }
            })
            .fail(function () {
                alert('No se pudo cambiar el estado del comunicado.');
            })
            .always(function () {
                $btn.html(originalHtml).prop('disabled', false);
            });
    });

    // Eliminar con SweetAlert2
    $(document).on('click', '.js-delete-comunicado', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const title = $(this).data('title') || 'este comunicado';

        Swal.fire({
            title: `¿Eliminar "${title}"?`,
            text: "Esta acción no se puede deshacer. El comunicado será eliminado permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url,
                    type: 'POST',
                    data: { _method: 'DELETE' }
                })
                .done(function (response) {
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: response.message || `"${title}" fue eliminado correctamente`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                })
                .fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el comunicado.'
                    });
                });
            }
        });
    });

    // Función de limpieza de modales
    function cleanupModalBackdrops() {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        $('.modal-backdrop').remove();
    }

    $(document).on('click', '.btn-eliminar-comunicado', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Este comunicado se eliminará definitivamente!",
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

    $(document).on('click', '.btn-edit-comunicado', function (e) {
        e.preventDefault();
        const url = $(this).data('edit-url') || $(this).attr('href');

        // Loading simple
        $('#comunicadoEditModalContent').html('<div class="text-center p-4">Cargando...</div>');
        const modal = new bootstrap.Modal(document.getElementById('comunicadoEditModal'));
        modal.show();

        // Cargar el formulario de edición
        $.get(url, function (html) {
            $('#comunicadoEditModalContent').html(html);
        }).fail(function () {
            $('#comunicadoEditModalContent').html('<div class="alert alert-danger">Error al cargar el formulario</div>');
        });
    });

    // Envío por AJAX (delegado porque el form se inyecta dinámicamente)
    $(document).on('submit', '#formEditComunicado', function (e) {
        e.preventDefault();

        const $form = $(this);
        const action = $form.attr('action');
        const formData = new FormData(this);
        const $btn = $form.find('#submitBtnEdit');

        // UI: deshabilitar botón
        $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Guardando...');

        $.ajax({
            url: action,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {
                // Si el controlador responde JSON de éxito:
                // { ok: true, message: "..."}
                try {
                    if (resp && resp.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: resp.message || 'El comunicado se actualizó correctamente.',
                            timer: 1300,
                            showConfirmButton: false
                        });
                    } else {
                        // Caso: devolvió HTML (no JSON), refrescamos de todas formas
                        Swal.fire({ icon:'success', title:'Actualizado', timer: 1200, showConfirmButton:false });
                    }
                } catch (err) {
                    // Silencioso: igual mostramos éxito si status 200
                    Swal.fire({ icon:'success', title:'Actualizado', timer:1200, showConfirmButton:false });
                }

                // Cerrar modal
                const modalEl = document.getElementById('comunicadoEditModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal && modal.hide();

                // Recargar DataTable sin reiniciar página
                if (window.table) {
                    window.table.ajax.reload(null, false);
                }
                //  Vamos a refrescar la página completa
                location.reload();
            },
            error: function (xhr) {
                // Intentar mostrar validaciones
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let html = '<ul class="mb-0">';
                    for (const [field, messages] of Object.entries(xhr.responseJSON.errors)) {
                        messages.forEach(m => html += `<li>${m}</li>`);
                    }
                    html += '</ul>';
                    Swal.fire({ icon: 'error', title: 'Datos inválidos', html });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el comunicado.' });
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Guardar cambios');
            }
        });
    });

});
</script>
@endpush
