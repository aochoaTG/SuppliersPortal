@extends('layouts.zircos')

@section('title', 'Detalle de OCD: ' . ($directPurchaseOrder->folio ?? 'Borrador'))

@section('page.title', 'Detalle de OCD')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
    <li class="breadcrumb-item active">{{ $directPurchaseOrder->folio ?? 'Borrador' }}</li>
@endsection

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

                    {{-- ETIQUETA DE RECHAZO --}}
                    @if($directPurchaseOrder->status === 'REJECTED')
                        <span class="badge bg-danger fs-6 px-3 py-2 d-flex align-items-center gap-1">
                            <i class="ti ti-ban"></i> OC RECHAZADA
                        </span>
                    @endif

                    {{-- BOTONES DE ACCIÓN PARA EL APROBADOR --}}
                    @if($directPurchaseOrder->status === 'PENDING_APPROVAL')
                        {{-- En este flujo simplificado, cualquier superadmin o buyer con permiso puede aprobar --}}
                        <button type="button" class="btn btn-sm btn-success" onclick="confirmApproval()">
                            <i class="ti ti-check me-1"></i>Aprobar OC
                        </button>
                        <button type="button" class="btn btn-sm btn-info" onclick="confirmReturn()">
                            <i class="ti ti-arrow-back-up me-1"></i>Devolver
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmReject()">
                            <i class="ti ti-x me-1"></i>Rechazar OC
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
    {{-- Formulario oculto para devolución (enviado por SweetAlert) --}}
    <form id="form-return" action="{{ route('direct-purchase-orders.return', $directPurchaseOrder->id) }}" method="POST" style="display:none">
        @csrf
        <input type="hidden" name="comments" id="return-comments-input">
    </form>

    {{-- Formulario oculto para rechazo (enviado por SweetAlert) --}}
    <form id="form-reject" action="{{ route('direct-purchase-orders.reject', $directPurchaseOrder->id) }}" method="POST" style="display:none">
        @csrf
        <input type="hidden" name="comments" id="reject-comments-input">
    </form>
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
    // Feedback de éxito genérico
    @if(session('success'))
        Swal.fire({
            title: '¡Operación Exitosa!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#28a745'
        });
    @endif

    // Feedback de devolución exitosa
    @if(session('return_data'))
    @php $rtd = session('return_data'); @endphp
    Swal.fire({
        title: '<span class="fw-bold" style="color:#0dcaf0">↩️ OC devuelta para correcciones</span>',
        icon: 'info',
        html: `
            <div class="text-start">
                <div class="mb-2"><strong>Folio:</strong> {{ $rtd['folio'] }}</div>
                <div class="mb-3"><strong>Nuevo estatus:</strong> <span class="badge bg-info text-dark">Corrección requerida</span></div>
                <div class="border-top pt-3">
                    <p class="mb-1 small text-muted fw-bold">Instrucciones enviadas a <strong>{{ $rtd['creator_name'] }}</strong>:</p>
                    <div class="p-2 bg-light rounded fst-italic text-muted small">"{{ Str::limit($rtd['comments'], 120) }}"</div>
                </div>
                <div class="mt-3 text-muted small">
                    <div><i class="ti ti-edit me-1"></i>El solicitante podrá editar la OC y reenviarla.</div>
                    <div class="mt-1"><i class="ti ti-calendar me-1"></i>El presupuesto se mantiene reservado por 7 días.</div>
                </div>
            </div>
        `,
        confirmButtonText: 'ACEPTAR',
        confirmButtonColor: '#0dcaf0',
    });
    @endif

    // Feedback de rechazo exitoso
    @if(session('rejection_data'))
    @php $rd = session('rejection_data'); @endphp
    Swal.fire({
        title: '<span class="text-danger fw-bold">OC Rechazada</span>',
        icon: 'error',
        html: `
            <div class="text-start">
                <div class="mb-2"><strong>Folio:</strong> {{ $rd['folio'] }}</div>
                <div class="mb-2"><strong>Estatus:</strong> <span class="badge bg-danger">Rechazada</span></div>
                <div class="mb-3"><strong>Monto:</strong> ${{ number_format($rd['total'], 2) }} {{ $rd['currency'] }}</div>
                <div class="border-top pt-3">
                    <p class="mb-1 small text-muted fw-bold">Se notificó a <strong>{{ $rd['creator_name'] }}</strong> con el motivo:</p>
                    <div class="p-2 bg-light rounded fst-italic text-muted small">"{{ Str::limit($rd['comments'], 120) }}"</div>
                </div>
            </div>
        `,
        confirmButtonText: 'ACEPTAR',
        confirmButtonColor: '#dc3545',
    });
    @endif

    function confirmApproval() {
        Swal.fire({
            title: '<h4 class="fw-bold mb-0">Confirmar Aprobación</h4>',
            icon: 'question',
            html: `
                <div class="text-start mt-3 p-3 bg-light rounded border">
                    <p class="mb-3 fw-bold text-dark">Al aprobar esta Orden de Compra:</p>
                    <div class="mb-2"><i class="ti ti-check text-success me-2"></i>Se comprometerá el presupuesto del centro de costos</div>
                    <div class="mb-2"><i class="ti ti-check text-success me-2"></i>Se generará el folio único de OC</div>
                    <div class="mb-0"><i class="ti ti-check text-success me-2"></i>Se notificará al proveedor vía correo y portal</div>
                </div>
                <div class="text-start mt-3">
                    <label class="form-label fw-bold small text-uppercase">Comentarios de Aprobación (Opcional)</label>
                    <textarea id="swal-comments" class="form-control" rows="3" placeholder="Escriba algún comentario si lo desea..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-check me-1"></i>SÍ, APROBAR OC',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const comments = document.getElementById('swal-comments').value;
                return comments;
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear y enviar formulario dinámicamente
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('direct-purchase-orders.approve', $directPurchaseOrder->id) }}";
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);
                
                const commentsInput = document.createElement('input');
                commentsInput.type = 'hidden';
                commentsInput.name = 'comments';
                commentsInput.value = result.value;
                form.appendChild(commentsInput);
                
                document.body.appendChild(form);
                
                // Mostrar cargando mientras se procesa en el servidor
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Validando presupuesto y notificando al proveedor',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        form.submit();
                    }
                });
            }
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Flujo de Devolución con SweetAlert (1 paso)
    // ──────────────────────────────────────────────────────────────
    function confirmReturn() {
        Swal.fire({
            title: '<span class="fw-bold" style="color:#0dcaf0">↩️ Devolver OC para correcciones</span>',
            icon: 'info',
            html: `
                <div class="text-start">
                    <p class="text-muted small mb-3">
                        <strong>{{ $directPurchaseOrder->folio }}</strong>
                    </p>
                    <label class="form-label fw-bold small text-uppercase">
                        Indica al solicitante qué debe corregir:
                    </label>
                    <textarea id="swal-return-instructions" class="form-control" rows="5" maxlength="500"
                        placeholder="Ejemplo: Favor de corregir el centro de costos, debe ser 'Administración' no 'Operaciones'..."
                    ></textarea>
                    <div class="d-flex justify-content-end mt-1">
                        <span class="text-muted small" id="swal-return-char-count">0/500</span>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'DEVOLVER A SOLICITANTE',
            cancelButtonText: 'CANCELAR',
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            customClass: {
                confirmButton: 'text-dark',
            },
            didOpen: () => {
                const textarea   = document.getElementById('swal-return-instructions');
                const charCount  = document.getElementById('swal-return-char-count');
                const confirmBtn = Swal.getConfirmButton();

                confirmBtn.disabled = true;

                textarea.addEventListener('input', () => {
                    const len = textarea.value.length;
                    charCount.textContent = `${len}/500`;
                    confirmBtn.disabled = len === 0;
                });
            },
            preConfirm: () => {
                const instructions = document.getElementById('swal-return-instructions').value.trim();
                if (!instructions) {
                    Swal.showValidationMessage('Las instrucciones de corrección son obligatorias');
                    return false;
                }
                return instructions;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('return-comments-input').value = result.value;
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Notificando al solicitante con las instrucciones',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        document.getElementById('form-return').submit();
                    }
                });
            }
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Flujo de Rechazo con SweetAlert (2 pasos)
    // ──────────────────────────────────────────────────────────────
    function confirmReject(previousReason = '') {
        Swal.fire({
            title: '<span class="fw-bold text-danger">Rechazar Orden de Compra</span>',
            icon: 'warning',
            html: `
                <div class="text-start">
                    <label class="form-label fw-bold small text-uppercase text-danger">
                        Motivo del rechazo <span class="text-muted fw-normal">(mínimo 50 caracteres)</span>:
                    </label>
                    <textarea id="swal-reject-reason" class="form-control" rows="5" maxlength="500"
                        placeholder="Ejemplo: El proveedor no cumple con las especificaciones técnicas requeridas para el servicio de mantenimiento..."
                    ></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <span id="swal-min-msg" class="text-danger small" style="display:none">
                            Mínimo 50 caracteres requeridos
                        </span>
                        <span class="ms-auto text-muted small" id="swal-char-count">0/500</span>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'RECHAZAR OC',
            cancelButtonText: 'CANCELAR',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            didOpen: () => {
                const textarea   = document.getElementById('swal-reject-reason');
                const charCount  = document.getElementById('swal-char-count');
                const minMsg     = document.getElementById('swal-min-msg');
                const confirmBtn = Swal.getConfirmButton();

                // Restaurar texto previo si viene del botón VOLVER
                if (previousReason) {
                    textarea.value = previousReason;
                }

                const update = () => {
                    const len = textarea.value.length;
                    charCount.textContent = `${len}/500`;
                    const valid = len >= 50;
                    confirmBtn.disabled = !valid;
                    minMsg.style.display = valid ? 'none' : 'block';
                };

                update(); // estado inicial
                textarea.addEventListener('input', update);
            },
            preConfirm: () => {
                const reason = document.getElementById('swal-reject-reason').value;
                if (reason.length < 50) {
                    Swal.showValidationMessage('Mínimo 50 caracteres requeridos');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showRejectConfirmation(result.value);
            }
        });
    }

    function showRejectConfirmation(reason) {
        const preview = reason.length > 80 ? reason.substring(0, 80) + '...' : reason;

        Swal.fire({
            title: '<span class="fw-bold">⚠️ ¿Confirmar rechazo?</span>',
            html: `
                <div class="text-start">
                    <p class="fw-bold text-danger mb-2">Esta acción:</p>
                    <ul class="text-muted small mb-3">
                        <li>Liberará el presupuesto comprometido</li>
                        <li>Notificará al solicitante</li>
                        <li>La OC quedará bloqueada permanentemente</li>
                    </ul>
                    <div class="p-2 bg-light rounded border">
                        <p class="mb-1 small text-muted fw-bold">Motivo registrado:</p>
                        <p class="mb-0 fst-italic small">"${preview}"</p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-x me-1"></i>SÍ, RECHAZAR',
            cancelButtonText: 'VOLVER',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reject-comments-input').value = reason;
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Registrando rechazo y notificando al solicitante',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        document.getElementById('form-reject').submit();
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // VOLVER → regresa al paso 1 con el texto preservado
                confirmReject(reason);
            }
        });
    }

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
