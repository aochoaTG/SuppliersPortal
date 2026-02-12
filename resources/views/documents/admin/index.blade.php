@extends('layouts.zircos')

@php
    use Illuminate\Support\Facades\Storage;

    // Mapa de etiquetas amigables
    $labels = [
        'constancia_fiscal'        => 'Constancia de situación fiscal',
        'comprobante_domicilio'    => 'Comprobante de domicilio',
        'caratula_bancaria'        => 'Carátula bancaria',
        'opinion_sat'              => 'Opinión positiva del SAT',
        'acta_constitutiva'        => 'Acta constitutiva',
        'poder_legal'              => 'Poder legal',
        'identificacion_oficial'   => 'Identificación oficial',
        'opinion_imss'             => 'Opinión del IMSS',
        'opinion_infonavit'        => 'Opinión del INFONAVIT',
        'solicitud_alta_proveedor' => 'Solicitud de alta de proveedor',
        'repse'                    => 'REPSE',
        'acta_confidencialidad'    => 'Acta de confidencialidad',
        'curso_induccion'          => 'Curso de inducción',
    ];

    // Helper badge
    function badge_status($s) {
        return match ($s) {
            'accepted'       => '<span class="badge bg-success">Aprobado</span>',
            'rejected'       => '<span class="badge bg-danger">Rechazado</span>',
            'pending_review' => '<span class="badge bg-warning text-dark">En revisión</span>',
            default          => '<span class="badge bg-secondary">—</span>',
        };
    }
@endphp

