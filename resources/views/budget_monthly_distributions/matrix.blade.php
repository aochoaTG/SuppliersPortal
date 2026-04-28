@extends('layouts.zircos')

@section('title', 'Matriz Presupuestal')

@section('page.title', 'Matriz Presupuestal')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Matriz mensual por categoría</h5>
            <small class="text-muted">[{{ $budget->costCenter?->code }}] {{ $budget->costCenter?->name }} · {{ $budget->fiscal_year }}</small>
        </div>
        <a href="{{ route('annual_budgets.show', $budget->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i> Volver
        </a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Mes</th>
                    @foreach ($categories as $category)
                        <th>[{{ $category->code }}] {{ $category->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($month = 1; $month <= 12; $month++)
                    <tr>
                        <th>{{ \App\Models\BudgetMonthlyDistribution::make(['month' => $month])->month_label }}</th>
                        @foreach ($categories as $category)
                            @php $cell = $matrix[$month][$category->id] ?? null; @endphp
                            <td class="text-end">
                                {{ $cell ? '$' . number_format($cell['assigned_amount'], 2) : '—' }}
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection
