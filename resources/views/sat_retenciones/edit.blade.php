@extends('layouts.zircos')

@section('title', 'Editar Retención SAT')
@section('page.title', 'Editar Retención SAT')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sat-retenciones.index') }}">Retenciones SAT</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <form action="{{ route('sat-retenciones.update', $sat_retencion->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-header">
            <h5 class="mb-0">
                <i class="ti ti-receipt-tax me-1"></i>
                Editar: {{ $sat_retencion->clave }} — {{ $sat_retencion->nombre }}
            </h5>
        </div>

        <div class="card-body">
            @include('sat_retenciones.partials.form', ['sat_retencion' => $sat_retencion])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('sat-retenciones.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Actualizar
            </button>
        </div>
    </form>
@endsection
