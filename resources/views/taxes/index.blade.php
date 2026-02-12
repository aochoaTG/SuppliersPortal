@extends('layouts.zircos')

{{-- TÍTULO DE LA PÁGINA --}}
@section('title', 'Impuestos')

@section('page.title', 'Listado de Impuestos')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
    <li class="breadcrumb-item active">Impuestos</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Impuestos</h5>
        <a href="{{ route('taxes.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i> Nuevo
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if($taxes->count())
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle w-100">
                    <thead>
                        <tr>
                            <th style="width:45%;">Nombre</th>
                            <th class="text-end" style="width:20%;">Tasa (%)</th>
                            <th style="width:15%;">Activo</th>
                            <th class="text-end" style="width:20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($taxes as $tax)
                            <tr>
                                <td>{{ $tax->name }}</td>
                                <td class="text-end">{{ number_format($tax->rate_percent, 2) }}</td>
                                <td>
                                    @if($tax->is_active)
                                        <span class="badge bg-success">Sí</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i> Editar
                                    </a>
                                    <form action="{{ route('taxes.destroy', $tax) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('¿Eliminar este impuesto?');">
                                            <i class="ti ti-trash me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $taxes->links() }}
        @else
            <div class="text-muted">No hay impuestos registrados.</div>
        @endif
    </div>
</div>
@endsection
