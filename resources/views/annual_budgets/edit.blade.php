@extends('layouts.zircos')

@section('title', 'Editar Presupuesto Anual')
@section('page.title', 'Editar Presupuesto Anual')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('annual-budgets.index') }}">Presupuestos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <form action="{{ route('annual-budgets.update', $annualBudget->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-cash"></i> Editar Presupuesto</h5>
        </div>

        <div class="card-body">
            @include('annual_budgets.partials.form', [
                'annualBudget' => $annualBudget,
                'companies' => $companies,
                'costCenters' => $costCenters,
            ])

            {{-- Panel opcional con saldos (usa $totals del controlador) --}}
            @isset($totals)
                <hr>
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Comprometido</div>
                                <div class="fw-bold">{{ number_format($totals['committed'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Consumido</div>
                                <div class="fw-bold">{{ number_format($totals['consumed'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card {{ ($totals['available'] ?? 0) < 0 ? 'bg-danger text-white' : 'bg-light' }}">
                            <div class="card-body">
                                <div class="text-muted small">Disponible</div>
                                <div class="fw-bold">{{ number_format($totals['available'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <a href="{{ route('budget-movements.index', ['annual_budget_id' => $annualBudget->id]) }}"
                            class="btn btn-outline-secondary">
                            <i class="ti ti-activity"></i> Ver bitácora de movimientos
                        </a>
                    </div>
                </div>
            @endisset
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('annual-budgets.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Regresar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Actualizar
            </button>
        </div>
    </form>
@endsection
