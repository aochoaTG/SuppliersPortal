<div class="modal-header">
    <h5 class="modal-title">
        <i class="ti ti-shield-lock me-2"></i> Roles de: {{ $user->name }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<div class="modal-body">
    <div id="formErrors" class="d-none"></div>

    <form id="userForm" action="{{ route('users.roles.update', $user) }}" method="POST" data-form-type="roles">
        @csrf
        @method('PATCH')

        <p class="text-muted mb-3">
            Marca o desmarca los roles que debe tener este usuario. Al guardar se aplicarán los cambios inmediatamente.
        </p>

        <div class="row g-3">
            @forelse ($roles as $role)
                <div class="col-12 col-md-6">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value="{{ $role->name }}"
                            id="role_{{ Str::slug($role->name) }}"
                            name="roles[]"
                            @checked($user->hasRole($role->name))
                        >
                        <label class="form-check-label" for="role_{{ Str::slug($role->name) }}">
                            {{ Str::headline($role->name) }}
                        </label>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-0">
                        No hay roles configurados. Crea roles primero (spatie/permission).
                    </div>
                </div>
            @endforelse
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Guardar cambios
            </button>
        </div>
    </form>
</div>
