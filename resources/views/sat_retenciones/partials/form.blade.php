{{-- Formulario parcial reutilizable para create y edit --}}
@php
    $activoChecked        = filter_var(old('activo', $sat_retencion->activo ?? true), FILTER_VALIDATE_BOOLEAN);
    $cfdiChecked          = filter_var(old('requiere_cfdi_retencion', $sat_retencion->requiere_cfdi_retencion ?? true), FILTER_VALIDATE_BOOLEAN);
    $impuestoSeleccionado = old('impuesto', $sat_retencion->impuesto ?? '');
@endphp

<div class="row g-3">

    {{-- Clave --}}
    <div class="col-md-6">
        <label for="clave" class="form-label">Clave <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('clave') is-invalid @enderror"
               id="clave" name="clave"
               value="{{ old('clave', $sat_retencion->clave ?? '') }}"
               placeholder="ISR-HON, IVA-ARR…"
               maxlength="20">
        @error('clave')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Identificador único, máx. 20 caracteres. Solo mayúsculas, números y guiones.</div>
    </div>

    {{-- Nombre --}}
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('nombre') is-invalid @enderror"
               id="nombre" name="nombre"
               value="{{ old('nombre', $sat_retencion->nombre ?? '') }}"
               placeholder="ISR Honorarios"
               maxlength="100">
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Impuesto --}}
    <div class="col-md-6">
        <label for="impuesto" class="form-label">Impuesto <span class="text-danger">*</span></label>
        <select class="form-select @error('impuesto') is-invalid @enderror" id="impuesto" name="impuesto">
            <option value="">— Seleccionar —</option>
            <option value="ISR" {{ $impuestoSeleccionado === 'ISR' ? 'selected' : '' }}>ISR</option>
            <option value="IVA" {{ $impuestoSeleccionado === 'IVA' ? 'selected' : '' }}>IVA</option>
        </select>
        @error('impuesto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Base legal --}}
    <div class="col-md-6">
        <label for="base_legal" class="form-label">Base legal <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('base_legal') is-invalid @enderror"
               id="base_legal" name="base_legal"
               value="{{ old('base_legal', $sat_retencion->base_legal ?? '') }}"
               placeholder="ISR Art. 106"
               maxlength="100">
        @error('base_legal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Porcentaje (numérico, nullable) --}}
    <div class="col-md-6">
        <label for="porcentaje" class="form-label">Porcentaje</label>
        <input type="number"
               class="form-control @error('porcentaje') is-invalid @enderror"
               id="porcentaje" name="porcentaje"
               value="{{ old('porcentaje', $sat_retencion->porcentaje ?? '') }}"
               placeholder="10.0000"
               step="0.0001" min="0" max="100">
        @error('porcentaje')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Dejar vacío si la tasa es variable.</div>
    </div>

    {{-- Porcentaje display --}}
    <div class="col-md-6">
        <label for="porcentaje_display" class="form-label">Texto de porcentaje <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('porcentaje_display') is-invalid @enderror"
               id="porcentaje_display" name="porcentaje_display"
               value="{{ old('porcentaje_display', $sat_retencion->porcentaje_display ?? '') }}"
               placeholder="10% o Variable — tabla Art. 96"
               maxlength="100">
        @error('porcentaje_display')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Texto legible que se mostrará al usuario.</div>
    </div>

    {{-- Base de cálculo --}}
    <div class="col-12">
        <label for="base_calculo" class="form-label">Base de cálculo <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('base_calculo') is-invalid @enderror"
               id="base_calculo" name="base_calculo"
               value="{{ old('base_calculo', $sat_retencion->base_calculo ?? '') }}"
               placeholder="Monto del pago"
               maxlength="255">
        @error('base_calculo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Aplica cuando --}}
    <div class="col-12">
        <label for="aplica_cuando" class="form-label">Aplica cuando <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('aplica_cuando') is-invalid @enderror"
               id="aplica_cuando" name="aplica_cuando"
               value="{{ old('aplica_cuando', $sat_retencion->aplica_cuando ?? '') }}"
               placeholder="Persona moral paga a persona física"
               maxlength="255">
        @error('aplica_cuando')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Descripción --}}
    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('descripcion') is-invalid @enderror"
               id="descripcion" name="descripcion"
               value="{{ old('descripcion', $sat_retencion->descripcion ?? '') }}"
               placeholder="Servicios profesionales (persona física)"
               maxlength="255">
        @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Notas --}}
    <div class="col-12">
        <label for="notas" class="form-label">Notas</label>
        <textarea class="form-control @error('notas') is-invalid @enderror"
                  id="notas" name="notas"
                  rows="3"
                  placeholder="Observaciones adicionales, condiciones especiales…">{{ old('notas', $sat_retencion->notas ?? '') }}</textarea>
        @error('notas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Requiere CFDI de retención --}}
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="requiere_cfdi_retencion" value="0">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="requiere_cfdi_retencion" name="requiere_cfdi_retencion"
                   value="1" {{ $cfdiChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="requiere_cfdi_retencion">
                Requiere CFDI de retención
            </label>
        </div>
        <div class="form-text">Indica si se debe emitir CFDI de retenciones e información de pagos.</div>
    </div>

    {{-- Activo --}}
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="activo" value="0">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="activo" name="activo"
                   value="1" {{ $activoChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
        <div class="form-text">Las retenciones inactivas no aparecerán en selectores.</div>
    </div>

</div>
