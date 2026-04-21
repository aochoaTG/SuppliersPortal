@extends('layouts.zircos')

@section('title', 'Preview de importación')
@section('page.title', 'Preview de importación de centros de costo')
@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item"><a href="{{ route('cost-centers.index') }}">Centros de Costo</a></li>
<li class="breadcrumb-item active">Preview de importación</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ti ti-table-import me-1"></i> Preview de importación</h5>
        <span class="badge {{ $preview['can_import'] ? 'bg-success' : 'bg-danger' }}">
            {{ $preview['can_import'] ? 'Listo para importar' : 'Con errores' }}
        </span>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Archivo</div>
                    <div class="fw-semibold">{{ $preview['original_filename'] ?? '—' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Filas procesadas</div>
                    <div class="fs-4 fw-semibold">{{ $preview['total_rows'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Filas válidas</div>
                    <div class="fs-4 fw-semibold text-success">{{ $preview['valid_rows_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Filas con error</div>
                    <div class="fs-4 fw-semibold text-danger">{{ $preview['error_rows_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="mb-2">Columnas detectadas</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($preview['columns_detected'] as $column)
                <span class="badge bg-light text-dark border">{{ $column }}</span>
                @endforeach
            </div>
        </div>

        @if (!empty($preview['errors']))
        <div class="mb-4">
            <h6 class="mb-2 text-danger">Errores detectados</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Fila</th>
                            <th style="width: 180px;">Columna</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['errors'] as $error)
                        <tr>
                            <td>{{ $error['row'] > 0 ? $error['row'] : 'Archivo' }}</td>
                            <td>{{ $error['column'] }}</td>
                            <td>{{ $error['message'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if (!empty($preview['valid_rows']))
        <div>
            <h6 class="mb-2">Filas válidas</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fila</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Empresa</th>
                            <th>Categoría</th>
                            <th>Responsable</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['valid_rows'] as $row)
                        <tr>
                            <td>{{ $row['row_number'] }}</td>
                            <td>{{ $row['values']['codigo'] }}</td>
                            <td>{{ $row['values']['nombre'] }}</td>
                            <td>{{ $row['values']['empresa'] }}</td>
                            <td>{{ $row['values']['categoria'] }}</td>
                            <td>{{ $row['values']['responsable'] }}</td>
                            <td>{{ $row['values']['tipo_presupuesto'] }}</td>
                            <td>{{ $row['values']['estado'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('cost-centers.index') }}" class="btn btn-light">
            <i class="ti ti-arrow-left me-1"></i> Regresar
        </a>
        <form action="{{ route('cost-centers.import.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" {{ $preview['can_import'] ? '' : 'disabled' }}>
                <i class="ti ti-check me-1"></i> Confirmar importación
            </button>
        </form>
    </div>
</div>
@endsection
