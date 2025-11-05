<form id="userForm"
      method="POST"
      action="{{ route('users.companies.update', $user) }}"
      data-form-type="companies">
    @csrf
    @method('PATCH')

    <div class="modal-header">
        <h5 class="modal-title">
            Empresas de {{ $user->full_name ?? $user->name }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>

    <div class="modal-body">
        <div id="formErrors" class="d-none"></div>

        <p class="text-muted small mb-3">
            Activa las empresas a las que este usuario tiene acceso.
        </p>

        <div class="list-group list-group-flush">
            @foreach ($companies as $c)
                <label class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <div>
                        <span class="fw-semibold">[{{ $c->code }}]</span> {{ $c->name }}
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="company_ids[]"
                               value="{{ $c->id }}" id="company_{{ $c->id }}"
                               {{ in_array($c->id, $attached) ? 'checked' : '' }}>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i> Guardar
        </button>
    </div>
</form>

@push('styles')
<style>
.list-group-item:hover {
    background-color: #f9fafb;
}
.form-check-input {
    cursor: pointer;
}
</style>
@endpush
