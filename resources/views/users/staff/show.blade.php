@extends('layouts.zircos')

{{-- TÍTULO DE LA PÁGINA       --}}
@section('title', 'Usuario Staff - ' . $user->full_name)

{{-- CSS ADICIONAL (opcional)  --}}
@push('styles')
<style>
    .user-avatar {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .info-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    .badge-status {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }
    .section-divider {
        border-top: 2px solid #e9ecef;
        margin: 2rem 0;
    }
</style>
@endpush

@section('page.title', 'Perfil de Usuario')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.staff.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">{{ $user->full_name }}</li>
@endsection

{{-- CONTENIDO PRINCIPAL       --}}
@section('content')
    <!-- Header del Usuario -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}"
                             alt="{{ $user->full_name }}"
                             class="rounded-circle user-avatar">
                    @else
                        <div class="rounded-circle user-avatar bg-primary d-flex align-items-center justify-content-center">
                            <i class="ti ti-user text-white" style="font-size: 3rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-1 fw-bold">{{ $user->full_name }}</h3>
                            @if($user->job_title)
                                <p class="text-muted mb-2 fs-5">{{ $user->job_title }}</p>
                            @endif
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge badge-status {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                    <i class="ti ti-{{ $user->is_active ? 'check' : 'x' }} me-1"></i>
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                                @if($user->hasAnyRole())
                                    <div class="d-flex gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-info">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                                    <i class="ti ti-edit me-2"></i>Editar usuario
                                </a></li>
                                @if($user->is_active)
                                    <li><a class="dropdown-item text-warning" href="#" onclick="toggleUserStatus({{ $user->id }}, false)">
                                        <i class="ti ti-player-pause me-2"></i>Desactivar
                                    </a></li>
                                @else
                                    <li><a class="dropdown-item text-success" href="#" onclick="toggleUserStatus({{ $user->id }}, true)">
                                        <i class="ti ti-player-play me-2"></i>Activar
                                    </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser({{ $user->id }})">
                                    <i class="ti ti-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <div class="card info-card mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-user-circle text-primary me-2"></i>
                        Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-primary me-3">
                                    <i class="ti ti-user"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Nombre completo</label>
                                    <p class="mb-0 fw-semibold">{{ $user->full_name ?: $user->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-info me-3">
                                    <i class="ti ti-mail"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Correo electrónico</label>
                                    <p class="mb-0 fw-semibold">{{ $user->email }}</p>
                                    @if($user->email_verified_at)
                                        <small class="text-success"><i class="ti ti-check me-1"></i>Verificado</small>
                                    @else
                                        <small class="text-warning"><i class="ti ti-alert-circle me-1"></i>Sin verificar</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($user->phone)
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-success me-3">
                                    <i class="ti ti-phone"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Teléfono</label>
                                    <p class="mb-0 fw-semibold">{{ $user->phone }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($user->job_title)
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-warning me-3">
                                    <i class="ti ti-briefcase"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Puesto</label>
                                    <p class="mb-0 fw-semibold">{{ $user->job_title }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información del Sistema -->
            <div class="card info-card">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-settings text-secondary me-2"></i>
                        Información del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-primary me-3">
                                    <i class="ti ti-calendar-plus"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Fecha de registro</label>
                                    <p class="mb-0 fw-semibold">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-info me-3">
                                    <i class="ti ti-edit"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Última actualización</label>
                                    <p class="mb-0 fw-semibold">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                                    <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>

                        @if($user->last_login)
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-success me-3">
                                    <i class="ti ti-login"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">Último acceso</label>
                                    <p class="mb-0 fw-semibold">{{ $user->last_login->format('d/m/Y H:i') }}</p>
                                    <small class="text-muted">{{ $user->last_login->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="stat-icon bg-light text-secondary me-3">
                                    <i class="ti ti-hash"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label text-muted mb-1">ID de usuario</label>
                                    <p class="mb-0 fw-semibold">#{{ $user->id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Estadísticas Rápidas -->
            <div class="card info-card mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-chart-bar text-info me-2"></i>
                        Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">Tiempo en el sistema</small>
                            <div class="fw-bold">{{ $user->created_at->diffForHumans(null, true) }}</div>
                        </div>
                        <div class="stat-icon bg-primary text-white">
                            <i class="ti ti-clock"></i>
                        </div>
                    </div>

                    @if($user->hasAnyRole())
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">Roles asignados</small>
                            <div class="fw-bold">{{ $user->roles->count() }}</div>
                        </div>
                        <div class="stat-icon bg-info text-white">
                            <i class="ti ti-shield-check"></i>
                        </div>
                    </div>
                    @endif

                    @if($user->supplier)
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Proveedor asociado</small>
                            <div class="fw-bold text-success">Sí</div>
                        </div>
                        <div class="stat-icon bg-success text-white">
                            <i class="ti ti-building"></i>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card info-card">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-bolt text-warning me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="javascript:void(0);" onclick="openUserModal('{{ route('users.edit', $user->id) }}')"
                           class="btn btn-primary">
                            <i class="ti ti-edit me-2"></i>Editar Usuario
                        </a>

                        @if($user->is_active)
                            <button type="button" class="btn btn-warning" onclick="toggleUserStatus({{ $user->id }}, false)">
                                <i class="ti ti-player-pause me-2"></i>Desactivar
                            </button>
                        @else
                            <button type="button" class="btn btn-success" onclick="toggleUserStatus({{ $user->id }}, true)">
                                <i class="ti ti-player-play me-2"></i>Activar
                            </button>
                        @endif

                        <a href="{{ route('users.staff.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
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

{{-- JS ADICIONAL   --}}
@push('scripts')
<script>
function toggleUserStatus(userId, active) {
    const action = active ? 'activar' : 'desactivar';
    const title = active ? '¿Activar usuario?' : '¿Desactivar usuario?';
    const text = `¿Estás seguro de que quieres ${action} este usuario?`;

    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status ? '#28a745' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: status ? 'Sí, activar' : 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí puedes hacer la petición AJAX
            fetch(`/users/${userId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ is_active: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', 'No se pudo actualizar el estado del usuario', 'error');
                }
            });
        }
    });
}

function deleteUser(userId) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí puedes hacer la petición de eliminación
            document.getElementById('delete-form').action = `/users/${userId}`;
            document.getElementById('delete-form').submit();
        }
    });
}

// Abrir modal Crear
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
                    if (typeof toastOk === 'function') toastOk('Guardado correctamente');
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

</script>

<!-- Formulario oculto para eliminación -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endpush
