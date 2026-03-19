{{-- resources/views/users/partials/actions.blade.php --}}
<div class="d-flex justify-content-end gap-1">
    <a href="{{ route('users.staff.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="ti ti-eye"></i></a>
    <a class="btn btn-sm btn-outline-primary js-open-user-modal" href="#" data-url="{{ route('users.edit', $user) }}" data-title="Editar usuario #{{ $user->id }}" title="Editar"><i class="ti ti-pencil"></i></a>
    <a class="btn btn-sm btn-outline-secondary js-open-user-modal" href="#" data-url="{{ route('users.roles.edit', $user) }}" title="Roles"><i class="ti ti-shield-lock"></i></a>
    <a class="btn btn-sm btn-outline-secondary js-open-user-modal" href="#" data-url="{{ route('users.companies.edit', $user) }}" title="Empresas"><i class="ti ti-building"></i></a>
    <a class="btn btn-sm btn-outline-secondary js-open-cost-centers-modal"
        href="javascript:void(0)"
        data-url="{{ route('users.cost-centers.edit', $user->id) }}"
        data-has-companies="{{ $user->companies->isNotEmpty() ? 'true' : 'false' }}"
        data-user-name="{{ $user->name }}"
        title="Centros de Costo"><i class="ti ti-building-bank"></i></a>
    <a href="#" class="btn btn-sm btn-outline-warning js-toggle-active" data-url="{{ route('users.toggle', $user) }}" title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
        @if($user->is_active)
            <i class="ti ti-user-off"></i>
        @else
            <i class="ti ti-user-check"></i>
        @endif
    </a>
    <a href="#" class="btn btn-sm btn-outline-secondary js-open-user-modal" data-url="{{ route('users.supplier.edit', $user) }}" data-title="Datos de Proveedor — Usuario #{{ $user->id }}" title="Datos de Proveedor"><i class="ti ti-building-store"></i></a>
    <a href="#" class="btn btn-sm btn-outline-danger js-delete-user" data-url="{{ route('users.destroy', $user) }}" data-name="{{ $user->name }}" title="Eliminar"><i class="ti ti-trash"></i></a>
</div>
