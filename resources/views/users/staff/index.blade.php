@extends('layouts.zircos')

{{-- TÍTULO DE LA PÁGINA       --}}
@section('title', 'Listado de Usuarios Staff')

{{-- CSS ADICIONAL (opcional)  --}}
@push('styles')
    {{-- Ejemplo: <link rel="stylesheet" href="{{ asset('css/custom.css') }}"> --}}
    <style>
        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 200px); /* header + footer */
            overflow-y: auto;
        }
    </style>
@endpush

@section('page.title', 'Listado de Usuarios Staff')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
    <li class="breadcrumb-item active">Usuarios Staff</li>
@endsection
{{-- CONTENIDO PRINCIPAL       --}}
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            {{-- Título del listado --}}
            <h5 class="mb-0">Usuarios</h5>
        </div>
        <div class="card-body">
            {{-- Aquí va tu tabla o listado --}}
            <table class="table table-sm table-striped align-middle w-100" id="usuariosTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Puesto</th>
                        <th>Empresas</th>
                        <th>Roles</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal genérico --}}
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" id="userModalContent">
            {{-- aquí se inyecta users.partials.form vía AJAX --}}
            </div>
        </div>
    </div>

@endsection

{{-- JS ADICIONAL (opcional)   --}}
@push('scripts')
<script>
$(function () {
    // CSRF p/ AJAX
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    // DataTable
    const table = $('#usuariosTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        dom: '<"top"Bf>rt<"bottom"lip>',
        pageLength: 50, // 👈 Agregar tamaño de página
        buttons: [
            {
                text: '<i class="ti ti-user-plus me-1"></i> Nuevo usuario',
                className: 'btn btn-primary btn-sm',
                attr: { id: 'btnCreateUser', title: 'Crear nuevo usuario' },
                action: function (e, dt, node, config) {
                    openUserModal("{{ route('users.create') }}");
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
            url: "{{ route('users.datatable') }}",
            type: "GET", // 👈 Especificar método
            error: function(xhr, error, thrown) {
                console.error('Error en DataTable:', xhr.responseText);
                alert('Error al cargar los datos. Revisa la consola.');
            }
        },
        columns: [
            { data: 'id',       name: 'id' },
            { data: 'name',     name: 'name' },
            { data: 'email',    name: 'email' },
            { data: 'telefono', name: 'telefono' },
            { data: 'puesto',   name: 'puesto' },
            { // 👇 nueva columna
                data: 'empresas',
                name: 'empresas',
                orderable: false,  // es HTML
                searchable: false  // la búsqueda la hacemos en el servidor
            },
            {
                data: 'roles',
                name: 'roles',
                orderable: false,
                searchable: false
            },
            {
                data: 'activo',
                name: 'activo',
                render: function(data) {
                    return data ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>';
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
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");

            // Re-inicializar tooltips Bootstrap en cada render
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    $('#userModal').on('hidden.bs.modal', function () {
        cleanupModalBackdrops();
    });

    // Abrir modal Crear
    $(document).on('click', '#btnCreateUser', function (e) {
        e.preventDefault();
        openUserModal("{{ route('users.create') }}");
    });

    // Abrir modal Editar (desde dropdown)
    $(document).on('click', '.js-open-user-modal', function (e) {
        e.preventDefault();
        openUserModal($(this).data('url'));
    });

    function openUserModal(url) {
        const el = document.getElementById('userModal');
        const modal = bootstrap.Modal.getOrCreateInstance(el); // 👈 evita instancias duplicadas

        $('#userModalContent').html('<div class="p-5 text-center">Cargando...</div>');
        modal.show();

        $.get(url)
            .done(function (html) { $('#userModalContent').html(html); })
            .fail(function () {
                $('#userModalContent').html('<div class="p-5 text-danger">No se pudo cargar el formulario.</div>');
            });
        }

        // Submit del form del modal (create / edit)
        $(document).on('submit', '#userForm', function (e) {
            e.preventDefault();
            const $form  = $(this);
            const action = $form.attr('action');
            const data   = $form.serialize();

            $form.find('button[type="submit"]').prop('disabled', true);
            $('#formErrors').addClass('d-none').empty();

            $.ajax({ url: action, type: 'POST', data })
                .done(function () {
                const el = document.getElementById('userModal');
                const modal = bootstrap.Modal.getInstance(el);
                if (modal) modal.hide();

                // Espera a que cierre visualmente y entonces recarga tabla/toast
                $('#userModal').one('hidden.bs.modal', function () {
                    $('#userModalContent').empty();             // opcional
                    table.ajax.reload(null, false);

                    // 👇 mensaje específico si es formulario de roles
                    const formType = $form.data('form-type');
                    if (formType === 'roles' && typeof toastOk === 'function') {
                        toastOk('Roles guardados correctamente');
                    } else if (formType === 'companies' && typeof toastOk === 'function') {
                        toastOk('Empresas guardadas correctamente');
                    } else if (typeof toastOk === 'function') {
                        toastOk('Guardado correctamente');
                    }
                    // Fallback de limpieza por si algo quedara
                    cleanupModalBackdrops();
                });
                })
                .fail(function (xhr) {
                $form.find('button[type="submit"]').prop('disabled', false);
                if (xhr.status === 422) {
                    const res = xhr.responseJSON;
                    let html = '<div class="alert alert-danger"><ul class="mb-0">';
                    Object.values(res.errors || {}).forEach(arr => arr.forEach(msg => html += `<li>${msg}</li>`));
                    html += '</ul></div>';
                    $('#formErrors').html(html).removeClass('d-none');
                } else {
                    $('#formErrors').html('<div class="alert alert-danger">Error inesperado.</div>').removeClass('d-none');
                }
                });
    });

    // Fallback fuerte para limpiar backdrop/clases si se quedaran pegadas
    function cleanupModalBackdrops() {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        $('.modal-backdrop').remove();
    }

    // Toggle activo
    $(document).on('click', '.js-toggle-active', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        $.ajax({ url, type: 'PATCH' })
            .done(function () {
                table.ajax.reload(null, false);
            })
            .fail(function () {
                alert('No se pudo cambiar el estado.');
            });
    });

    // Eliminar con SweetAlert2
    $(document).on('click', '.js-delete-user', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const name = $(this).data('name') || 'este usuario';

        Swal.fire({
            title: `¿Eliminar ${name}?`,
            text: "Esta acción no se puede deshacer.",
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
                .done(function () {
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: `${name} fue eliminado correctamente`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el usuario.'
                    });
                });
            }
        });
    });
});

// Toast genérico con SweetAlert2 (si ya lo tienes, omite esto)
window.toastOk = function (msg = 'Operación exitosa') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
    Toast.fire({ icon: 'success', title: msg });
};

</script>

@endpush
