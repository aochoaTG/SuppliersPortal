<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $department->name ?? '') }}" maxlength="100" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Abreviación <span class="text-danger">*</span></label>
        <input type="text" name="abbreviated" class="form-control @error('abbreviated') is-invalid @enderror"
            value="{{ old('abbreviated', $department->abbreviated ?? '') }}" maxlength="10" required>
        <div class="form-text">Mayúsculas, sin acentos/espacios (p.ej. ADF, OPE).</div>
        @error('abbreviated')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            {{-- 👇 hidden para cuando el checkbox esté apagado --}}
            <input type="hidden" name="is_active" value="0">

            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', $department->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Notas</label>
        <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" maxlength="255">{{ old('notes', $department->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> Guardar
        </button>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Volver
        </a>
    </div>
</div>
