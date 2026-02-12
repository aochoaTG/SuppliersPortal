@extends('layouts.zircos')

@section('title', 'Editar Presupuesto ' . $annual_budget->fiscal_year)

@section('page.title', 'Editar Presupuesto ' . $annual_budget->fiscal_year)

@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item"><a href="{{ route('annual_budgets.index') }}">Presupuestos Anuales</a></li>
<li class="breadcrumb-item active">{{ $annual_budget->fiscal_year }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ti ti-edit me-2"></i>
                    Editar Presupuesto Anual {{ $annual_budget->fiscal_year }}
                </h6>
                @if ($annual_budget->status === 'PLANIFICACION')
                <span class="badge bg-info text-white">En Planificación</span>
                @else
                <span class="badge bg-secondary text-white">{{ $annual_budget->status }}</span>
                @endif
            </div>
            <div class="card-body">
                @if ($annual_budget->status !== 'PLANIFICACION')
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    <strong>Presupuesto {{ strtolower($annual_budget->status) }}</strong>
                    <p class="small mb-0 mt-1">
                        Este presupuesto no puede ser editado porque ya está
                        {{ strtolower($annual_budget->status) }}.
                    </p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('annual_budgets.update', $annual_budget->id) }}" method="POST" novalidate
                    id="formBudget">
                    @csrf
                    @method('PUT')
                    @include('annual_budgets.partials.form', ['action' => 'edit'])
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
                <p class="text-muted small mb-3">
                    Realiza cambios en los campos editable. Si el presupuesto ya está aprobado, necesitarás crear
                    uno nuevo.
                </p>

                <hr class="my-3">

                <h6 class="small fw-semibold mb-2">Detalles del Presupuesto:</h6>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Creado:</span>
                        <span class="fw-semibold">{{ $annual_budget->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Por:</span>
                        <span class="fw-semibold">{{ $annual_budget->createdBy?->name ?? '—' }}</span>
                    </div>
                    @if ($annual_budget->updated_at && $annual_budget->updated_at != $annual_budget->created_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Modificado:</span>
                        <span class="fw-semibold">{{ $annual_budget->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Por:</span>
                        <span class="fw-semibold">{{ $annual_budget->updatedBy?->name ?? '—' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($annual_budget->status === 'PLANIFICACION')
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="ti ti-trash me-2"></i>Zona de Peligro</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Elimina este presupuesto si ya no lo necesitas.
                </p>
                <form action="{{ route('annual_budgets.destroy', $annual_budget->id) }}" method="POST"
                    id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger btn-sm w-100" id="btnDelete">
                        <i class="ti ti-trash me-1"></i>Eliminar Presupuesto
                    </button>
                </form>
            </div>
        </div>
        @endif
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

        // Botón eliminar
        const btnDelete = document.getElementById('btnDelete');
        if (btnDelete) {
            btnDelete.addEventListener('click', function() {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Este presupuesto se eliminará permanentemente',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="ti ti-trash me-1"></i>Sí, eliminar',
                    cancelButtonText: '<i class="ti ti-x me-1"></i>Cancelar',
                    customClass: {
                        confirmButton: 'btn',
                        cancelButton: 'btn'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteForm').submit();
                    }
                });
            });
        }
    });
</script>
@endpush