@extends('layouts.zircos')

@section('title', 'Registrar Recepción — ' . ($order->folio ?? 'Nueva'))
@section('page.title', 'Registrar Recepción')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('receptions.overview') }}">Recepciones</a></li>
    <li class="breadcrumb-item active">Registrar Recepción</li>
@endsection

@section('content')
<form action="{{ $storeRoute }}" method="POST" enctype="multipart/form-data" id="reception-form">
    @csrf

    {{-- Hiddens globales --}}
    <input type="hidden" name="receivable_type" value="{{ get_class($order) }}">
    <input type="hidden" name="receivable_id" value="{{ $order->id }}">
    <input type="hidden" name="receivable_item_type" value="{{ $orderType === 'purchase_order' ? \App\Models\PurchaseOrderItem::class : \App\Models\DirectPurchaseOrderItem::class }}">

    <div class="row g-3">

        {{-- ── Error general ────────────────────────────────────────────── --}}
        @if(session('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="ti ti-alert-circle me-2"></i><strong>Corrige los errores antes de continuar.</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        {{-- ── Advertencia REPSE ──────────────────────────────────────── --}}
        @if($repseWarning)
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-0">
                    <i class="ti ti-alert-triangle fs-20 mt-1 flex-shrink-0"></i>
                    <div><strong>Atención — REPSE:</strong> {{ $repseWarning }}</div>
                </div>
            </div>
        @endif

        {{-- ╔══════════════════════════════════════════════════════════════╗
             ║  CARD 1 — Datos de la orden (solo lectura)                  ║
             ╚══════════════════════════════════════════════════════════════╝ --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="ti ti-file-description"></i>
                    <h6 class="mb-0 flex-grow-1">Datos de la Orden</h6>
                    <span class="badge bg-{{ $order->getStatusBadgeClass() }}">
                        {{ $order->getStatusLabel() }}
                    </span>
                    @if($orderType === 'purchase_order')
                        <span class="badge bg-soft-primary text-primary">OC Estándar</span>
                    @else
                        <span class="badge bg-soft-warning text-warning">OC Directa</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-sm-3">
                            <label class="form-label small fw-bold text-muted">Folio</label>
                            <p class="mb-0 fw-bold fs-15">{{ $order->folio ?? '—' }}</p>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label small fw-bold text-muted">Proveedor</label>
                            <p class="mb-0">{{ $order->supplier->company_name ?? '—' }}</p>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label small fw-bold text-muted">Fecha Emisión</label>
                            <p class="mb-0">{{ $order->issued_at?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label small fw-bold text-muted">Total</label>
                            <p class="mb-0 fw-bold">${{ number_format($order->total, 2) }} {{ $order->currency }}</p>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label small fw-bold text-muted">Punto de Entrega Original</label>
                            <p class="mb-0 small">{{ $order->receivingLocation?->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════════╗
             ║  CARD 2 — Datos generales de recepción                      ║
             ╚══════════════════════════════════════════════════════════════╝ --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-clipboard-check me-2"></i>Datos Generales de Recepción</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Ubicación de recepción --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold" for="receiving_location_id">
                                Punto de Recepción <span class="text-danger">*</span>
                            </label>
                            <select name="receiving_location_id"
                                    id="receiving_location_id"
                                    class="form-select form-select-sm select2-enable @error('receiving_location_id') is-invalid @enderror"
                                    required>
                                <option value="">Seleccionar…</option>
                                @foreach($receivingLocations as $loc)
                                    <option value="{{ $loc->id }}"
                                        {{ old('receiving_location_id', $order->receiving_location_id) == $loc->id ? 'selected' : '' }}>
                                        [{{ $loc->code }}] {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('receiving_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha y hora de recepción --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold" for="received_at">
                                Fecha y Hora de Recepción <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local"
                                   name="received_at"
                                   id="received_at"
                                   class="form-control form-control-sm @error('received_at') is-invalid @enderror"
                                   value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('received_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nº de remisión --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold" for="delivery_reference">
                                Nº de Remisión / Referencia
                            </label>
                            <input type="text"
                                   name="delivery_reference"
                                   id="delivery_reference"
                                   class="form-control form-control-sm @error('delivery_reference') is-invalid @enderror"
                                   value="{{ old('delivery_reference') }}"
                                   maxlength="100"
                                   placeholder="Folio del documento del proveedor">
                            @error('delivery_reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Archivo de remisión --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold" for="remission_file">
                                Archivo de Remisión <span class="text-danger">*</span>
                            </label>
                            <input type="file"
                                   name="remission_file"
                                   id="remission_file"
                                   class="form-control form-control-sm @error('remission_file') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            <div class="form-text">PDF, JPG o PNG · Máx. 10 MB</div>
                            @error('remission_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notas --}}
                        <div class="col-md-8">
                            <label class="form-label small fw-bold" for="notes">
                                Notas u Observaciones
                            </label>
                            <textarea name="notes"
                                      id="notes"
                                      class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                      rows="2"
                                      maxlength="1000"
                                      placeholder="Estado del empaque, condiciones de entrega, etc.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════════╗
             ║  CARD 3 — Partidas                                          ║
             ╚══════════════════════════════════════════════════════════════╝ --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="ti ti-list-details me-2"></i>Partidas a Recibir</h6>
                    <small class="text-muted">Al menos una partida debe tener cantidad a recibir mayor a cero</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Descripción</th>
                                    <th class="text-center" style="width:110px">Cant. Ordenada</th>
                                    <th class="text-center" style="width:110px">Ya Recibido</th>
                                    <th class="text-center" style="width:100px">Pendiente</th>
                                    <th class="text-center" style="width:130px">
                                        Cant. a Recibir <span class="text-danger">*</span>
                                    </th>
                                    <th class="text-center" style="width:120px">Cant. Rechazada</th>
                                    <th style="min-width:180px">Motivo de Rechazo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $i => $item)
                                    @php
                                        $alreadyReceived = (float) $item->quantity_received;
                                        $pending         = (float) $item->quantity_pending;
                                        $fullyReceived   = $item->isFullyReceived();
                                        $oldReceived     = old("items.{$i}.quantity_received");
                                        $oldRejected     = (float) old("items.{$i}.quantity_rejected", 0);
                                    @endphp
                                    <tr class="{{ $fullyReceived ? 'table-secondary' : '' }}"
                                        data-pending="{{ $pending }}">

                                        <input type="hidden"
                                               name="items[{{ $i }}][receivable_item_id]"
                                               value="{{ $item->id }}">

                                        {{-- Descripción --}}
                                        <td class="ps-3">
                                            <span class="fw-semibold small">{{ $item->description }}</span>
                                            @if($fullyReceived)
                                                <span class="badge bg-success ms-1">Completo</span>
                                            @endif
                                        </td>

                                        {{-- Cant. ordenada --}}
                                        <td class="text-center small text-muted">
                                            {{ number_format((float) $item->quantity, 3) }}
                                        </td>

                                        {{-- Ya recibido --}}
                                        <td class="text-center small text-muted">
                                            {{ number_format($alreadyReceived, 3) }}
                                        </td>

                                        {{-- Pendiente --}}
                                        <td class="text-center">
                                            <span class="fw-bold {{ $pending > 0 ? 'text-primary' : 'text-success' }}">
                                                {{ number_format($pending, 3) }}
                                            </span>
                                        </td>

                                        {{-- Cant. a recibir --}}
                                        <td class="text-center">
                                            <input type="number"
                                                   name="items[{{ $i }}][quantity_received]"
                                                   class="form-control form-control-sm text-center qty-received"
                                                   value="{{ $oldReceived !== null ? $oldReceived : ($pending > 0 ? $pending : 0) }}"
                                                   min="0"
                                                   max="{{ $pending }}"
                                                   step="0.001"
                                                   data-max="{{ $pending }}"
                                                   data-index="{{ $i }}"
                                                   {{ $fullyReceived ? 'disabled' : 'required' }}>
                                            @error("items.{$i}.quantity_received")
                                                <div class="text-danger" style="font-size:0.75rem">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        {{-- Cant. rechazada --}}
                                        <td class="text-center">
                                            <input type="number"
                                                   name="items[{{ $i }}][quantity_rejected]"
                                                   class="form-control form-control-sm text-center qty-rejected"
                                                   value="{{ $oldRejected }}"
                                                   min="0"
                                                   step="0.001"
                                                   data-index="{{ $i }}"
                                                   {{ $fullyReceived ? 'disabled' : '' }}>
                                            @error("items.{$i}.quantity_rejected")
                                                <div class="text-danger" style="font-size:0.75rem">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        {{-- Motivo de rechazo --}}
                                        <td>
                                            <input type="text"
                                                   name="items[{{ $i }}][rejection_reason]"
                                                   class="form-control form-control-sm rejection-reason"
                                                   value="{{ old("items.{$i}.rejection_reason") }}"
                                                   maxlength="255"
                                                   placeholder="Requerido si hay rechazos"
                                                   {{ ($fullyReceived || $oldRejected <= 0) ? 'disabled' : '' }}>
                                            @error("items.{$i}.rejection_reason")
                                                <div class="text-danger" style="font-size:0.75rem">{{ $message }}</div>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Botones ──────────────────────────────────────────────────── --}}
        <div class="col-12 d-flex justify-content-end gap-2 pb-4">
            <a href="{{ route('receptions.overview') }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i>Cancelar
            </a>
            <button type="button" id="btn-submit" class="btn btn-success btn-sm">
                <i class="ti ti-package-import me-1"></i>Registrar Recepción
            </button>
        </div>

    </div>{{-- /row --}}
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ─── Select2 ─────────────────────────────────────────────────────────────
    $('.select2-enable').select2({ width: '100%', placeholder: 'Seleccionar…' });

    // ─── Motivo de rechazo: activar/desactivar según cantidad rechazada ───────
    $(document).on('change input', '.qty-rejected', function () {
        const $row    = $(this).closest('tr');
        const $reason = $row.find('.rejection-reason');
        const val     = parseFloat($(this).val()) || 0;

        if (val > 0) {
            $reason.prop('disabled', false).attr('required', 'required');
        } else {
            $reason.prop('disabled', true).removeAttr('required').val('');
        }
    });

    // ─── Cant. a recibir: bloqueo estricto si excede pendiente ───────────────
    $(document).on('change input', '.qty-received', function () {
        const max = parseFloat($(this).data('max')) || 0;
        const val = parseFloat($(this).val()) || 0;

        if (val > max) {
            $(this).val(max.toFixed(3));
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad excedida',
                text: 'La cantidad a recibir no puede superar la pendiente (' + max.toFixed(3) + ').',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f7b731',
            });
        }
    });

    // ─── Validaciones antes de submit ────────────────────────────────────────

    function validateRemissionFile() {
        const file = document.getElementById('remission_file').files[0];
        if (!file) return { valid: false, message: 'El archivo de remisión es obligatorio.' };

        const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowed.includes(file.type)) {
            return { valid: false, message: 'Solo se permiten archivos PDF, JPG o PNG.' };
        }
        if (file.size > 10 * 1024 * 1024) {
            return { valid: false, message: 'El archivo no puede superar los 10 MB.' };
        }
        return { valid: true };
    }

    function validateItems() {
        let hasAnyReceived = false;
        const errors = [];

        $('#items-table tbody tr:not(.table-secondary)').each(function () {
            const $row     = $(this);
            const pending  = parseFloat($row.data('pending')) || 0;
            const received = parseFloat($row.find('.qty-received').val()) || 0;
            const rejected = parseFloat($row.find('.qty-rejected').val()) || 0;

            if (received > 0) hasAnyReceived = true;

            if ((received + rejected) > (pending + 0.0005)) {
                const desc = $row.find('td.ps-3 .fw-semibold').text().trim().substring(0, 45);
                errors.push('"' + desc + '": recibido + rechazado (' +
                    (received + rejected).toFixed(3) + ') supera pendiente (' + pending.toFixed(3) + ').');
            }

            if (rejected > 0 && $row.find('.rejection-reason').val().trim() === '') {
                const desc = $row.find('td.ps-3 .fw-semibold').text().trim().substring(0, 45);
                errors.push('"' + desc + '": el motivo de rechazo es obligatorio cuando hay rechazos.');
            }
        });

        if (!hasAnyReceived) {
            errors.unshift('Debes registrar al menos una partida con cantidad a recibir mayor a cero.');
        }

        return errors;
    }

    // ─── Botón submit con confirmación SweetAlert2 ───────────────────────────
    $('#btn-submit').on('click', function () {

        const fileCheck = validateRemissionFile();
        if (!fileCheck.valid) {
            Swal.fire({ icon: 'error', title: 'Archivo inválido', text: fileCheck.message });
            return;
        }

        const itemErrors = validateItems();
        if (itemErrors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Revisa las partidas',
                html: '<ul class="text-start mb-0">' +
                      itemErrors.map(function(e) { return '<li>' + e + '</li>'; }).join('') +
                      '</ul>',
            });
            return;
        }

        Swal.fire({
            title: '¿Confirmar recepción?',
            html: 'Esta acción registrará la recepción y actualizará el estado de la orden.<br><strong>No se puede deshacer.</strong>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-check me-1"></i>Sí, registrar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#0acf97',
            cancelButtonColor: '#6c757d',
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#btn-submit').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando…');
                document.getElementById('reception-form').submit();
            }
        });
    });

});
</script>
@endpush
