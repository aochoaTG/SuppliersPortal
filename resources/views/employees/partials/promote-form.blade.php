<div class="modal-header border-bottom-0 pb-1">
    <div>
        <h5 class="modal-title fw-semibold">
            <i class="ti ti-user-plus me-2 text-muted fs-18"></i>
            Crear usuario staff
        </h5>
        <p class="text-muted mb-0 mt-1" style="font-size:12px;">
            Completa los datos para crear el acceso al portal para <strong>{{ $employee->full_name }}</strong>.
        </p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="promoteForm"
      action="{{ route('employees.promote', $employee->id) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf

    <div class="modal-body pt-2">

        {{-- ── Avatar drag & drop ──────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-photo me-1"></i>Foto de perfil
        </p>

        <div class="mb-3">
            <div id="avatarDropZone"
                 class="border border-dashed rounded d-flex flex-column align-items-center justify-content-center p-3"
                 style="cursor:pointer;min-height:120px;border-style:dashed!important;">
                <img id="avatarPreviewPromote"
                     src="{{ asset('images/users/avatar-1.jpg') }}"
                     class="rounded-circle mb-2 d-none"
                     style="width:64px;height:64px;object-fit:cover;"
                     alt="Preview">
                <i id="avatarDropIcon" class="ti ti-photo fs-28 text-muted"></i>
                <span class="text-muted mt-1" style="font-size:12px;">Arrastra tu foto aquí o haz clic</span>
                <span class="text-muted" style="font-size:11px;">JPG, PNG o WEBP · Máx. 2 MB</span>
                <input type="file" name="avatar" id="avatarInputPromote"
                       accept="image/png,image/jpeg,image/webp"
                       class="d-none">
            </div>
        </div>

        {{-- ── Datos personales ─────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-user me-1"></i>Datos personales
        </p>

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Nombre(s)</label>
                <input type="text" name="first_name" class="form-control form-control-sm"
                       value="{{ $employee->first_name }}">
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Apellido(s)</label>
                <input type="text" name="last_name" class="form-control form-control-sm"
                       value="{{ $employee->last_name }}">
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Usuario (nombre completo) <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-sm"
                       value="{{ trim($employee->first_name . ' ' . $employee->last_name) }}"
                       required>
                <div class="invalid-feedback" id="err-name"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Teléfono</label>
                <input type="text" name="phone" class="form-control form-control-sm"
                       value="{{ $employee->phone }}">
                <div class="invalid-feedback" id="err-phone"></div>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-12">
                <label class="form-label form-label-sm mb-1">Puesto</label>
                <input type="text" name="job_title" class="form-control form-control-sm"
                       value="{{ $employee->job_title }}">
                <div class="invalid-feedback" id="err-job_title"></div>
            </div>
        </div>

        {{-- ── Credenciales ──────────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2 mt-3" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-lock me-1"></i>Credenciales de acceso
        </p>

        <div class="row g-2 mb-2">
            <div class="col-md-7">
                <label class="form-label form-label-sm mb-1">Correo electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control form-control-sm"
                       value="{{ $employee->email }}"
                       required>
                <div class="invalid-feedback" id="err-email"></div>
                <div class="form-text">Solo se permiten dominios corporativos del grupo TotalGas.</div>
            </div>
            <div class="col-md-5">
                <label class="form-label form-label-sm mb-1">Contraseña <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control form-control-sm"
                       required minlength="8" autocomplete="new-password">
                <div class="invalid-feedback" id="err-password"></div>
            </div>
        </div>

        {{-- ── Roles ─────────────────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2 mt-3" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-shield me-1"></i>Roles
        </p>

        <div class="row g-2">
            @foreach($roles as $role)
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="roles[]" value="{{ $role->name }}"
                           id="role_{{ $role->id }}">
                    <label class="form-check-label form-label-sm" for="role_{{ $role->id }}">
                        {{ $role->name }}
                    </label>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Errores generales --}}
        <div id="promoteGeneralError" class="alert alert-danger mt-3 d-none"></div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>Cancelar
        </button>
        <button type="submit" class="btn btn-primary btn-sm" id="promoteSubmitBtn">
            <i class="ti ti-user-plus me-1"></i>Crear usuario
        </button>
    </div>
</form>

<script>
(function () {
    // Drag & drop avatar
    const dropZone = document.getElementById('avatarDropZone');
    const fileInput = document.getElementById('avatarInputPromote');
    const preview  = document.getElementById('avatarPreviewPromote');
    const icon     = document.getElementById('avatarDropIcon');

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            icon.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }

    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => showPreview(fileInput.files[0]));
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-primary'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-primary'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-primary');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showPreview(file);
        }
    });
})();
</script>
