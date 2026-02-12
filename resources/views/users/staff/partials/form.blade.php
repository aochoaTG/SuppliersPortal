{{-- resources/views/users/partials/form.blade.php --}}
<div class="modal-header">
    <h5 class="modal-title">{{ $title ?? ($mode === 'create' ? 'Crear usuario' : 'Editar usuario') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="userForm" action="{{ $action }}" method="POST">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method) {{-- en edición: PUT --}}
    @endif

    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre(s)</label>
                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellidos</label>
                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Nombre a mostrar (name)</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Puesto</label>
                <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Activo</label>
                <select name="is_active" class="form-select">
                    <option value="1" @selected(old('is_active', $user->is_active ?? true))>Sí</option>
                    <option value="0" @selected(!old('is_active', $user->is_active ?? true))>No</option>
                </select>
            </div>

            @if(($mode ?? 'create') === 'create')
            <div class="col-md-6">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            @else
            <div class="col-md-6">
                <label class="form-label">Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" name="password" class="form-control">
            </div>
            @endif
        </div>

        <div class="mt-3 d-none" id="formErrors"></div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">{{ ($mode ?? 'create') === 'create' ? 'Crear' : 'Guardar' }}</button>
    </div>
</form>
