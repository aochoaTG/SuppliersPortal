@extends('layouts.zircos')

@section('title', 'Editar Nivel de Autorización')
@section('page.title', 'Editar Nivel: ' . $approvalLevel->label)

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('approval-levels.index') }}">Niveles de Autorización</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <form action="{{ route('approval-levels.update', $approvalLevel) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-header bg-gradient-primary text-white border-0 py-3">
                    <h5 class="mb-0"><i class="ti ti-settings-automation me-1"></i> Parámetros de Nivel {{ $approvalLevel->level_number }}</h5>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Aprobador</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="ti ti-user-check"></i></span>
                                <input type="text" name="label" class="form-control" value="{{ old('label', $approvalLevel->label) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Color del Badge (UI)</label>
                            <select name="color_tag" class="form-select" required>
                                <option value="primary" {{ $approvalLevel->color_tag == 'primary' ? 'selected' : '' }}>Azul (Primary)</option>
                                <option value="info" {{ $approvalLevel->color_tag == 'info' ? 'selected' : '' }}>Cian (Info)</option>
                                <option value="success" {{ $approvalLevel->color_tag == 'success' ? 'selected' : '' }}>Verde (Success)</option>
                                <option value="warning" {{ $approvalLevel->color_tag == 'warning' ? 'selected' : '' }}>Naranja (Warning)</option>
                                <option value="danger" {{ $approvalLevel->color_tag == 'danger' ? 'selected' : '' }}>Rojo (Danger)</option>
                                <option value="secondary" {{ $approvalLevel->color_tag == 'secondary' ? 'selected' : '' }}>Gris (Secondary)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Monto Mínimo (MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ old('min_amount', $approvalLevel->min_amount) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Monto Máximo (MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.01" name="max_amount" class="form-control" value="{{ old('max_amount', $approvalLevel->max_amount) }}" placeholder="Dejar vacío si no tiene límite">
                            </div>
                            <small class="text-muted">Vacío = Sin límite superior.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Descripción / Notas Internas</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $approvalLevel->description) }}</textarea>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 d-flex align-items-center" role="alert">
                        <i class="ti ti-info-circle fs-20 me-2"></i>
                        <div>
                            Asegúrate de que los rangos no se traslapen con otros niveles para evitar conflictos en la validación de **SQL Server**.
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light border-0 p-3 text-end">
                    <a href="{{ route('approval-levels.index') }}" class="btn btn-outline-secondary me-1">
                        <i class="ti ti-x me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection