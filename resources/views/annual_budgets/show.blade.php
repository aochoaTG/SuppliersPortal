@extends('layouts.zircos')

@section('title', 'Presupuesto Anual ' . $annual_budget->fiscal_year)

@section('page.title', 'Presupuesto ' . $annual_budget->fiscal_year)

@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item"><a href="{{ route('annual_budgets.index') }}">Presupuestos Anuales</a></li>
<li class="breadcrumb-item active">{{ $annual_budget->fiscal_year }}</li>
@endsection

@section('content')
<div class="content">
    <!-- ===== HEADER ===== -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <p class="text-muted mb-2">
                {{ $annual_budget->costCenter?->company?->name ?? '—' }} /
                <strong>[{{ $annual_budget->costCenter?->code ?? '—' }}]</strong>
                {{ $annual_budget->costCenter?->name ?? '—' }}
            </p>
        </div>
        <div>
            <a href="{{ route('annual_budgets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- ===== TARJETAS RESUMEN ===== -->
    <div class="row g-3 mb-4">
        <!-- Asignado -->
        <div class="col-md-3">
            <div class="card border-start border-primary border-4">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Presupuesto Asignado</small>
                    <h4 class="mb-0">${{ number_format($summary['total_assigned'], 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Comprometido -->
        <div class="col-md-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Comprometido</small>
                    <h4 class="mb-0">${{ number_format($summary['total_committed'], 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Consumido -->
        <div class="col-md-3">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Consumido</small>
                    <h4 class="mb-0">${{ number_format($summary['total_consumed'], 2) }}</h4>
                </div>
            </div>
        </div>

        <!-- Disponible -->
        <div class="col-md-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Disponible</small>
                    <h4 class="mb-0">${{ number_format($summary['total_available'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== INFORMACIÓN GENERAL ===== -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Información General</h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <div>
                    <label class="form-label text-muted small">Año Fiscal</label>
                    <p class="fw-semibold mb-0">{{ $annual_budget->fiscal_year }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <label class="form-label text-muted small">Estado</label>
                    <p class="mb-0">
                        @if ($annual_budget->status === 'PLANIFICACION')
                        <span class="badge bg-info text-white">En Planificación</span>
                        @elseif ($annual_budget->status === 'APROBADO')
                        <span class="badge bg-success text-white">Aprobado</span>
                        @else
                        <span class="badge bg-secondary text-white">Cerrado</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <label class="form-label text-muted small">% Utilización</label>
                    <p class="fw-semibold mb-0">{{ number_format($summary['usage_percentage'], 1) }}%</p>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <label class="form-label text-muted small">Creado Por</label>
                    <p class="mb-0">{{ $annual_budget->createdBy?->name ?? '—' }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <label class="form-label text-muted small">Aprobado Por</label>
                    <p class="mb-0">{{ $annual_budget->approvedBy?->name ?? '—' }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <label class="form-label text-muted small">Aprobado En</label>
                    <p class="mb-0">
                        @if ($annual_budget->approved_at)
                        {{ $annual_budget->approved_at->format('d/m/Y H:i') }}
                        @else
                        —
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== BARRA DE PROGRESO ===== -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Utilización del Presupuesto</h6>
    </div>
    <div class="card-body">
        <div class="progress" style="height: 25px;">
            @php
            $usagePercentage = $summary['usage_percentage'];
            $progressColor = match (true) {
            $usagePercentage > 90 => 'bg-danger',
            $usagePercentage > 70 => 'bg-warning',
            $usagePercentage > 50 => 'bg-info',
            default => 'bg-success',
            };
            @endphp
            <div class="progress-bar {{ $progressColor }}" role="progressbar"
                style="width: {{ $usagePercentage }}%" aria-valuenow="{{ $usagePercentage }}" aria-valuemin="0"
                aria-valuemax="100">
                <small class="fw-semibold">{{ number_format($usagePercentage, 1) }}%</small>
            </div>
        </div>
        <div class="row g-3 mt-3">
            <div class="col-sm-6">
                <small class="text-muted">Consumido + Comprometido</small>
                <p class="fw-semibold mb-0">
                    ${{ number_format($summary['total_consumed'] + $summary['total_committed'], 2) }}</p>
            </div>
            <div class="col-sm-6">
                <small class="text-muted">Disponible para Gastar</small>
                <p class="fw-semibold mb-0">${{ number_format($summary['total_available'], 2) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- ===== DISTRIBUCIONES MENSUALES ===== -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Distribuciones Mensuales por Categoría</h6>
        <a href="{{ route('budget_monthly_distributions.matrix', $annual_budget->id) }}"
            class="btn btn-sm btn-outline-primary">
            <i class="ti ti-layout-grid me-1"></i> Vista Matriz
        </a>
    </div>
    <div class="card-body table-responsive">
        <table class="table-sm table-hover mb-0 table">
            <thead class="table-light">
                <tr>
                    <th>Mes</th>
                    <th>Categoría</th>
                    <th class="text-end">Asignado</th>
                    <th class="text-end">Consumido</th>
                    <th class="text-end">Comprometido</th>
                    <th class="text-end">Disponible</th>
                    <th class="text-end">% Uso</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($annual_budget->monthlyDistributions->sortBy('month') as $dist)
                <tr>
                    <td>
                        <span class="badge bg-light text-dark">{{ $dist->month_label }}</span>
                    </td>
                    <td>
                        <small>
                            <strong>[{{ $dist->expenseCategory?->code }}]</strong>
                            {{ $dist->expenseCategory?->name }}
                        </small>
                    </td>
                    <td class="text-end">${{ number_format($dist->assigned_amount, 2) }}</td>
                    <td class="text-end">${{ number_format($dist->consumed_amount, 2) }}</td>
                    <td class="text-end">${{ number_format($dist->committed_amount, 2) }}</td>
                    <td class="text-end">
                        @php $avail = $dist->getAvailableAmount(); @endphp
                        @if ($avail > 0)
                        <span class="badge bg-success text-white">${{ number_format($avail, 2) }}</span>
                        @elseif ($avail == 0)
                        <span class="badge bg-warning text-dark">$0.00</span>
                        @else
                        <span class="badge bg-danger text-white">${{ number_format($avail, 2) }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @php $pct = $dist->getUsagePercentage(); @endphp
                        @if ($pct > 90)
                        <span class="badge bg-danger text-white">{{ number_format($pct, 1) }}%</span>
                        @elseif ($pct > 70)
                        <span class="badge bg-warning text-dark">{{ number_format($pct, 1) }}%</span>
                        @else
                        <span class="badge bg-success text-white">{{ number_format($pct, 1) }}%</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-muted py-3 text-center">
                        <i class="ti ti-inbox me-1"></i> Sin distribuciones mensuales
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection