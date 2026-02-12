@php
  /** @var \App\Models\User $user */
  $supplier = $user->supplier ?? null;
@endphp

<div class="modal-header">
  <h5 class="modal-title">
    <i class="ti ti-building-store me-2"></i>
    {{ $supplier ? 'Editar datos de proveedor' : 'Agregar datos de proveedor' }}
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="supplierForm"
      action="{{ $supplier ? route('users.supplier.update', $user) : route('users.supplier.store', $user) }}"
      method="POST"
      autocomplete="off">
  @csrf
  @if($supplier)
    @method('PUT')
  @endif

  <div class="modal-body">
    <div id="formErrors" class="alert alert-danger d-none mb-3"></div>

    {{-- ====== Información general ====== --}}
    <div class="border rounded p-3 mb-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3">
        <i class="ti ti-id-badge me-1"></i> Información del proveedor
      </h6>

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Razón social</label>
          <input type="text" name="company_name" class="form-control"
                 value="{{ old('company_name', $supplier->company_name ?? '') }}" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">RFC</label>
          <input type="text" name="rfc" class="form-control"
                 value="{{ old('rfc', $supplier->rfc ?? '') }}" maxlength="13" style="text-transform:uppercase" required>
        </div>

        <div class="col-md-8">
          <label class="form-label">Domicilio</label>
          <input type="text" name="address" class="form-control"
                 value="{{ old('address', $supplier->address ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono</label>
          <input type="text" name="phone_number" class="form-control"
                 value="{{ old('phone_number', $supplier->phone_number ?? '') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control"
                 value="{{ old('email', $supplier->email ?? $user->email) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Actividad económica</label>
          <input type="text" name="economic_activity" class="form-control"
                 value="{{ old('economic_activity', $supplier->economic_activity ?? '') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Tipo de proveedor</label>
          <input type="text" name="supplier_type" class="form-control"
                 placeholder="Servicios, Materiales, Transporte, etc."
                 value="{{ old('supplier_type', $supplier->supplier_type ?? '') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Régimen fiscal</label>
          <input type="text" name="tax_regime" class="form-control"
                 value="{{ old('tax_regime', $supplier->tax_regime ?? '') }}">
        </div>
      </div>
    </div>

    {{-- ====== Contacto ====== --}}
    <div class="border rounded p-3 mb-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3">
        <i class="ti ti-address-book me-1"></i> Contacto
      </h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Persona contacto</label>
          <input type="text" name="contact_person" class="form-control"
                 value="{{ old('contact_person', $supplier->contact_person ?? '') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Teléfono contacto</label>
          <input type="text" name="contact_phone" class="form-control"
                 value="{{ old('contact_phone', $supplier->contact_phone ?? '') }}">
        </div>
      </div>
    </div>

    {{-- ====== REPSE ====== --}}
    <div class="border rounded p-3">
      <h6 class="text-uppercase text-muted fw-semibold mb-3">
        <i class="ti ti-clipboard-check me-1"></i> REPSE
      </h6>

      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch"
               id="provides_specialized_services"
               name="provides_specialized_services"
               value="1" @checked(old('provides_specialized_services', $supplier->provides_specialized_services ?? false))>
        <label class="form-check-label" for="provides_specialized_services">
          ¿Proporciona servicios especializados (REPSE)?
        </label>
      </div>

      <div id="repseFields" class="{{ old('provides_specialized_services', $supplier->provides_specialized_services ?? false) ? '' : 'd-none' }}">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Número de registro REPSE</label>
            <input type="text" name="repse_registration_number" class="form-control"
                   value="{{ old('repse_registration_number', $supplier->repse_registration_number ?? '') }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Vigencia / Fecha de expiración</label>
            <input type="date" name="repse_expiry_date" class="form-control"
                   value="{{ old('repse_expiry_date', optional($supplier->repse_expiry_date ?? null)?->format('Y-m-d')) }}">
          </div>
          <div class="col-12">
            <label class="form-label">Tipos de servicios especializados (coma o Enter para separar)</label>
            <input type="text" name="specialized_services_types"
                   class="form-control"
                   placeholder="Ej: Limpieza industrial, Mantenimiento eléctrico"
                   value="{{ old('specialized_services_types', isset($supplier) && is_array($supplier->specialized_services_types ?? null)
                        ? implode(', ', $supplier->specialized_services_types) : '') }}">
            <div class="form-text">Se guardará como arreglo (JSON).</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Estado inicial (oculto): pending_docs para forzar onboarding de documentos --}}
    <input type="hidden" name="status" value="{{ old('status', $supplier->status ?? 'pending_docs') }}">
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" class="btn btn-primary">
      <i class="ti ti-device-floppy me-1"></i> Guardar
    </button>
  </div>
</form>

<script>
  // Toggle REPSE
  (function () {
    const sw = document.getElementById('provides_specialized_services');
    const box = document.getElementById('repseFields');
    if (sw && box) {
      sw.addEventListener('change', () => {
        box.classList.toggle('d-none', !sw.checked);
      });
    }

    // Envío AJAX del formulario
    const form = document.getElementById('supplierForm');
    const errBox = document.getElementById('formErrors');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errBox.classList.add('d-none'); errBox.innerHTML = '';

      // armar el payload
      const fd = new FormData(form);

      // Normalizar specialized_services_types => array
      const sst = fd.get('specialized_services_types') || '';
      if (sst) {
        const arr = sst.split(',').map(s => s.trim()).filter(Boolean);
        fd.delete('specialized_services_types');
        fd.append('specialized_services_types', JSON.stringify(arr));
      }

      try {
        const res = await fetch(form.action, {
          method: form.querySelector('input[name="_method"]')?.value === 'PUT' ? 'POST' : 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: fd
        });

        if (!res.ok) {
          const data = await res.json().catch(() => ({}));
          if (data?.errors) {
            errBox.classList.remove('d-none');
            errBox.innerHTML = '<strong>Corrige los siguientes campos:</strong><ul class="mb-0">' +
              Object.entries(data.errors).map(([k, v]) => `<li>${v.join('<br>')}</li>`).join('') +
              '</ul>';
          } else {
            errBox.classList.remove('d-none');
            errBox.textContent = 'Ocurrió un error al guardar.';
          }
          return;
        }

        // Éxito: cierra el modal y dispara un evento para recargar la tabla/lista
        const modalEl = form.closest('.modal');
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          modal.hide();
        }
        document.dispatchEvent(new CustomEvent('supplier:updated', { detail: { user_id: {{ $user->id }} } }));
      } catch (err) {
        errBox.classList.remove('d-none');
        errBox.textContent = 'Error de red o servidor. Intenta de nuevo.';
      }
    });
  })();
</script>
