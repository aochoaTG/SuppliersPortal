@extends('layouts.zircos')

@section('title', 'Roles Autorizadores')
@section('page.title', 'Roles Autorizadores')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Roles autorizadores</li>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Roles autorizadores</h5>
                <small class="text-muted">Administra los límites de autorización disponibles para los aprobadores.</small>
            </div>
            <a href="{{ route('authorizer-roles.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i>Agregar rol autorizador
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Rol autorizador</th>
                        <th>Límite con IVA</th>
                        <th>Usuarios ligados</th>
                        <th>Aprobaciones ligadas</th>
                        <th>Estatus</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>
                                @if($role->approval_limit !== null)
                                    ${{ number_format((float) $role->approval_limit, 2) }}
                                @else
                                    <span class="text-muted">Sin límite</span>
                                @endif
                            </td>
                            <td>{{ $role->assignments_count }}</td>
                            <td>{{ $role->quotation_summaries_count }}</td>
                            <td>
                                <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $role->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('authorizer-roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                        Editar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('authorizer-roles.destroy', $role) }}"
                                          class="js-authorizer-role-delete"
                                          data-role-name="{{ $role->name }}"
                                          data-assignment-count="{{ $role->assignments_count }}"
                                          data-summary-count="{{ $role->quotation_summaries_count }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="force_delete" value="0">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay roles autorizadores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.js-authorizer-role-delete').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const roleName = form.dataset.roleName || 'este rol';
            const assignmentsCount = Number(form.dataset.assignmentCount || 0);
            const summariesCount = Number(form.dataset.summaryCount || 0);
            const forceInput = form.querySelector('input[name="force_delete"]');

            let message = `¿Deseas eliminar el rol autorizador "${roleName}"?`;

            if (assignmentsCount > 0) {
                message = `El rol autorizador "${roleName}" tiene ${assignmentsCount} usuario(s) ligado(s). Si lo borras, esas asignaciones se eliminarán automáticamente.`;

                if (summariesCount > 0) {
                    message += ` También se limpiarán ${summariesCount} referencia(s) de aprobaciones existentes.`;
                }

                message += ' ¿Quieres borrarlo de todos modos?';
                forceInput.value = '1';
            } else {
                if (summariesCount > 0) {
                    message += ` Esto limpiará ${summariesCount} referencia(s) de aprobaciones existentes.`;
                }

                forceInput.value = '0';
            }

            if (window.confirm(message)) {
                form.submit();
            }
        });
    });
</script>
@endsection
