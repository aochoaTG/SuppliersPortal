@extends('layouts.zircos')

@section('title', 'Crear Presupuesto Anual')
@section('page.title', 'Crear Presupuesto Anual')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('annual-budgets.index') }}">Presupuestos</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
    <form action="{{ route('annual-budgets.store') }}" method="POST" class="card">
        @csrf

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-cash"></i> Nuevo Presupuesto</h5>
        </div>

        <div class="card-body">
            @include('annual_budgets.partials.form', [
                'annualBudget' => $annualBudget,
                'companies' => $companies,
                'costCenters' => $costCenters,
            ])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('annual-budgets.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Regresar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Guardar
            </button>
        </div>
    </form>
@endsection
