{{-- Formulario parcial reutilizable para create y edit --}}
@php
    // Helper para valores por defecto en edit/create
    $isActiveChecked = old('is_active', $category->is_active ?? true) ? true : false;
@endphp

<div class="row g-3">
    {{-- Nombre --}}
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $category->name ?? '') }}" placeholder="ADMINISTRACION, ENPROYECTO, STAFF, ...">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Único, corto, se recomienda mayúsculas para consistencia.</div>
    </div>

    {{-- Descripción --}}
    <div class="col-md-6">
        <label for="description" class="form-label">Descripción</label>
        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description"
            name="description" value="{{ old('description', $category->description ?? '') }}"
            placeholder="Contexto opcional para contabilidad/operaciones.">
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Activo --}}
    <div class="col-md-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active"
                value="1" {{ $isActiveChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Activo</label>
        </div>
        <div class="form-text">Si está desactivado, los usuarios no podrán seleccionar esta categoría.</div>
    </div>
</div>
