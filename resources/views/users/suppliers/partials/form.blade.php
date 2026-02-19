<form id="userForm" action="{{ route('users.suppliers.update', $user) }}" method="POST" enctype="multipart/form-data" data-form-type="edit">
  @csrf
  @method('PUT')

  <div class="modal-header border-bottom-0 pb-1">
    <h5 class="modal-title">
      <i class="ti ti-user-cog me-2 text-primary"></i> Editar Usuario Proveedor
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
  </div>

  <div class="modal-body pt-2">
    <div id="formErrors" class="d-none"></div>

    {{-- ═══════════════════════════════════════════════════
         SECCIÓN 1: CUENTA DE USUARIO
    ═══════════════════════════════════════════════════ --}}
    <div class="border rounded p-3 mb-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3 fs-12">
        <i class="ti ti-user me-1"></i> Cuenta de usuario
      </h6>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Nombre</label>
          <input type="text" name="user[name]" value="{{ old('user.name', $user->name) }}"
            class="form-control form-control-sm" maxlength="150" required>
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Correo (acceso al portal)</label>
          <input type="email" name="user[email]" value="{{ old('user.email', $user->email) }}"
            class="form-control form-control-sm" maxlength="150" required>
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Puesto</label>
          <input type="text" name="user[job_title]" value="{{ old('user.job_title', $user->job_title) }}"
            class="form-control form-control-sm" maxlength="100">
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Avatar</label>
          @php
            $avatarUrl = $user->avatar ? asset('storage/'.$user->avatar) : asset('assets/img/avatar-placeholder.png');
          @endphp
          <div class="d-flex align-items-center gap-2 mb-1">
            <img id="avatarPreview" src="{{ $avatarUrl }}" alt="avatar"
              class="rounded-circle border" width="44" height="44" style="object-fit:cover;">
            <div class="d-flex gap-2">
              <label class="btn btn-sm btn-outline-primary mb-0">
                <i class="ti ti-upload me-1"></i> Elegir...
                <input type="file" name="user[avatar]" accept="image/png,image/jpeg,image/webp"
                  class="d-none" id="avatarInput">
              </label>
              @if($user->avatar)
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveAvatar">
                  <i class="ti ti-trash me-1"></i> Quitar
                </button>
              @endif
            </div>
          </div>
          <input type="hidden" name="user[remove_avatar]" id="removeAvatarFlag" value="0">
          <div class="form-text">JPG/PNG/WEBP, máx. 2 MB.</div>
        </div>

        <div class="col-md-6 d-flex align-items-center">
          <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" id="isActiveSwitch"
              name="user[is_active]" value="1"
              {{ old('user.is_active', $user->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActiveSwitch">Usuario activo</label>
          </div>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         SECCIÓN 2: EMPRESA Y CONTACTO
    ═══════════════════════════════════════════════════ --}}
    @php $supplier = $user->supplier; @endphp

    <div class="border rounded p-3 mb-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3 fs-12">
        <i class="ti ti-building-store me-1"></i> Empresa y Contacto
      </h6>

      <div class="row g-3">
        {{-- Datos fiscales --}}
        <div class="col-md-5">
          <label class="form-label form-label-sm mb-1">Razón social / Empresa</label>
          <input type="text" name="supplier[company_name]"
            value="{{ old('supplier.company_name', $supplier->company_name ?? '') }}"
            class="form-control form-control-sm" maxlength="255" required>
        </div>

        <div class="col-md-3">
          <label class="form-label form-label-sm mb-1">RFC</label>
          <input type="text" name="supplier[rfc]"
            value="{{ old('supplier.rfc', $supplier->rfc ?? '') }}"
            class="form-control form-control-sm text-uppercase" maxlength="13" required>
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Régimen fiscal</label>
          <select name="supplier[tax_regime]" class="form-select form-select-sm">
            <option value="" disabled {{ old('supplier.tax_regime', $supplier->tax_regime ?? '') == '' ? 'selected' : '' }}>Seleccionar...</option>
            <option value="individual"   @selected(old('supplier.tax_regime', $supplier->tax_regime ?? '') === 'individual')>Persona Física</option>
            <option value="corporation"  @selected(old('supplier.tax_regime', $supplier->tax_regime ?? '') === 'corporation')>Persona Moral</option>
            <option value="resico"       @selected(old('supplier.tax_regime', $supplier->tax_regime ?? '') === 'resico')>RESICO</option>
          </select>
        </div>

        {{-- Teléfonos --}}
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Teléfono empresa</label>
          <input type="text" name="supplier[phone_number]"
            value="{{ old('supplier.phone_number', $supplier->phone_number ?? '') }}"
            class="form-control form-control-sm" maxlength="15"
            placeholder="Ej. 6561234567">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Nombre del contacto</label>
          <input type="text" name="supplier[contact_person]"
            value="{{ old('supplier.contact_person', $supplier->contact_person ?? '') }}"
            class="form-control form-control-sm" maxlength="100">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Teléfono del contacto</label>
          <input type="text" name="supplier[contact_phone]"
            value="{{ old('supplier.contact_phone', $supplier->contact_phone ?? '') }}"
            class="form-control form-control-sm" maxlength="10">
        </div>

        {{-- Correo y tipo --}}
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Correo del proveedor</label>
          <input type="email" name="supplier[email]"
            value="{{ old('supplier.email', $supplier->email ?? '') }}"
            class="form-control form-control-sm" maxlength="150">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Tipo de proveedor</label>
          <select name="supplier[supplier_type]" class="form-select form-select-sm">
            <option value="" disabled {{ old('supplier.supplier_type', $supplier->supplier_type ?? '') == '' ? 'selected' : '' }}>Seleccionar...</option>
            <option value="product"         @selected(old('supplier.supplier_type', $supplier->supplier_type ?? '') === 'product')>Productos</option>
            <option value="service"         @selected(old('supplier.supplier_type', $supplier->supplier_type ?? '') === 'service')>Servicios</option>
            <option value="product_service" @selected(old('supplier.supplier_type', $supplier->supplier_type ?? '') === 'product_service')>Productos y Servicios</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Estatus del proveedor</label>
          <select name="supplier[status]" class="form-select form-select-sm">
            <option value="pending_docs" @selected(old('supplier.status', $supplier->status ?? '') === 'pending_docs')>Pendiente de documentos</option>
            <option value="approved"     @selected(old('supplier.status', $supplier->status ?? '') === 'approved')>Información completa</option>
            <option value="rejected"     @selected(old('supplier.status', $supplier->status ?? '') === 'rejected')>Rechazado</option>
          </select>
        </div>

        {{-- Actividad económica --}}
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Actividad económica</label>
          <input type="text" name="supplier[economic_activity]"
            value="{{ old('supplier.economic_activity', $supplier->economic_activity ?? '') }}"
            class="form-control form-control-sm" maxlength="150"
            placeholder="Ej. Venta y distribución de productos industriales">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Moneda de pago</label>
          <select name="supplier[currency]" class="form-select form-select-sm" id="currencySelect">
            @foreach(($currencies ?? ['MXN' => 'MXN - Peso Mexicano', 'USD' => 'USD - Dólar Americano']) as $k => $label)
              <option value="{{ $k }}" @selected(old('supplier.currency', $supplier->currency ?? 'MXN') === $k)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        {{-- Condiciones de pago por defecto --}}
        @php
          $currentPaymentTerm = old('supplier.default_payment_terms',
            ($supplier->default_payment_terms instanceof \App\Enum\PaymentTerm
              ? $supplier->default_payment_terms->value
              : ($supplier->default_payment_terms ?? 'CASH'))
          );
        @endphp
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Condiciones de pago <span class="text-muted fw-normal">(por defecto)</span></label>
          <select name="supplier[default_payment_terms]" class="form-select form-select-sm">
            @foreach(\App\Enum\PaymentTerm::options() as $value => $label)
              <option value="{{ $value }}" @selected($currentPaymentTerm === $value)>{{ $label }}</option>
            @endforeach
          </select>
          <div class="form-text">Valor por defecto en OC, cotizaciones, etc.</div>
        </div>

        {{-- Dirección --}}
        <div class="col-12">
          <label class="form-label form-label-sm mb-1">Dirección fiscal</label>
          <textarea name="supplier[address]" rows="2" class="form-control form-control-sm"
            maxlength="500">{{ old('supplier.address', $supplier->address ?? '') }}</textarea>
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         SECCIÓN 3: DATOS BANCARIOS — MÉXICO
    ═══════════════════════════════════════════════════ --}}
    <div class="border rounded p-3 mb-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3 fs-12">
        <i class="ti ti-building-bank me-1"></i> Datos Bancarios — México
      </h6>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Banco</label>
          <input type="text" name="supplier[bank_name]"
            value="{{ old('supplier.bank_name', $supplier->bank_name ?? '') }}"
            class="form-control form-control-sm" maxlength="100"
            placeholder="Ej. BBVA, Banamex, Santander">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">No. de cuenta</label>
          <input type="text" name="supplier[account_number]"
            value="{{ old('supplier.account_number', $supplier->account_number ?? '') }}"
            class="form-control form-control-sm" maxlength="20">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">CLABE interbancaria</label>
          <input type="text" name="supplier[clabe]"
            value="{{ old('supplier.clabe', $supplier->clabe ?? '') }}"
            class="form-control form-control-sm" maxlength="18"
            placeholder="18 dígitos">
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         SECCIÓN 4: DATOS BANCARIOS — INTERNACIONAL
    ═══════════════════════════════════════════════════ --}}
    <div class="border rounded p-3 mb-3" id="intlBankingSection">
      <h6 class="text-uppercase text-muted fw-semibold mb-1 fs-12">
        <i class="ti ti-world me-1"></i> Datos Bancarios — Internacional
        <span class="badge bg-light text-muted fw-normal ms-1" style="font-size:10px">Para pagos en USD u otras divisas</span>
      </h6>
      <p class="text-muted small mb-3">Completa solo si el proveedor recibe pagos internacionales.</p>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">Banco (internacional)</label>
          <input type="text" name="supplier[us_bank_name]"
            value="{{ old('supplier.us_bank_name', $supplier->us_bank_name ?? '') }}"
            class="form-control form-control-sm" maxlength="100">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">SWIFT / BIC</label>
          <input type="text" name="supplier[swift_bic]"
            value="{{ old('supplier.swift_bic', $supplier->swift_bic ?? '') }}"
            class="form-control form-control-sm text-uppercase" maxlength="11"
            placeholder="Ej. BMSXMXMM">
        </div>

        <div class="col-md-4">
          <label class="form-label form-label-sm mb-1">ABA / Routing Number</label>
          <input type="text" name="supplier[aba_routing]"
            value="{{ old('supplier.aba_routing', $supplier->aba_routing ?? '') }}"
            class="form-control form-control-sm" maxlength="9"
            placeholder="9 dígitos (EE.UU.)">
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">IBAN</label>
          <input type="text" name="supplier[iban]"
            value="{{ old('supplier.iban', $supplier->iban ?? '') }}"
            class="form-control form-control-sm text-uppercase" maxlength="34"
            placeholder="Hasta 34 caracteres">
        </div>

        <div class="col-md-6">
          <label class="form-label form-label-sm mb-1">Ciudad y país del banco</label>
          <input type="text" name="supplier[bank_address]"
            value="{{ old('supplier.bank_address', $supplier->bank_address ?? '') }}"
            class="form-control form-control-sm" maxlength="255"
            placeholder="Ej. Nueva York, EE.UU.">
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         SECCIÓN 5: SERVICIOS ESPECIALIZADOS / REPSE
    ═══════════════════════════════════════════════════ --}}
    @php
      $currentServiceTypes = is_array($supplier->specialized_services_types ?? null)
          ? $supplier->specialized_services_types
          : json_decode($supplier->specialized_services_types ?? '[]', true) ?? [];
      $selectedServiceTypes = old('supplier', [])['specialized_services_types'] ?? $currentServiceTypes;

      $serviceTypeOptions = [
          'limpieza_mantenimiento' => 'Limpieza y Mantenimiento',
          'construccion'           => 'Construcción',
          'seguridad'              => 'Seguridad',
          'transporte'             => 'Transporte y Logística',
          'ti_telecomunicaciones'  => 'TI y Telecomunicaciones',
          'consultoria'            => 'Consultoría',
          'manufactura'            => 'Manufactura',
          'otros'                  => 'Otros',
      ];

      $providesSpecialized = old('supplier.provides_specialized_services', $supplier->provides_specialized_services ?? false);
    @endphp

    <div class="border rounded p-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3 fs-12">
        <i class="ti ti-certificate me-1"></i> Servicios Especializados / REPSE
      </h6>

      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="providesSpecializedSwitch"
          name="supplier[provides_specialized_services]" value="1"
          {{ $providesSpecialized ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="providesSpecializedSwitch">
          Este proveedor presta servicios especializados (REPSE)
        </label>
      </div>

      <div id="repseFields" class="{{ $providesSpecialized ? '' : 'd-none' }}">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label form-label-sm mb-1">No. de registro REPSE</label>
            <input type="text" name="supplier[repse_registration_number]"
              value="{{ old('supplier.repse_registration_number', $supplier->repse_registration_number ?? '') }}"
              class="form-control form-control-sm"
              placeholder="Ej. REPSE-000000">
          </div>

          <div class="col-md-4">
            <label class="form-label form-label-sm mb-1">Vigencia del registro</label>
            <input type="date" name="supplier[repse_expiry_date]"
              value="{{ old('supplier.repse_expiry_date', $supplier->repse_expiry_date ? \Carbon\Carbon::parse($supplier->repse_expiry_date)->format('Y-m-d') : '') }}"
              class="form-control form-control-sm">
          </div>

          <div class="col-md-12">
            <label class="form-label form-label-sm mb-1">Tipos de servicio especializado</label>
            <div class="row g-2">
              @foreach($serviceTypeOptions as $value => $label)
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                      name="supplier[specialized_services_types][]"
                      value="{{ $value }}"
                      id="sst_{{ $value }}"
                      {{ in_array($value, (array) $selectedServiceTypes) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="sst_{{ $value }}">{{ $label }}</label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /modal-body --}}

  <div class="modal-footer py-2 border-top">
    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
      <i class="ti ti-x me-1"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-primary btn-sm">
      <i class="ti ti-device-floppy me-1"></i> Guardar cambios
    </button>
  </div>
</form>

<script>
(function () {
  // ── Toggle sección REPSE ───────────────────────────────────────────
  var repseSwitch = document.getElementById('providesSpecializedSwitch');
  var repseFields = document.getElementById('repseFields');

  if (repseSwitch && repseFields) {
    repseSwitch.addEventListener('change', function () {
      repseFields.classList.toggle('d-none', !this.checked);
    });
  }

  // ── Preview de avatar ──────────────────────────────────────────────
  var avatarInput = document.getElementById('avatarInput');
  if (avatarInput) {
    avatarInput.addEventListener('change', function () {
      var file = this.files[0];
      if (file) {
        document.getElementById('avatarPreview').src = URL.createObjectURL(file);
        document.getElementById('removeAvatarFlag').value = '0';
      }
    });
  }

  // ── Quitar avatar ──────────────────────────────────────────────────
  var btnRemove = document.getElementById('btnRemoveAvatar');
  if (btnRemove) {
    btnRemove.addEventListener('click', function () {
      document.getElementById('avatarPreview').src = '{{ asset("assets/img/avatar-placeholder.png") }}';
      document.getElementById('removeAvatarFlag').value = '1';
      if (avatarInput) avatarInput.value = '';
    });
  }
})();
</script>
