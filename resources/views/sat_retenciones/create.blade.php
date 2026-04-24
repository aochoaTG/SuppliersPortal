@extends('layouts.zircos')

@section('title', 'Nueva Retención SAT')
@section('page.title', 'Nueva Retención SAT')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sat-retenciones.index') }}">Retenciones SAT</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
    <form action="{{ route('sat-retenciones.store') }}" method="POST" class="card">
        @csrf

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-receipt-tax me-1"></i> Nueva Retención SAT</h5>
        </div>

        <div class="card-body">
            @include('sat_retenciones.partials.form', ['sat_retencion' => $sat_retencion])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('sat-retenciones.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Guardar
            </button>
        </div>
    </form>
@endsection
