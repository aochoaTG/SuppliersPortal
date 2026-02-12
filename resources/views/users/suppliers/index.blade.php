@extends('layouts.zircos')

{{-- TÍTULO DE LA PÁGINA       --}}
@section('title', 'Listado de Usuarios Proveedores')

{{-- CSS ADICIONAL (opcional)  --}}
@push('styles')
<style>
/* Estilos para mejorar el preview del avatar */
#avatarPreview {
    transition: opacity 0.3s ease;
    cursor: pointer;
}

#avatarPreview:hover {
    opacity: 0.8;
}

/* Indicador de carga opcional */
.avatar-loading {
    position: relative;
    opacity: 0.5;
}

.avatar-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@section('page.title', 'Listado de Usuarios Proveedores')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
    <li class="breadcrumb-item active">Usuarios Proveedores</li>
@endsection
{{-- CONTENIDO PRINCIPAL       --}}
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            {{-- Título del listado --}}
            <h5 class="mb-0">Usuarios Proveedores</h5>
        </div>
        <div class="card-body">
            {{-- Aquí va tu tabla o listado --}}
            <table class="table table-sm table-striped align-middle w-100" id="suppliersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Empresa</th>
                        <th>RFC</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Banco</th>
                        <th>Último acceso</th>
                        <th>Estatus</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Modal genérico --}}
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable"><!-- 👈 más ancho -->
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
    const table = $('#suppliersTable').DataTable({
        responsive: false,
        processing: true,
        dom: '<"top"Bf>rt<"bottom"lip>',
        buttons: [
            {
            extend: 'excel',
            text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
            className: 'btn btn-success btn-sm'  // verde
            },
            {
            extend: 'copy',
            text: '<i class="ti ti-copy me-1"></i> Copiar',
            className: 'btn btn-warning btn-sm'  // amarillo
            },
            {
            extend: 'pdf',
            text: '<i class="ti ti-file-text me-1"></i> PDF',
            className: 'btn btn-info btn-sm',     // azul suave
            orientation: 'portrait', // opcional
            pageSize: 'A4'          // opcional
            }
        ],
        ajax: "{{ route('users.suppliers.datatable') }}", // Ruta que devuelve JSON
        columns: [
            { data: 'id', name: 'id' },
            { data: 'company_name', name: 'company_name' },
            { data: 'rfc', name: 'rfc' },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'contact_phone', name: 'contact_phone' },
            { data: 'email', name: 'email' },
            { data: 'bank_name', name: 'bank_name' },
            { data: 'last_login', name: 'last_login' }, // NUEVA
            { data: 'status', name: 'status'},
            { data: 'is_active', name: 'is_active', render: function(data){
                return data
                ? '<span class="badge bg-success"><i class="ti ti-check me-1"></i> Sí</span>'
                : '<span class="badge bg-danger"><i class="ti ti-x me-1"></i> No</span>';
            }},
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        language: {
            url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
        },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
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
        const modal = bootstrap.Modal.getOrCreateInstance(el);

        $('#userModalContent').html('<div class="p-5 text-center">Cargando...</div>');
        modal.show();

        $.get(url)
            .done(function (html) { $('#userModalContent').html(html); })
            .fail(function () {
                $('#userModalContent').html('<div class="p-5 text-danger">No se pudo cargar el formulario.</div>');
            });
    }

    $(document).off('submit', '#userForm'); // evita doble binding si reinyectas el form

    $(document).on('submit', '#userForm', function (e) {
    e.preventDefault();
    const $form  = $(this);
    const action = $form.attr('action');
    const formData = new FormData($form[0]); // incluye el file
    $form.find('button[type="submit"]').prop('disabled', true);
    $('#formErrors').addClass('d-none').empty();

    $.ajax({
        url: action,
        type: 'POST',          // _method=PUT via input hidden
        data: formData,
        processData: false,
        contentType: false,
        cache: false
    })
    .done(function (res) {
        const el = document.getElementById('userModal');
        const modal = bootstrap.Modal.getInstance(el);
        if (modal) modal.hide();

        $('#userModal').one('hidden.bs.modal', function () {
        $('#userModalContent').empty();
        table.ajax.reload(null, false);
        if (typeof toastOk === 'function') toastOk(res?.message || 'Guardado correctamente');
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
        $.ajax({
            url,
            type: 'PATCH'
        })
        .done(function (res) {
            table.ajax.reload(null, false);
            toastOk(res.message || 'Estado actualizado');
        })
        .fail(function () {
            Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
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
                $.ajax({ url, type: 'POST', data: { _method: 'DELETE' } })
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
// Preview en vivo del avatar seleccionado
$(document).on('change', 'input[name="user[avatar]"]', function () {
  const file = this.files?.[0];
  if (!file) return;

  // Validaciones simples (opcional)
  const okTypes = ['image/jpeg','image/png','image/webp'];
  if (!okTypes.includes(file.type)) {
    if (window.Swal) Swal.fire('Archivo no válido', 'Solo JPG, PNG o WEBP.', 'warning');
    this.value = '';
    return;
  }
  if (file.size > 2 * 1024 * 1024) {
    if (window.Swal) Swal.fire('Archivo muy grande', 'Máximo 2 MB.', 'warning');
    this.value = '';
    return;
  }

  const img = $('#userModal').find('#avatarPreview');
  if (!img.length) return;

  const url = URL.createObjectURL(file);
  img.attr('src', url);
  // liberar el blob cuando cargue
  img.one('load', function () {
    URL.revokeObjectURL(url);
  });

  // por si usas el flag de "quitar avatar"
  $('#removeAvatarFlag').val('0');
});

$(function () {
  const $avatarInput   = $('#avatarInput');
  const $removeFlag    = $('#removeAvatarFlag');
  const $preview       = $('#avatarPreview');
  const placeholderUrl = "{{ asset('assets/img/avatar-placeholder.png') }}"; // tu placeholder
  const $fileNameHint  = $('.mt-1.text-muted.small.text-truncate'); // el div que muestra el nombre del archivo (si existe)

    $(document).on('click', '#btnRemoveAvatar', function (e) {
        e.preventDefault();

        // Ubica el modal actual y toma los elementos desde ahí
        const $modal      = $(this).closest('.modal');
        const $removeFlag = $modal.find('#removeAvatarFlag');
        const $avatarInput= $modal.find('#avatarInput');
        const $preview    = $modal.find('#avatarPreview');
        const $fileNameHint = $modal.find('.mt-1.text-muted.small.text-truncate'); // si existe
        const placeholderUrl = "{{ asset('assets/img/avatar-placeholder.png') }}";

        const doRemove = () => {
            $removeFlag.val('1');
            $avatarInput.val('');
            $preview.attr('src', placeholderUrl);
            $fileNameHint.text('');              // opcional
            $(this).prop('disabled', true).addClass('d-none');
        };

        if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Quitar avatar',
            text: 'Se eliminará la foto actual del usuario.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then(res => res.isConfirmed && doRemove());
        } else {
        if (confirm('Se eliminará la foto actual del usuario. ¿Continuar?')) doRemove();
        }
    });

    function doRemoveAvatar() {
        // Marca flag para el backend
        $removeFlag.val('1');
        // Limpia selección de archivo (si hubiera)
        $avatarInput.val('');
        // Cambia preview a placeholder
        $preview.attr('src', placeholderUrl);
        // Borra/elide el nombre del archivo actual (si lo muestras)
        $fileNameHint.text(''); // opcional
        // Deshabilitar el botón
        $('#btnRemoveAvatar').prop('disabled', true).addClass('d-none');
    }

    // Si el usuario selecciona un archivo NUEVO, desmarca el flag (porque ya no es “quitar” sino “reemplazar”)
    $(document).on('change', '#avatarInput', function () {
        const $modal   = $(this).closest('.modal');
        const $preview = $modal.find('#avatarPreview');
        const $removeFlag = $modal.find('#removeAvatarFlag');

        if (this.files && this.files[0]) {
            $removeFlag.val('0'); // ya no es “quitar”, es “reemplazar”
            const reader = new FileReader();
            reader.onload = (e) => $preview.attr('src', e.target.result);
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
@endpush
