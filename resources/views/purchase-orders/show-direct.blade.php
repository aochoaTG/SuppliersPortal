@extends('layouts.zircos')

@section('page.title', 'Detalle de OCD: ' . ($directPurchaseOrder->folio ?? 'Borrador'))

@section('content')
<div class="row">
    <div class="col-md-12">
        {{-- ALERTAS DE ACCIÓN --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center d-print-none">
                <div>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Regresar
                    </a>
                    @if($directPurchaseOrder->canBeEdited() && $directPurchaseOrder->created_by === Auth::id())
                        <a href="{{ route('direct-purchase-orders.edit', $directPurchaseOrder->id) }}" class="btn btn-sm btn-outline-warning ms-1">
                            <i class="ti ti-edit me-1"></i>Editar
                        </a>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    {{-- BOTONES DE ACCIÓN PARA EL CREADOR --}}
                    @if($directPurchaseOrder->status === 'DRAFT' || $directPurchaseOrder->status === 'RETURNED')
                        @if($directPurchaseOrder->created_by === Auth::id())
                            <form action="{{ route('direct-purchase-orders.submit', $directPurchaseOrder->id) }}" method="POST" id="form-submit">
                                @csrf
                                <button type="button" onclick="confirmAction('form-submit', '¿Estás seguro de enviar esta orden a aprobación?')" class="btn btn-sm btn-primary">
                                    <i class="ti ti-send me-1"></i>Enviar a Aprobación
                                </button>
                            </form>
                        @endif
                    @endif

                    {{-- BOTONES DE ACCIÓN PARA EL APROBADOR --}}
                    @if($directPurchaseOrder->status === 'PENDING_APPROVAL')
                        {{-- En este flujo simplificado, cualquier superadmin o buyer con permiso puede aprobar --}}
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalApprove">
                            <i class="ti ti-check me-1"></i>Aprobar
                        </button>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalReturn">
                            <i class="ti ti-arrow-back-up me-1"></i>Devolver
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">
                            <i class="ti ti-x me-1"></i>Rechazar
                        </button>
                    @endif

                    <button onclick="window.print();" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="ti ti-printer me-1"></i>Imprimir
                    </button>
                </div>
            </div>
            
            <div class="card-body p-5" id="printable-area">
                {{-- HEADER DE LA OC --}}
                <div class="row mb-4">
                    <div class="col-6">
                        <img src="{{ asset('images/logos/logo_TotalGas_hor.png') }}" alt="TotalGas" height="50" class="mb-3">
                        <h6 class="text-muted fw-bold">TOTALGAS MÉXICO</h6>
                        <p class="text-muted small">
                            RFC: TGM123456789<br>
                            Av. Tecnológico #1234<br>
                            Ciudad Juárez, Chihuahua.
                        </p>
                    </div>
                    <div class="col-6 text-end">
                        <h3 class="text-primary fw-bold mb-1">ORDEN DE COMPRA DIRECTA</h3>
                        <h4 class="text-dark mb-3">{{ $directPurchaseOrder->folio ?? 'BORRADOR' }}</h4>
                        <div class="text-muted small">
                            <strong>Fecha de Solicitud:</strong> {{ $directPurchaseOrder->created_at->format('d/m/Y H:i') }}<br>
                            <strong>Estado:</strong> 
                            <span class="badge bg-{{ 
                                match($directPurchaseOrder->status) {
                                    'DRAFT' => 'secondary',
                                    'PENDING_APPROVAL' => 'warning',
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'danger',
                                    'RETURNED' => 'info',
                                    default => 'dark'
                                }
                            }}">
                                {{ 
                                    match($directPurchaseOrder->status) {
                                        'DRAFT' => 'Borrador',
                                        'PENDING_APPROVAL' => 'Pendiente Aprobación',
                                        'APPROVED' => 'Aprobada',
                                        'REJECTED' => 'Rechazada',
                                        'RETURNED' => 'Devuelta',
                                        default => $directPurchaseOrder->status
                                    }
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- INFORMACIÓN DE PROVEEDOR Y CONTROL --}}
                <div class="row mb-5">
                    <div class="col-6 border-end">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">Datos del Proveedor</h6>
                        <h5 class="fw-bold mb-1">{{ $directPurchaseOrder->supplier->company_name }}</h5>
                        <p class="text-muted small mb-0">
                            <strong>RFC:</strong> {{ $directPurchaseOrder->supplier->rfc ?? 'N/A' }}<br>
                            <strong>Contacto:</strong> {{ $directPurchaseOrder->supplier->contact_name ?? 'Sin contacto' }}<br>
                            <strong>Email:</strong> {{ $directPurchaseOrder->supplier->email }}
                        </p>
                    </div>
                    <div class="col-6 ps-4">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">Control y Presupuesto</h6>
                        <p class="text-muted small mb-0">
                            <strong>Centro de Costo:</strong> {{ $directPurchaseOrder->costCenter->name ?? 'N/A' }}<br>
                            <strong>Categoría de Gasto:</strong> {{ $directPurchaseOrder->expenseCategory->name ?? 'N/A' }}<br>
                            <strong>Mes Aplicación:</strong> {{ $directPurchaseOrder->application_month }}<br>
                            <strong>Solicitado por:</strong> {{ $directPurchaseOrder->creator->name }}
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-dark fw-bold mb-2">Justificación:</h6>
                        <div class="p-3 bg-light rounded italic text-muted">
                            {{ $directPurchaseOrder->justification ?? 'Sin justificación proporcionada.' }}
                        </div>
                    </div>
                </div>

                {{-- TABLA DE PARTIDAS --}}
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-centered mb-0">
                        <thead class="table-light">
                            <tr class="text-dark fw-bold">
                                <th style="width: 50px">#</th>
                                <th>Descripción del Producto/Servicio</th>
                                <th class="text-center" style="width: 100px">Cantidad</th>
                                <th class="text-end" style="width: 150px">P. Unitario</th>
                                <th class="text-end" style="width: 150px">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($directPurchaseOrder->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $item->description }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end fw-bold text-dark">${{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" rowspan="3" class="border-0 align-top">
                                    <div class="bg-light p-3 rounded mt-2">
                                        <h6 class="fw-bold text-dark mb-2">Condiciones Comerciales:</h6>
                                        <ul class="small text-muted mb-0">
                                            <li><strong>Forma de Pago:</strong> {{ $directPurchaseOrder->payment_terms }}</li>
                                            <li><strong>Entrega Estimada:</strong> {{ $directPurchaseOrder->estimated_delivery_days }} días</li>
                                            <li><strong>Moneda:</strong> {{ $directPurchaseOrder->currency }}</li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="text-end border-0 fw-bold">Subtotal:</td>
                                <td class="text-end border-0 fw-bold">${{ number_format($directPurchaseOrder->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end border-0 fw-bold text-muted small">I.V.A (16%):</td>
                                <td class="text-end border-0 text-muted small">${{ number_format($directPurchaseOrder->iva_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end border-0 bg-soft-primary"><h5 class="fw-bold text-primary mb-0">TOTAL:</h5></td>
                                <td class="text-end border-0 bg-soft-primary"><h5 class="fw-bold text-primary mb-0">${{ number_format($directPurchaseOrder->total, 2) }}</h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- DOCUMENTOS ADJUNTOS --}}
                <div class="row mb-5 d-print-none">
                    <div class="col-12">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">Documentos de Soporte</h6>
                        <div class="d-flex flex-wrap gap-3">
                            @forelse($directPurchaseOrder->documents as $doc)
                                <div class="p-2 border rounded d-flex align-items-center bg-light">
                                    <i class="ti ti-file-text fs-2 text-primary me-2"></i>
                                    <div>
                                        <p class="mb-0 small fw-bold">{{ $doc->original_filename }}</p>
                                        <small class="text-muted text-uppercase">{{ $doc->document_type }}</small>
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="ms-2 text-info" title="Ver archivo">
                                            <i class="ti ti-external-link"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No hay documentos adjuntos.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- HISTORIAL DE APROBACIONES --}}
                @if($directPurchaseOrder->approvals->count() > 0)
                <div class="row pt-4">
                    <div class="col-12">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">Historial de Aprobaciones</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <thead class="table-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Comentarios</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($directPurchaseOrder->approvals as $approval)
                                        <tr class="align-middle">
                                            <td class="small">{{ $approval->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="small fw-bold">{{ $approval->approver->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $approval->getActionBadgeClass() }} small">
                                                    {{ $approval->getActionLabel() }}
                                                </span>
                                            </td>
                                            <td class="small italic text-muted">{{ $approval->comments ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODALES DE APROBACIÓN --}}
@if($directPurchaseOrder->status === 'PENDING_APPROVAL')
    {{-- Modal Aprobar --}}
    <div class="modal fade" id="modalApprove" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('direct-purchase-orders.approve', $directPurchaseOrder->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Aprobación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas aprobar esta Orden de Compra Directa?</p>
                    <div class="mb-3">
                        <label class="form-label">Comentarios (Opcional)</label>
                        <textarea name="comments" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">SÍ, APROBAR</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Devolver --}}
    <div class="modal fade" id="modalReturn" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('direct-purchase-orders.return', $directPurchaseOrder->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header text-white bg-info">
                    <h5 class="modal-title">Devolver para Corrección</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-danger fw-bold">Motivo de la Devolución (Requerido)</label>
                        <textarea name="comments" class="form-control" rows="3" required placeholder="Explica qué debe corregir el solicitante..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">DEVOLVER</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Rechazar --}}
    <div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('direct-purchase-orders.reject', $directPurchaseOrder->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header text-white bg-danger">
                    <h5 class="modal-title">Rechazar Definitivamente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-danger fw-bold">Motivo del Rechazo (Requerido)</label>
                        <textarea name="comments" class="form-control" rows="3" required placeholder="Explica por qué se rechaza la orden..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">RECHAZAR</button>
                </div>
            </form>
        </div>
    </div>
@endif

@push('styles')
<style>
    @media print {
        .d-print-none { display: none !important; }
        .card { box-shadow: none !important; border: 0 !important; }
        body { background-color: white !important; }
        .sidenav-menu, .topbar { display: none !important; }
        .page-content { padding: 0 !important; margin: 0 !important; }
        .content-page { margin: 0 !important; }
        footer { display: none !important; }
    }
    .fs-12 { font-size: 12px; }
    .italic { font-style: italic; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmAction(formId, message) {
        Swal.fire({
            title: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endpush
@endsection
