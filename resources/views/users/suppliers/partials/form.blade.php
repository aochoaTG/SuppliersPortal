<div class="modal-header">
  <h5 class="modal-title">
    <i class="ti ti-user-cog me-2"></i> Editar Usuario Proveedor
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="userForm" action="{{ route('users.suppliers.update', $user) }}" method="POST" enctype="multipart/form-data" data-form-type="edit">
  @csrf
  @method('PUT')

  <div class="modal-body">
    <div id="formErrors" class="d-none"></div>

    {{-- ===== CUENTA DE USUARIO ===== --}}
    <div class="border rounded p-2 mb-2">
      <h6 class="text-uppercase text-muted fw-semibold mb-2 fs-6">
        <i class="ti ti-user me-1"></i> Cuenta de usuario
      </h6>

      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Nombre</label>
          <input type="text" name="user[name]" value="{{ old('user.name', $user->name) }}" class="form-control form-control-sm" maxlength="150" required>
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Correo (usuario)</label>
          <input type="email" name="user[email]" value="{{ old('user.email', $user->email) }}" class="form-control form-control-sm" maxlength="150" required>
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Puesto (usuario)</label>
          <input type="text" name="user[job_title]" value="{{ old('user.job_title', $user->job_title) }}" class="form-control form-control-sm" maxlength="100">
        </div>

        <div class="col-md-6">
            <label class="form-label form-label-sm mb-1">Avatar</label>

            {{-- Preview actual o placeholder --}}
            @php
                $avatarUrl = $user->avatar ? asset('storage/'.$user->avatar) : asset('assets/img/avatar-placeholder.png');
            @endphp
            <div class="d-flex align-items-center gap-2 mb-2">
                <img id="avatarPreview" src="{{ $avatarUrl }}" alt="avatar" class="rounded-circle border" width="48" height="48" style="object-fit:cover;">
                <div class="d-flex gap-2">
                <label class="btn btn-sm btn-outline-primary mb-0">
                    <i class="ti ti-upload me-1"></i> Elegir...
                    <input type="file" name="user[avatar]" accept="image/png,image/jpeg,image/webp" class="d-none" id="avatarInput">
                </label>

                @if($user->avatar)
                    <button type="button"
                            class="btn btn-sm btn-outline-danger"
                            id="btnRemoveAvatar">
                    <i class="ti ti-trash me-1"></i> Quitar
                    </button>
                @endif
                </div>
            </div>

            <input type="hidden" name="user[remove_avatar]" id="removeAvatarFlag" value="0">
            <div class="form-text small">JPG/PNG/WEBP, máx. 2 MB.</div>

            @if($user->avatar)
                <div class="mt-1 text-muted small text-truncate">{{ $user->avatar }}</div>
            @endif
        </div>

        <div class="col-12">
          <div class="form-check form-switch mt-1">
            <input class="form-check-input" type="checkbox" id="isActiveSwitch" name="user[is_active]" value="1" {{ old('user.is_active', $user->is_active) ? 'checked' : '' }}>
            <label class="form-check-label small" for="isActiveSwitch">Usuario activo</label>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== DATOS DEL PROVEEDOR ===== --}}
    @php $supplier = $user->supplier; @endphp
    @php
        $statusDefault = old('supplier.status', $supplier->status ?? 'Pending_docs');
    @endphp
    <input type="hidden" name="supplier[status]" value="{{ $statusDefault }}">

    {{-- Empresa / Contacto --}}
    <div class="border rounded p-2 mb-2">
      <h6 class="text-uppercase text-muted fw-semibold mb-2 fs-6">
        <i class="ti ti-building-store me-1"></i> Empresa / Contacto
      </h6>

      <div class="row g-2">
        <div class="col-md-8">
          <label class="form-label form-label-sm mb-1">Empresa</label>
          <input type="text" name="supplier[company_name]" value="{{ old('supplier.company_name', $supplier->company_name ?? '') }}" class="form-control form-control-sm" maxlength="255" required>
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">RFC</label>
          <input type="text" name="supplier[rfc]" value="{{ old('supplier.rfc', $supplier->rfc ?? '') }}" class="form-control form-control-sm text-uppercase" maxlength="13" required>
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Contacto</label>
          <input type="text" name="supplier[contact_person]" value="{{ old('supplier.contact_person', $supplier->contact_person ?? '') }}" class="form-control form-control-sm" maxlength="150">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Teléfono</label>
          <input type="text" name="supplier[contact_phone]" value="{{ old('supplier.contact_phone', $supplier->contact_phone ?? '') }}" class="form-control form-control-sm" maxlength="50">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Correo proveedor</label>
          <input type="email" name="supplier[email]" value="{{ old('supplier.email', $supplier->email ?? '') }}" class="form-control form-control-sm" maxlength="150">
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Tipo de proveedor</label>
          <select name="supplier[supplier_type]" class="form-select form-select-sm">
            <option value="" disabled {{ old('supplier.supplier_type', $supplier->supplier_type ?? '') == '' ? 'selected' : '' }}>Selecciona una opción</option>
            <option value="product" @selected(old('supplier.supplier_type', $supplier->supplier_type ?? '') === 'product')>Productos</option>
            <option value="service" @selected(old('supplier.supplier_type', $supplier->supplier_type ?? '') === 'service')>Servicios</option>
            <option value="product_service" @selected(old('supplier.supplier_type', $supplier->supplier_type ?? '') === 'product_service')>Productos y Servicios</option>
          </select>
        </div>
        <div class="col-md-6">
            <label class="form-label form-label-sm mb-1">Estado del proveedor</label>
            <select name="supplier[status]" class="form-select form-select-sm">
                <option value="pending_docs" @selected($supplier->status === 'pending_docs')>
                    Pendiente de documentos
                </option>
                <option value="approved" @selected($supplier->status === 'approved')>
                    Información completa
                </option>
                <option value="rejected" @selected($supplier->status === 'rejected')>
                    Rechazado
                </option>
            </select>
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Moneda</label>
          <select name="supplier[currency]" class="form-select form-select-sm">
            @foreach(($currencies ?? ['MXN'=>'MXN','USD'=>'USD']) as $k => $label)
              <option value="{{ $k }}" @selected(old('supplier.currency', $supplier->currency ?? 'MXN') === $k)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Régimen fiscal</label>
          <select name="supplier[tax_regime]" class="form-select form-select-sm">
            <option value="" disabled {{ old('supplier.tax_regime', $supplier->tax_regime ?? '') == '' ? 'selected' : '' }}>Selecciona una opción</option>
            <option value="individual" @selected(old('supplier.tax_regime', $supplier->tax_regime ?? '') === 'individual')>Persona Física</option>
            <option value="corporation" @selected(old('supplier.tax_regime', $supplier->tax_regime ?? '') === 'corporation')>Persona Moral</option>
            <option value="resico" @selected(old('supplier.tax_regime', $supplier->tax_regime ?? '') === 'resico')>RESICO</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label form-label-sm mb-1">Dirección</label>
          <textarea name="supplier[address]" rows="2" class="form-control form-control-sm" maxlength="500">{{ old('supplier.address', $supplier->address ?? '') }}</textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-footer py-2">
    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
      <i class="ti ti-x me-1"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-primary btn-sm">
      <i class="ti ti-device-floppy me-1"></i> Guardar
    </button>
  </div>
</form>



