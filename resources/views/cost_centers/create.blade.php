@extends('layouts.zircos')

@section('title', 'Crear Centro de Costo')
@section('page.title', 'Crear Centro de Costo')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cost-centers.index') }}">Centros de Costo</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
    <form action="{{ route('cost-centers.store') }}" method="POST" class="card">
        @csrf

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-hierarchy-2"></i> Nuevo Centro de Costo</h5>
        </div>

        <div class="card-body">
            @include('cost_centers.partials.form', [
                'costCenter' => $costCenter,
                'categories' => $categories,
            ])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('cost-centers.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Regresar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Guardar
            </button>
        </div>
    </form>
@endsection
