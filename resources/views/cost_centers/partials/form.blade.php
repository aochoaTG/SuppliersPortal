@php
    // Valores por defecto (en create) y persistencia de old()
    $isActiveChecked = old('is_active', $costCenter->is_active ?? true) ? true : false;
@endphp

<div class="row g-3">
    {{-- Código --}}
    <div class="col-md-4">
        <label for="code" class="form-label">Código <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
            value="{{ old('code', $costCenter->code ?? '') }}" placeholder="Ej.: E04188, CORP01, PROY-MIGUEL">
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Identificador único para reportes/integraciones.</div>
    </div>

    {{-- Nombre --}}
    <div class="col-md-8">
        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $costCenter->name ?? '') }}" placeholder="Ej.: Estación 07 Gemela Grande">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Categoría --}}
    <div class="col-md-6">
        <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="@error('category_id') is-invalid @enderror form-select">
            <option value="">-- Selecciona categoría --</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ (int) old('category_id', $costCenter->category_id ?? 0) === (int) $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Empresa (opcional) --}}
    <div class="col-md-6">
        <label for="company_id" class="form-label">Empresa</label>
        <input type="number" class="form-control @error('company_id') is-invalid @enderror" id="company_id"
            name="company_id" value="{{ old('company_id', $costCenter->company_id ?? '') }}"
            placeholder="ID de empresa (opcional)">
        @error('company_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Déjalo vacío si por ahora no aplica multiempresa.</div>
    </div>

    {{-- Activo --}}
    <div class="col-md-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active"
                value="1" {{ $isActiveChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Activo</label>
        </div>
        <div class="form-text">Si está inactivo, no se podrá usar en requisiciones.</div>
    </div>
</div>

@push('scripts')
    {{-- Si usas Select2, inicialízalo aquí (opcional) --}}
    {{-- <script>
        $(function(){
            $('#category_id').select2({ width: '100%' });
        });
    </script> --}}
@endpush
