@extends('layouts.zircos')

@section('title', 'Crear Presupuesto Anual')

@section('page.title', 'Crear Presupuesto Anual')

@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item"><a href="{{ route('annual_budgets.index') }}">Presupuestos Anuales</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="ti ti-plus-circle me-2"></i>
                    Crear Nuevo Presupuesto Anual
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('annual_budgets.store') }}" method="POST" novalidate id="formBudget">
                    @csrf
                    @include('annual_budgets.partials.form', ['action' => 'create'])
                </form>
            </div>
        </div>
    </div>

    <!-- Información útil -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Información</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    Un presupuesto anual define el monto total disponible para un centro de costo en un año fiscal.
                </p>
                <hr class="my-3">
                <h6 class="small fw-semibold mb-2">Pasos a seguir:</h6>
                <ol class="small text-muted ps-3">
                    <li>Selecciona el centro de costo</li>
                    <li>Especifica el año fiscal</li>
                    <li>Ingresa el monto total anual</li>
                    <li>Guarda y continúa con distribución mensual</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="ti ti-alert-circle me-2"></i>Importante</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info alert-sm mb-0" role="alert">
                    <small>
                        <i class="ti ti-info-circle me-1"></i>
                        Solo se pueden crear presupuestos para centros de costo de tipo <strong>ANUAL</strong>.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formBudget');

        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
</script>
@endpush