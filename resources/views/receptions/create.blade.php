@extends('layouts.zircos')

@section('title', 'Registrar Recepción — ' . $order->folio)
@section('page.title', 'Registrar Recepción')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('receptions.pending') }}">Recepciones Pendientes</a></li>
    <li class="breadcrumb-item active">{{ $order->folio }}</li>
@endsection

@section('content')

{{-- Advertencia REPSE --}}
@if($repseWarning)
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="ti ti-alert-triangle me-2"></i>
        <strong>Advertencia REPSE:</strong> {{ $repseWarning }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Error del servicio --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ $storeRoute }}" method="POST" id="reception-form">
    @csrf

    <div class="row g-3">

        {{-- Columna principal --}}
        <div class="col-lg-8">

            {{-- Encabezado de la orden --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="info-label">Orden</span>
                            <div class="fw-bold text-primary fs-5">{{ $order->folio }}</div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Proveedor</span>
                            <div class="fw-semibold">{{ $order->supplier->company_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Punto de Entrega</span>
                            <div>
                                <span class="badge bg-soft-info text-info me-1">
                                    {{ $order->receivingLocation->code }}
                                </span>
                                {{ $order->receivingLocation->name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de ítems --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-list-check me-2"></i>Partidas a Recibir</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Descripción</th>
                                    <th class="text-center" style="width:90px">Ordenado</th>
                                    <th class="text-center" style="width:90px">Recibido Prev.</th>
                                    <th class="text-center" style="width:90px">Pendiente</th>
                                    <th class="text-center" style="width:110px">A Recibir <span class="text-danger">*</span></th>
                                    <th class="text-center" style="width:100px">Rechazado</th>
                                    <th style="width:180px">Motivo Rechazo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $index => $item)
                                    @php $pending = $item->quantity_pending; @endphp
                                    <tr>
                                        <td class="text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
                                            <span class="fw-semibold">{{ $item->description }}</span>
                                            @isset($item->unit_of_measure)
                                                <small class="text-muted d-block">{{ $item->unit_of_measure }}</small>
                                            @endisset
                                        </td>
                                        <td class="text-center text-muted">
                                            {{ number_format($item->quantity, 2) }}
                                        </td>
                                        <td class="text-center text-muted">
                                            {{ number_format($item->quantity_received, 2) }}
                                        </td>
                                        <td class="text-center fw-semibold {{ $pending > 0 ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($pending, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   name="items[{{ $index }}][quantity_received]"
                                                   class="form-control form-control-sm text-center qty-received @error('items.'.$index.'.quantity_received') is-invalid @enderror"
                                                   value="{{ old('items.'.$index.'.quantity_received', $pending) }}"
                                                   min="0"
                                                   max="{{ $pending }}"
                                                   step="0.001"
                                                   data-pending="{{ $pending }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   name="items[{{ $index }}][quantity_rejected]"
                                                   class="form-control form-control-sm text-center qty-rejected"
                                                   value="{{ old('items.'.$index.'.quantity_rejected', 0) }}"
                                                   min="0"
                                                   step="0.001">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="items[{{ $index }}][rejection_reason]"
                                                   class="form-control form-control-sm rejection-reason"
                                                   placeholder="Motivo..."
                                                   value="{{ old('items.'.$index.'.rejection_reason') }}"
                                                   style="display:none">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- Panel lateral --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-clipboard me-2"></i>Datos de la Recepción</h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Fecha de Recepción <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local"
                               name="received_at"
                               class="form-control @error('received_at') is-invalid @enderror"
                               value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}"
                               required>
                        @error('received_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Referencia del Proveedor
                            <small class="text-muted fw-normal">(Remisión, albarán)</small>
                        </label>
                        <input type="text"
                               name="delivery_reference"
                               class="form-control @error('delivery_reference') is-invalid @enderror"
                               value="{{ old('delivery_reference') }}"
                               placeholder="Ej. REM-2026-001"
                               maxlength="100">
                        @error('delivery_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notas de Recepción</label>
                        <textarea name="notes"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="Observaciones, condición de los bienes, etc.">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i>Confirmar Recepción
                        </button>
                        <a href="{{ route('receptions.pending') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Mostrar/ocultar motivo de rechazo según si se ingresó cantidad rechazada
    document.querySelectorAll('.qty-rejected').forEach(function (input) {
        const row = input.closest('tr');
        const reasonInput = row.querySelector('.rejection-reason');

        function toggleReason() {
            const rejected = parseFloat(input.value) || 0;
            reasonInput.style.display = rejected > 0 ? 'block' : 'none';
            if (rejected === 0) reasonInput.value = '';
        }

        input.addEventListener('input', toggleReason);
        toggleReason(); // estado inicial
    });

    // Validar que quantity_rejected no supere quantity_received
    document.getElementById('reception-form').addEventListener('submit', function (e) {
        let valid = true;
        document.querySelectorAll('tr').forEach(function (row) {
            const received = parseFloat(row.querySelector('.qty-received')?.value) || 0;
            const rejected = parseFloat(row.querySelector('.qty-rejected')?.value) || 0;
            if (rejected > received) {
                valid = false;
                row.querySelector('.qty-rejected').classList.add('is-invalid');
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('El total rechazado no puede ser mayor al total recibido en alguna partida.');
        }
    });

});
</script>
@endpush

@push('styles')
<style>
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        font-weight: 600;
    }
</style>
@endpush

@endsection
