{{-- resources/views/users/partials/actions.blade.php --}}
<div class="dropdown">
  <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    Acciones
  </button>
  <ul class="dropdown-menu">
    <li>
      <a href="{{ route('users.staff.show', $user) }}" class="dropdown-item"><i class="ti ti-eye"></i> Ver</a>
    </li>
    {{-- Botón Roles --}}
    <li>
        <a class="dropdown-item js-open-user-modal" href="#" data-url="{{ route('users.roles.edit', $user) }}">
            <i class="ti ti-shield-lock"></i> Roles
        </a>
    </li>
    {{-- Botón Empresas --}}
    <li>
        <a class="dropdown-item js-open-user-modal" href="#" data-url="{{ route('users.companies.edit', $user) }}">
            <i class="ti ti-building"></i> Empresas
        </a>
    </li>
    <!-- Botón Centros de Costos -->
    <li>
        <a class="dropdown-item js-open-cost-centers-modal" 
            href="javascript:void(0)" 
            data-url="{{ route('users.cost-centers.edit', $user->id) }}"
            data-has-companies="{{ $user->companies->isNotEmpty() ? 'true' : 'false' }}"
            data-user-name="{{ $user->name }}"><i class="ti ti-building-bank"></i> Centros de Costo
        </a>
    </li>
    <li>
      <a href="#" class="dropdown-item js-open-user-modal"
         data-url="{{ route('users.edit', $user) }}"
         data-title="Editar usuario #{{ $user->id }}">
        <i class="ti ti-edit"></i> Editar
      </a>
    </li>
    <li>
      <a href="#"
        class="dropdown-item js-toggle-active" data-url="{{ route('users.toggle', $user) }}">
            @if($user->is_active)
                <i class="ti ti-user-off me-1"></i> Desactivar
            @else
                <i class="ti ti-user-check me-1"></i> Activar
            @endif
        </a>
    </li>
    {{-- Botón: Datos de Proveedor --}}
    <li>
    <a href="#"
        class="dropdown-item js-open-user-modal"
        data-url="{{ route('users.supplier.edit', $user) }}"
        data-title="Datos de Proveedor — Usuario #{{ $user->id }}">
        <i class="ti ti-building-store"></i> Datos de Proveedor
    </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
      <a href="#" class="dropdown-item text-danger js-delete-user"
         data-url="{{ route('users.destroy', $user) }}"
         data-name="{{ $user->name }}">
        <i class="ti ti-trash"></i> Eliminar
      </a>
    </li>
  </ul>
</div>