@section('title','Revisión de documentos')
@push('styles')
<style>
    .nav-workmodes .nav-link { font-weight: 600; }
    .stat-card .value { font-size: 1.35rem; font-weight: 700; }
    .stat-card .label { font-size: .85rem; color: #6c757d; }
    .table td, .table th { vertical-align: middle; }
    .chip { display:inline-block; padding:.15rem .5rem; border-radius:999px; font-size:.75rem; }
    .chip.ok { background:#e9f7ef; color:#198754; }
    .chip.bad { background:#fdecea; color:#dc3545; }
    .chip.wait { background:#fff3cd; color:#856404; }
</style>
@endpush

@section('page.title','Revisión de documentos')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
    <li class="breadcrumb-item active">Revisión</li>
@endsection

@section('content')

    {{-- KPIs superiores (solo display) --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="value" id="kpiPendientes">{{ $kpiPendientes }}</div>
                        <div class="label">Pendientes de revisión</div>
                    </div>
                    <i class="ti ti-hourglass-high fs-2 text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="value" id="kpiAprobadosHoy">{{ $kpiAprobadosHoy }}</div>
                        <div class="label">Aprobados hoy</div>
                    </div>
                    <i class="ti ti-checks fs-2 text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="value" id="kpiRechazadosHoy">{{ $kpiRechazadosHoy }}</div>
                        <div class="label">Rechazados hoy</div>
                    </div>
                    <i class="ti ti-x fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs de modos de trabajo --}}
    <ul class="nav nav-pills nav-workmodes mb-3" id="workModes" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="bandeja-tab" data-bs-toggle="pill" data-bs-target="#bandejaPane" type="button" role="tab" aria-controls="bandejaPane" aria-selected="true">
                <i class="ti ti-inbox me-1"></i> Bandeja (documentos)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="proveedores-tab" data-bs-toggle="pill" data-bs-target="#proveedoresPane" type="button" role="tab" aria-controls="proveedoresPane" aria-selected="false">
                <i class="ti ti-users me-1"></i> Proveedores
            </button>
        </li>
    </ul>

    <div class="tab-content" id="workModesContent">

        {{-- PANE 1: BANDEJA --}}
        <div class="tab-pane fade show active" id="bandejaPane" role="tabpanel" aria-labelledby="bandeja-tab" tabindex="0">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Documentos pendientes de revisión</h5>
                    <span class="text-muted small"></span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>RFC</th>
                                <th>Tipo de documento</th>
                                <th>Subido por</th>
                                <th>Fecha carga</th>
                                <th>Status</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingDocs as $doc)
                                @php
                                    $prov = $doc->supplier;
                                    $uploader = $doc->uploader;
                                    $type        = $doc->doc_type;
                                    $label = $labels[$doc->doc_type] ?? ucfirst(str_replace('_',' ',$doc->doc_type));
                                    $url = Storage::disk('public')->url($doc->path_file);
                                    // Construimos la URL de retroalimentación para este documento
                                    $feedbackUrl = route('documents.suppliers.feedback', [
                                        'supplier' => $prov->id,
                                        'type'     => $type,
                                        'document' => $doc->id, // o null si quieres mantenerlo opcional
                                    ]);
                                @endphp
                                <tr>
                                    <td>{{ $prov?->company_name ?? '—' }}</td>
                                    <td>{{ $prov?->rfc ?? '—' }}</td>
                                    <td><span class="badge bg-info">{{ $label }}</span></td>
                                    <td>{{ $uploader?->name ?? '—' }}</td>
                                    <td>{{ optional($doc->uploaded_at ?? $doc->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{!! badge_status($doc->status) !!}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-settings"></i> Acciones
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ $url }}" target="_blank">
                                                        <i class="ti ti-eye me-1"></i> Abrir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item js-accept-doc"
                                                    href="javascript:void(0);"
                                                    data-url="{{ route('admin.review.documents.accept', $doc) }}">
                                                        <i class="ti ti-check me-1 text-success"></i> Aprobar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item js-reject-doc"
                                                    href="javascript:void(0);"
                                                    data-url="{{ route('admin.review.documents.reject', $doc) }}">
                                                        <i class="ti ti-x me-1 text-danger"></i> Rechazar
                                                    </a>
                                                </li>
                                                {{-- Retroalimentación: SIEMPRE visible --}}
                                                <li>
                                                    <a class="dropdown-item js-feedback-doc" href="javascript:void(0);" data-url="{{ $feedbackUrl }}" data-supplier="{{ $prov->id }}" data-type="{{ $type }}" data-doc="{{ $doc->id ?? '' }}">
                                                        <i class="ti ti-message-dots me-1"></i> Retroalimentación
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                            <i class="ti ti-inbox-off fs-1 mb-2"></i>
                                            <h6 class="mb-1">Sin documentos pendientes</h6>
                                            <p class="mb-0 small">Por ahora no hay archivos que requieran revisión.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="text-muted small mt-2">
                        <i class="ti ti-info-circle me-1"></i>
                        En esta bandeja puedes <strong>aprobar o rechazar</strong> documentos cargados por los proveedores.
                        Está pensada para revisión rápida, documento por documento.
                    </div>
                </div>
            </div>
        </div>

        {{-- PANE 2: PROVEEDORES --}}
        <div class="tab-pane fade" id="proveedoresPane" role="tabpanel" aria-labelledby="proveedores-tab" tabindex="0">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Estado por proveedor</h5>
                    <span class="text-muted small"></span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>RFC</th>
                                <th>Avance</th>
                                <th>Aprobados</th>
                                <th>Rechazados</th>
                                <th>Pendientes</th>
                                <th>Última actividad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliersSummary as $row)
                                @php
                                    $s = $row['supplier']; // instancia Supplier
                                    $totalReq = $row['total_required'] ?? count($requiredTypes);
                                    $uploaded  = $row['uploaded']  ?? 0;
                                    $accepted  = $row['accepted']  ?? 0;
                                    $rejected  = $row['rejected']  ?? 0;
                                    $pending   = max($totalReq - $accepted - $rejected, 0); // display simple
                                    $pct = $totalReq > 0 ? round(($uploaded / $totalReq) * 100) : 0;
                                    $last = $row['last_activity_at'] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $s->company_name ?? '—' }}</td>
                                    <td>{{ $s->rfc ?? '—' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress" style="width:140px;height:10px;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td><span class="chip ok">{{ $accepted }}</span></td>
                                    <td><span class="chip bad">{{ $rejected }}</span></td>
                                    <td><span class="chip wait">{{ $pending }}</span></td>
                                    <td>{{ $last ? \Illuminate\Support\Carbon::parse($last)->format('Y-m-d H:i') : '—' }}</td>
                                    <td>
                                        <a href="{{ route('admin.review.suppliers.show', $row['supplier']->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye mx-1"></i> Ver detalles
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Sin proveedores en seguimiento.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="text-muted small mt-2">
                        <i class="ti ti-info-circle me-1"></i>
                        Esta vista muestra el <strong>avance por proveedor</strong>.
                        Aquí no se aprueban ni rechazan documentos, solo es para seguimiento y auditoría.
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
$.ajaxSetup({
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
});
$(function () {
    // Solo navegación de tabs (Bootstrap 5 ya hace el toggle; esto es por si quieres recordar la última pestaña)
    const key = 'reviewTab';
    const last = localStorage.getItem(key);
    if (last) {
        const triggerEl = document.querySelector(`[data-bs-target="${last}"]`);
        if (triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();
    }
    document.querySelectorAll('#workModes [data-bs-toggle="pill"]').forEach(el => {
        el.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            localStorage.setItem(key, target);
        });
    });

    // Aceptar
    $(document).on('click', '.js-accept-doc', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: '¿Aprobar documento?',
            text: "Esta acción no se puede deshacer.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post(url) // POST simple, con CSRF vía meta en tu layout
            .done(function (json) {
                // Actualiza la fila visualmente
                $row.find('td:nth-child(6)').html('<span class="badge bg-success">Aprobado</span>'); // asumiendo col 6 = Status
                Swal.fire({ icon: 'success', title: 'Aprobado', timer: 1400, showConfirmButton: false });

                // ACEPTAR
                let pendientes = parseInt($('#kpiPendientes').text());
                let aprobados  = parseInt($('#kpiAprobadosHoy').text());

                $('#kpiPendientes').text(pendientes - 1);
                $('#kpiAprobadosHoy').text(aprobados + 1);
            })
            .fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo aprobar.' });
                console.error(xhr?.responseText || xhr);
            });
        });
    });

    // Rechazar
    $(document).on('click', '.js-reject-doc', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Rechazar documento',
            input: 'textarea',
            inputLabel: 'Motivo de rechazo',
            inputPlaceholder: 'Escribe el motivo…',
            inputAttributes: { 'aria-label': 'Motivo de rechazo' },
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar',
            preConfirm: (reason) => {
                if (!reason || reason.trim().length < 5) {
                    Swal.showValidationMessage('El motivo es obligatorio (mín. 5 caracteres).');
                }
                return reason;
            }
        }).then(res => {
            if (!res.isConfirmed) return;

            $.post(url, { reason: res.value })
            .done(function (json) {
                // Actualiza la fila visualmente
                $row.find('td:nth-child(6)').html('<span class="badge bg-danger">Rechazado</span>');
                // Si tienes una columna de motivo (o tooltip), podrías añadirlo aquí
                Swal.fire({ icon: 'success', title: 'Rechazado', timer: 1400, showConfirmButton: false });

                // RECHAZAR
                let pendientes = parseInt($('#kpiPendientes').text());
                let rechazados = parseInt($('#kpiRechazadosHoy').text());

                $('#kpiPendientes').text(pendientes - 1);
                $('#kpiRechazadosHoy').text(rechazados + 1);
            })
            .fail(function (xhr) {
                let msg = 'No se pudo rechazar.';
                if (xhr.status === 422 && xhr.responseJSON?.errors?.reason) {
                    msg = xhr.responseJSON.errors.reason.join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                console.error(xhr?.responseText || xhr);
            });
        });
    });

});

