{{-- resources/views/users/staff/partials/form.blade.php --}}
<div class="modal-header border-bottom-0 pb-1">
    <div>
        <h5 class="modal-title fw-semibold">
            <i class="ti ti-{{ ($mode ?? 'create') === 'create' ? 'user-plus' : 'user-edit' }} me-2 text-muted fs-18"></i>
            {{ $title ?? ($mode === 'create' ? 'Nuevo usuario' : 'Editar usuario') }}
        </h5>
        <p class="text-muted mb-0 mt-1" style="font-size:12px;">
            {{ ($mode ?? 'create') === 'create'
                ? 'Completa los datos para crear el acceso al portal.'
                : 'Actualiza la información del usuario.' }}
        </p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="userForm" action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="modal-body pt-2">

        {{-- ── Avatar ───────────────────────────────────────────────── --}}
        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">

            {{-- Preview --}}
            <div class="flex-shrink-0">
                <img id="avatarPreview"
                     src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/users/avatar-1.jpg') }}"
                     alt="Avatar"
                     class="rounded-circle border"
                     style="width:64px;height:64px;object-fit:cover;">
            </div>

            {{-- Controles --}}
            <div class="flex-grow-1">
                <label class="form-label form-label-sm mb-1 d-block">Foto de perfil</label>
                <div class="d-flex gap-2 flex-wrap">
                    <label class="btn btn-outline-secondary btn-sm mb-0" style="cursor:pointer;">
                        <i class="ti ti-upload me-1 fs-13"></i>Subir foto
                        <input type="file" name="avatar" id="avatarInput"
                               accept="image/png,image/jpeg,image/webp"
                               class="d-none"
                               onchange="
                                   var f=this.files[0];
                                   if(f){ document.getElementById('avatarPreview').src=URL.createObjectURL(f); }
                               ">
                    </label>
                    @if(($mode ?? 'create') === 'edit' && $user->avatar)
                        <label class="btn btn-outline-danger btn-sm mb-0" style="cursor:pointer;">
                            <i class="ti ti-trash me-1 fs-13"></i>Eliminar
                            <input type="checkbox" name="remove_avatar" value="1" class="d-none"
                                   onchange="
                                       if(this.checked){
                                           document.getElementById('avatarPreview').src='{{ asset('images/users/avatar-1.jpg') }}';
                                       }
                                   " checked>
                        </label>
                    @endif
                </div>
                <div class="form-text">JPG, PNG o WEBP · Máx. 2 MB</div>
            </div>

        </div>

        {{-- ── Datos personales ─────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2"
           style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-user me-1"></i>Datos personales
        </p>

        <div class="row g-2">

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Nombre(s)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-user fs-14 text-muted"></i>
                    </span>
                    <input type="text" name="first_name"
                           value="{{ old('first_name', $user->first_name) }}"
                           class="form-control form-control-sm"
                           placeholder="Juan">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Apellidos</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-user fs-14 text-muted"></i>
                    </span>
                    <input type="text" name="last_name"
                           value="{{ old('last_name', $user->last_name) }}"
                           class="form-control form-control-sm"
                           placeholder="Pérez López">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">
                    Nombre a mostrar <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-id-badge-2 fs-14 text-muted"></i>
                    </span>
                    <input type="text" name="name"
                           value="{{ old('name', $user->name) }}"
                           class="form-control form-control-sm"
                           placeholder="Juan Pérez" required>
                </div>
                <div class="form-text">Aparece en el topbar y notificaciones.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">
                    Correo electrónico <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-mail fs-14 text-muted"></i>
                    </span>
                    <input type="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-control form-control-sm"
                           placeholder="usuario@totalgas.com" required>
                </div>
                @if(($mode ?? 'create') === 'edit')
                    <div class="form-text">
                        @if($user->email_verified_at)
                            <i class="ti ti-circle-check text-success me-1"></i>Verificado
                            {{ $user->email_verified_at->format('d/m/Y') }}
                        @else
                            <i class="ti ti-circle-x text-warning me-1"></i>Sin verificar
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Teléfono</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-phone fs-14 text-muted"></i>
                    </span>
                    <input type="text" name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           class="form-control form-control-sm"
                           placeholder="(55) 1234-5678">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Puesto</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-briefcase fs-14 text-muted"></i>
                    </span>
                    <input type="text" name="job_title"
                           value="{{ old('job_title', $user->job_title) }}"
                           class="form-control form-control-sm"
                           placeholder="Analista de compras">
                </div>
            </div>

        </div>{{-- /row datos personales --}}

        <hr class="my-3">

        {{-- ── Cuenta ───────────────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2"
           style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-lock me-1"></i>Cuenta
        </p>

        <div class="row g-2">

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">
                    Contraseña
                    @if(($mode ?? 'create') === 'create')
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="ti ti-lock fs-14 text-muted"></i>
                    </span>
                    <input type="password"
                           name="password"
                           class="form-control form-control-sm"
                           {{ ($mode ?? 'create') === 'create' ? 'required' : '' }}
                           placeholder="{{ ($mode ?? 'create') === 'create' ? 'Mínimo 8 caracteres' : '••••••••' }}">
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            onclick="(function(btn){
                                var i = btn.closest('.input-group').querySelector('input');
                                i.type = i.type==='password' ? 'text' : 'password';
                                btn.querySelector('i').className = i.type==='password'
                                    ? 'ti ti-eye fs-14'
                                    : 'ti ti-eye-off fs-14';
                            })(this)"
                            title="Mostrar / ocultar">
                        <i class="ti ti-eye fs-14"></i>
                    </button>
                </div>
                @if(($mode ?? 'create') !== 'create')
                    <div class="form-text">Dejar en blanco para no cambiar.</div>
                @else
                    <div class="form-text">Mínimo 8 caracteres.</div>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Estado</label>
                <div class="rounded px-3 py-2" style="min-height:31px;">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox"
                               role="switch" id="isActiveSwitch"
                               name="is_active" value="1"
                               {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label form-label-sm mb-0" for="isActiveSwitch">
                            Usuario activo
                        </label>
                    </div>
                </div>
                <div class="form-text">
                    Los usuarios inactivos no pueden iniciar sesión.
                    @if(($mode ?? 'create') === 'edit' && $user->last_login)
                        · Último acceso: {{ $user->last_login->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>

        </div>{{-- /row cuenta --}}

        <hr class="my-3">

        {{-- ── Roles ────────────────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2"
           style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-shield-check me-1"></i>Roles
        </p>

        @if(isset($roles) && $roles->isNotEmpty())
            <div class="d-flex flex-wrap gap-2">
                @foreach($roles as $role)
                    @php
                        $checked = in_array($role->name, old('roles', ($userRoles ?? collect())->toArray()));
                    @endphp
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox"
                               name="roles[]"
                               id="role_{{ $role->id }}"
                               value="{{ $role->name }}"
                               {{ $checked ? 'checked' : '' }}>
                        <label class="form-check-label form-label-sm" for="role_{{ $role->id }}">
                            {{ trans("roles.{$role->name}") }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="form-text">Un usuario puede tener más de un rol simultáneamente.</div>
        @else
            <p class="text-muted fs-12 mb-0">No hay roles definidos.</p>
        @endif

        <div class="mt-3 d-none" id="formErrors"></div>

    </div>{{-- /modal-body --}}

    <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>Cancelar
        </button>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="ti ti-{{ ($mode ?? 'create') === 'create' ? 'user-plus' : 'device-floppy' }} me-1"></i>
            {{ ($mode ?? 'create') === 'create' ? 'Crear usuario' : 'Guardar cambios' }}
        </button>
    </div>

</form>
