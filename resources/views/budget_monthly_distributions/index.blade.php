@extends('layouts.zircos')

@section('title', 'Distribuciones Mensuales')

@section('page.title', 'Distribuciones Mensuales')

@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item"><a href="{{ route('annual_budgets.index') }}">Presupuestos Anuales</a></li>
<li class="breadcrumb-item active">Distribuciones</li>
@endsection

@section('content')

<!-- ===== HEADER ===== -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        @if ($budget)
        <h5 class="mb-1">{{ $budget->costCenter?->company?->name ?? '—' }}</h5>
        <p class="text-muted mb-0">
            <strong>[{{ $budget->costCenter?->code ?? '—' }}]</strong>
            {{ $budget->costCenter?->name ?? '—' }}
            · <span class="badge bg-light text-dark">{{ $budget->fiscal_year }}</span>
        </p>
        @else
        <h5 class="mb-1">Distribuciones Mensuales</h5>
        <p class="text-muted mb-0">Consulta de distribuciones presupuestales por mes y categoría.</p>
        @endif
    </div>

    @if ($budget)
    <div class="d-flex gap-2">
        @if ($budget->status === 'PLANIFICACION')
        @if ($budget->monthlyDistributions->count() === 0)
        {{-- No hay distribuciones, mostrar botón crear --}}
        <a href="{{ route('budget_monthly_distributions.create', $budget) }}"
            class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i> Crear Distribuciones
        </a>
        @else
        {{-- Ya hay distribuciones, mostrar botón editar --}}
        <a href="{{ route('budget_monthly_distributions.edit', $budget->id) }}"
            class="btn btn-warning btn-sm">
            <i class="ti ti-edit me-1"></i> Editar Distribuciones
        </a>
        @endif
        @endif

        <a href="{{ route('annual_budgets.show', $budget->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i> Volver a Presupuesto
        </a>
    </div>
    @endif
</div>

<!-- ===== ALERTAS ===== -->
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ti ti-check me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="ti ti-alert-circle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- ===== FILTROS (solo si NO hay budget específico) ===== -->
@if (!$budget)
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Presupuesto Anual</label>
                <select id="filterBudget" class="form-select">
                    <option value="">-- Selecciona un presupuesto --</option>
                    @php
                    $groupedBudgets = $budgets->groupBy('fiscal_year');
                    @endphp
                    @foreach ($groupedBudgets as $year => $yearBudgets)
                    <optgroup label="Ejercicio {{ $year }}">
                        @foreach ($yearBudgets as $b)
                        <option value="{{ $b->id }}">
                            [{{ $b->costCenter?->code ?? '—' }}]
                            {{ $b->costCenter?->name ?? '—' }}
                            - {{ $b->costCenter?->company?->name ?? '—' }}
                            @if ($b->status !== 'APROBADO')
                            - ({{ $b->status_label }})
                            @endif
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" id="btnReset">
                    <i class="ti ti-refresh me-1"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ===== TABLA DATATABLE ===== -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableDistributions" class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mes</th>
                        <th>Categoría</th>
                        <th class="text-end">Asignado</th>
                        <th class="text-end">Consumido</th>
                        <th class="text-end">Comprometido</th>
                        <th class="text-end">Disponible</th>
                        <th class="text-end">% Uso</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // prettier-ignore
        const budgetId = {{ isset($budget) ? json_encode($budget->id) : 'null' }};

        // 🔴 INICIALIZAR SELECT2
        @if(!$budget)
        const filterBudget = $('#filterBudget'); // jQuery selector

        filterBudget.select2({
            theme: 'bootstrap-5',
            placeholder: '-- Selecciona un presupuesto --',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
        @endif

        // Configuración base de DataTable
        const dataTableConfig = {
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('budget_monthly_distributions.datatable')),
                data: function(d) {
                    let selectedBudgetId = budgetId;

                    if (!budgetId) {
                        const filterBudgetElement = document.getElementById('filterBudget');
                        selectedBudgetId = filterBudgetElement ? filterBudgetElement.value : null;
                    }

                    console.log('Enviando budget_id:', selectedBudgetId);
                    d.annual_budget_id = selectedBudgetId || '';
                },
                error: function(xhr, error, code) {
                    console.error('Error en DataTable:', error, code);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                }
            },
            columns: [{
                    data: 'month_label',
                    name: 'month'
                },
                {
                    data: 'category_code',
                    name: 'expense_category_id'
                },
                {
                    data: 'assigned_amount',
                    name: 'assigned_amount',
                    className: 'text-end'
                },
                {
                    data: 'consumed_amount',
                    name: 'consumed_amount',
                    className: 'text-end'
                },
                {
                    data: 'committed_amount',
                    name: 'committed_amount',
                    className: 'text-end'
                },
                {
                    data: 'available_amount',
                    name: 'available_amount',
                    className: 'text-end'
                },
                {
                    data: 'usage_percentage',
                    name: 'usage_percentage',
                    className: 'text-end'
                },
                {
                    data: 'status_label',
                    name: 'status',
                    className: 'text-center'
                },
            ],
            order: [
                [0, 'asc'],
                [1, 'asc']
            ],
            pageLength: 100,
            language: {
                url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
            },
        };

        // Si no hay budget_id, no cargar datos automáticamente
        if (!budgetId) {
            dataTableConfig.deferLoading = 0;
        }

        // Inicializar DataTable
        const table = new DataTable('#tableDistributions', dataTableConfig);

        @if(!$budget)
        // 🔴 Event listeners para Select2 y botón Reset
        const btnReset = document.getElementById('btnReset');

        // Select2 usa el evento 'change' de jQuery
        filterBudget.on('change', function() {
            const value = $(this).val();
            console.log('Presupuesto seleccionado:', value);

            if (value) {
                table.ajax.reload();
            } else {
                table.clear().draw();
            }
        });

        if (btnReset) {
            btnReset.addEventListener('click', function() {
                console.log('Filtro limpiado');
                // Limpiar Select2
                filterBudget.val(null).trigger('change');
                // Limpiar tabla
                table.clear().draw();
            });
        }

        // Mostrar mensaje cuando no hay budget seleccionado
        if (!budgetId) {
            $('#tableDistributions tbody').html(
                '<tr><td colspan="8" class="text-center text-muted py-5">' +
                '<i class="ti ti-filter" style="font-size: 3rem;"></i><br><br>' +
                '<strong>Selecciona un presupuesto anual</strong><br>' +
                'Usa el filtro de arriba para ver las distribuciones mensuales' +
                '</td></tr>'
            );
        }
        @endif
    });
</script>
@endpush