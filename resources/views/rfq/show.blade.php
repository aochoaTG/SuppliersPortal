@extends('layouts.zircos')

@section('title', 'Detalle RFQ - ' . $rfq->folio)

@section('content')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="ti ti-file-invoice me-2"></i>
                        {{ $rfq->folio }}
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('rfq.index') }}">RFQs</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $rfq->folio }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('rfq.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- INFORMACIÓN PRINCIPAL --}}
    <div class="row mb-4">
        {{-- COLUMNA IZQUIERDA: Información General --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Folio</label>
                            <p class="mb-0 fw-bold fs-5">{{ $rfq->folio }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Estado</label>
                            <p class="mb-0">
                                @php
                                    $statusConfig = [
                                        'DRAFT' => ['class' => 'secondary', 'icon' => 'ti-pencil', 'label' => 'Borrador'],
                                        'SENT' => ['class' => 'info', 'icon' => 'ti-send', 'label' => 'Enviada'],
                                        'RESPONSES_RECEIVED' => ['class' => 'success', 'icon' => 'ti-check', 'label' => 'Con Respuestas'],
                                        'EVALUATED' => ['class' => 'primary', 'icon' => 'ti-check-circle', 'label' => 'Evaluada'],
                                        'CANCELLED' => ['class' => 'danger', 'icon' => 'ti-x', 'label' => 'Cancelada'],
                                    ];
                                    $status = $statusConfig[$rfq->status] ?? ['class' => 'secondary', 'icon' => 'ti-help', 'label' => $rfq->status];
                                @endphp
                                <span class="badge bg-{{ $status['class'] }} fs-6">
                                    <i class="ti {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Requisición</label>
                            <p class="mb-0">
                                <a href="{{ route('requisitions.show', $rfq->requisition) }}" class="text-decoration-none">
                                    <i class="ti ti-file-text"></i> {{ $rfq->requisition->folio }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tipo</label>
                            <p class="mb-0">
                                @if($rfq->quotation_group_id)
                                    <span class="badge bg-primary">
                                        <i class="ti ti-folder"></i> Grupo: {{ $rfq->quotationGroup->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ti ti-file"></i> Partida Individual
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Fecha de Envío</label>
                            <p class="mb-0">
                                @if($rfq->sent_at)
                                    <i class="ti ti-calendar"></i> {{ $rfq->sent_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">Aún no enviada</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Fecha Límite de Respuesta</label>
                            <p class="mb-0">
                                @if($rfq->response_deadline)
                                    @php
                                        $deadline = \Carbon\Carbon::parse($rfq->response_deadline);
                                        $today = \Carbon\Carbon::today();
                                        $daysRemaining = $today->diffInDays($deadline, false);
                                    @endphp
                                    <i class="ti ti-calendar-event"></i> {{ $deadline->format('d/m/Y') }}
                                    @if($daysRemaining < 0)
                                        <span class="badge bg-danger ms-2">Vencida</span>
                                    @elseif($daysRemaining === 0)
                                        <span class="badge bg-warning ms-2">Vence Hoy</span>
                                    @elseif($daysRemaining <= 3)
                                        <span class="badge bg-warning ms-2">{{ $daysRemaining }} día(s)</span>
                                    @else
                                        <span class="badge bg-success ms-2">{{ $daysRemaining }} días</span>
                                    @endif
                                @else
                                    <span class="text-muted">No definida</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($rfq->notes)
                    <div class="row">
                        <div class="col-12">
                            <label class="text-muted small">Notas / Instrucciones</label>
                            <div class="alert alert-info mb-0">
                                <i class="ti ti-notes"></i> {{ $rfq->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($rfq->message)
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="text-muted small">Mensaje para Proveedores</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $rfq->message }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Proveedores Invitados --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="ti ti-building-store me-2"></i>
                        Proveedores Invitados
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($rfq->suppliers as $supplier)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm bg-primary text-white rounded-circle">
                                    {{ substr($supplier->company_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $supplier->company_name }}</h6>
                                <p class="text-muted small mb-1">
                                    <i class="ti ti-mail"></i> {{ $supplier->email }}
                                </p>
                                @if($supplier->pivot->invited_at)
                                    <p class="text-muted small mb-1">
                                        <i class="ti ti-calendar"></i> Invitado: {{ \Carbon\Carbon::parse($supplier->pivot->invited_at)->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                                @if($supplier->pivot->responded_at)
                                    <span class="badge bg-success">
                                        <i class="ti ti-check"></i> Respondió
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="ti ti-clock"></i> Pendiente
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-user-off fs-2"></i>
                            <p class="mb-0 mt-2">No hay proveedores invitados</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Auditoría --}}
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="ti ti-clock-hour-4 me-2"></i>
                        Auditoría
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        <strong>Creado por:</strong><br>
                        {{ $rfq->creator->name ?? 'N/A' }}<br>
                        <span class="text-muted">{{ $rfq->created_at->format('d/m/Y H:i') }}</span>
                    </p>
                    @if($rfq->updated_at != $rfq->created_at)
                        <p class="text-muted small mb-0">
                            <strong>Última actualización:</strong><br>
                            {{ $rfq->updated_at->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- PARTIDAS / ITEMS DEL GRUPO --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="ti ti-package me-2"></i>
                        Partidas Incluidas
                    </h5>
                </div>
                <div class="card-body">
                    @if($rfq->quotation_group_id && $rfq->quotationGroup)
                        {{-- ESCENARIO A: Grupo de partidas --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="40%">Descripción</th>
                                        <th width="15%">Cantidad</th>
                                        <th width="10%">Unidad</th>
                                        <th width="20%">Categoría</th>
                                        <th width="10%">Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rfq->quotationGroup->items as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->description }}</strong>
                                                @if($item->product_code)
                                                    <br>
                                                    <small class="text-muted">
                                                        Código: {{ $item->product_code }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                            <td>{{ $item->unit }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $item->expenseCategory->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->notes)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ $item->notes }}">
                                                        <i class="ti ti-notes"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                No hay partidas en este grupo
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @elseif($rfq->requisition_item_id && $rfq->requisitionItem)
                        {{-- ESCENARIO B: Partida individual --}}
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Partida Individual</h6>
                            <p class="mb-0">
                                <strong>Descripción:</strong> {{ $rfq->requisitionItem->description }}<br>
                                <strong>Cantidad:</strong> {{ number_format($rfq->requisitionItem->quantity, 2) }} {{ $rfq->requisitionItem->unit }}<br>
                                @if($rfq->requisitionItem->notes)
                                    <strong>Notas:</strong> {{ $rfq->requisitionItem->notes }}
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="ti ti-alert-triangle"></i>
                            No se encontraron partidas asociadas a esta RFQ
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- RESPUESTAS DE PROVEEDORES --}}
    @if($rfq->responses && $rfq->responses->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="ti ti-message-check me-2"></i>
                        Respuestas de Proveedores
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Partida</th>
                                    <th>Precio Unitario</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Plazo Entrega</th>
                                    <th>Fecha Respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rfq->responses as $response)
                                    <tr>
                                        <td>{{ $response->rfq->supplier->company_name ?? 'N/A' }}</td>
                                        <td>{{ $response->requisitionItem->description ?? 'N/A' }}</td>
                                        <td class="text-end">${{ number_format($response->unit_price, 2) }}</td>
                                        <td class="text-center">{{ $response->quantity }}</td>
                                        <td class="text-end">${{ number_format($response->subtotal, 2) }}</td>
                                        <td>{{ $response->delivery_days }} días</td>
                                        <td>{{ $response->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ACCIONES --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('rfq.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left"></i> Volver al Listado
                            </a>
                        </div>
                        <div>
                            @if($rfq->status === 'DRAFT')
                                <button type="button" class="btn btn-success" id="sendRfqBtn">
                                    <i class="ti ti-send"></i> Enviar a Proveedores
                                </button>
                            @endif

                            @if($rfq->status !== 'CANCELLED')
                                <button type="button" class="btn btn-danger" id="cancelRfqBtn">
                                    <i class="ti ti-x"></i> Cancelar RFQ
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.badge {
    font-weight: 500;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Activar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Enviar RFQ
    $('#sendRfqBtn').on('click', function() {
        Swal.fire({
            icon: 'question',
            title: '¿Enviar RFQ?',
            html: '¿Estás seguro de enviar esta solicitud a los proveedores?<br><br>' +
                  '<small class="text-muted">Se enviarán notificaciones por correo electrónico.</small>',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-send me-2"></i>Sí, enviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                sendRFQ();
            }
        });
    });

    function sendRFQ() {
        Swal.fire({
            title: 'Enviando...',
            html: 'Por favor espera un momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("rfq.send", $rfq) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'No se pudo enviar la RFQ'
                });
            }
        });
    }

    // Cancelar RFQ
    $('#cancelRfqBtn').on('click', function() {
        Swal.fire({
            icon: 'warning',
            title: '¿Cancelar RFQ?',
            html: '¿Estás seguro de cancelar esta solicitud?<br><br>' +
                  '<small class="text-muted">Esta acción no se puede deshacer.</small>',
            input: 'textarea',
            inputPlaceholder: 'Motivo de cancelación (opcional)',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                cancelRFQ(result.value);
            }
        });
    });

    function cancelRFQ(reason) {
        $.ajax({
            url: '{{ route("rfq.cancel", $rfq) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelada',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'No se pudo cancelar la RFQ'
                });
            }
        });
    }
});
</script>
@endpush