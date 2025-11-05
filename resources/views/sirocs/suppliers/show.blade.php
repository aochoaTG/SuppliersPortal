@extends('layouts.zircos')

@section('title', 'Detalle SIROC')

@section('page.title', 'Proveedores')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="#">Proveedores</a></li>
    <li class="breadcrumb-item"><a href="{{ route('suppliers.sirocs.index', $supplier) }}">SIROC</a></li>
    <li class="breadcrumb-item active">Detalle #{{ $siroc->id }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="ti ti-building-construction me-2"></i> Registro SIROC
        </h5>
        <div class="d-flex gap-2">
            @if($siroc->siroc_file)
                <a href="{{ asset('storage/'.$siroc->siroc_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                    <i class="ti ti-file-type-pdf me-1"></i> Ver PDF
                </a>
            @endif
            <form action="{{ route('suppliers.sirocs.destroy', [$supplier, $siroc]) }}" method="POST" class="d-inline js-del-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="ti ti-trash me-1"></i> Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div class="mb-3 text-muted">
            <small>
                <strong>ID:</strong> {{ $siroc->id }} |
                <strong>Proveedor:</strong> {{ $supplier->company_name ?? $supplier->name ?? 'Proveedor' }}
            </small>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Número SIROC</dt>
            <dd class="col-sm-9"><code>{{ $siroc->siroc_number }}</code></dd>

            <dt class="col-sm-3">Contrato</dt>
            <dd class="col-sm-9">{{ $siroc->contract_number ?? '—' }}</dd>

            <dt class="col-sm-3">Nombre de la obra</dt>
            <dd class="col-sm-9">{{ $siroc->work_name ?? '—' }}</dd>

            <dt class="col-sm-3">Ubicación</dt>
            <dd class="col-sm-9">{{ $siroc->work_location ?? '—' }}</dd>

            <dt class="col-sm-3">Fecha inicio</dt>
            <dd class="col-sm-9">{{ optional($siroc->start_date)->format('d/m/Y') ?? '—' }}</dd>

            <dt class="col-sm-3">Fecha término</dt>
            <dd class="col-sm-9">{{ optional($siroc->end_date)->format('d/m/Y') ?? '—' }}</dd>

            <dt class="col-sm-3">Estatus</dt>
            <dd class="col-sm-9">
                @php
                    $badges = ['vigente' => 'success', 'suspendido' => 'warning', 'terminado' => 'secondary'];
                    $icons  = ['vigente' => 'check', 'suspendido' => 'alert-triangle', 'terminado' => 'circle-off'];
                    $b = $badges[$siroc->status] ?? 'secondary';
                    $i = $icons[$siroc->status] ?? 'help';
                @endphp
                <span class="badge bg-{{ $b }}">
                    <i class="ti ti-{{ $i }} me-1"></i>{{ ucfirst($siroc->status) }}
                </span>
            </dd>

            <dt class="col-sm-3">Observaciones</dt>
            <dd class="col-sm-9">{{ $siroc->observations ?? '—' }}</dd>
        </dl>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-del-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // evita submit inmediato

            Swal.fire({
                title: '¿Eliminar este SIROC?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({ _method: 'DELETE' })
                    })
                    .then(res => res.json())
                    .then(json => {
                        Swal.fire({
                            icon: 'success',
                            title: json.message || 'Eliminado',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('documents.suppliers.index') }}";
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo eliminar el SIROC. Inténtalo nuevamente.'
                        });
                    });
                }
            });
        });
    });
});
</script>
@endpush