// RETROALIMENTACIÓN (envía correo al proveedor)
$(document).on('click', '.js-feedback-doc', function (e) {
    e.preventDefault();
    const url       = $(this).data('url');      // ruta para enviar el correo
    const type      = $(this).data('type');     // tipo de documento (string)
    const docId     = $(this).data('doc') || ''; // id del doc (puede venir vacío)
    const labelCell = $(this).closest('tr').find('.doc-label').text().trim();

    Swal.fire({
        title: 'Enviar retroalimentación',
        html: `
            <div class="text-start">
                <div class="mb-2 small text-muted">
                    <i class="ti ti-file-description me-1"></i>
                    Documento / Tipo: <strong>${labelCell || type}</strong>
                </div>
                <textarea id="swal-feedback-message" class="form-control" rows="5" placeholder="Escribe la retroalimentación para el proveedor..."></textarea>
                <div class="form-text mt-1">
                    Este mensaje se enviará por correo al contacto del proveedor.
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const message = document.getElementById('swal-feedback-message').value || '';
            if (message.trim().length < 5) {
                Swal.showValidationMessage('El mensaje es obligatorio (mín. 5 caracteres).');
                return false;
            }
            return { message };
        }
    }).then(res => {
        if (!res.isConfirmed) return;

        $.post(url, { feedback: res.value.message, doc_id: docId, type: type })
            .done(() => {
                Swal.fire({ icon: 'success', title: 'Enviado', text: 'La retroalimentación fue enviada al proveedor.', timer: 1600, showConfirmButton: false });
            })
            .fail(xhr => {
                let msg = 'No se pudo enviar la retroalimentación.';
                if (xhr.status === 422 && xhr.responseJSON?.errors?.message) {
                    msg = xhr.responseJSON.errors.message.join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                console.error(xhr?.responseText || xhr);
            });
    });
});

</script>
@endpush
