@extends('layouts.zircos')

@section('title', 'Listado de SIROC')

@section('page.title', 'SIROC (IMSS)')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">SIROC</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="ti ti-building-construction me-2"></i> Registros SIROC
        </h5>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-striped align-middle" id="sirocAdminTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Proveedor</th>
                    <th>Número SIROC</th>
                    <th>Obra</th>
                    <th>Ubicación</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estatus</th>
                    <th>Archivo</th>
                    <th style="width: 80px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sirocs as $idx => $s)
                    <tr>
                        <td class="col-idx text-muted">{{ $idx + 1 }}</td>
                        <td class="col-supplier">
                            <a href="#" class="fw-semibold">
                                {{ $s->supplier->company_name ?? $s->supplier->name ?? 'Proveedor' }}
                            </a>
                        </td>
                        <td class="col-number"><code>{{ $s->siroc_number }}</code></td>
                        <td class="col-work">{{ $s->work_name ?? '—' }}</td>
                        <td class="col-location text-truncate" style="max-width: 200px;">{{ $s->work_location ?? '—' }}</td>
                        <td class="col-start">{{ optional($s->start_date)->format('d/m/Y') ?? '—' }}</td>
                        <td class="col-end">{{ optional($s->end_date)->format('d/m/Y') ?? '—' }}</td>
                        <td class="col-status">
                            @php
                                $badges = ['vigente' => 'success', 'suspendido' => 'warning', 'terminado' => 'secondary'];
                                $icons  = ['vigente' => 'check', 'suspendido' => 'alert-triangle', 'terminado' => 'circle-off'];
                                $b = $badges[$s->status] ?? 'secondary';
                                $i = $icons[$s->status] ?? 'help';
                            @endphp
                            <span class="badge bg-{{ $b }}">
                                <i class="ti ti-{{ $i }} me-1"></i>{{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="col-file">
                            @if($s->siroc_file)
                                <a href="{{ asset('storage/' . $s->siroc_file) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                    <i class="ti ti-file-type-pdf"></i> PDF
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="col-actions text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('suppliers.sirocs.show', [$s->supplier, $s]) }}">
                                        <i class="ti ti-eye me-1"></i> Ver
                                    </a>
                                    <button type="button" class="dropdown-item js-edit-siroc"
                                        data-update-url="{{ route('suppliers.sirocs.update', [$s->supplier, $s]) }}"
                                        data-siroc-number="{{ e($s->siroc_number) }}"
                                        data-contract-number="{{ e($s->contract_number) }}"
                                        data-work-name="{{ e($s->work_name) }}"
                                        data-work-location="{{ e($s->work_location) }}"
                                        data-start-date="{{ optional($s->start_date)->format('Y-m-d') }}"
                                        data-end-date="{{ optional($s->end_date)->format('Y-m-d') }}"
                                        data-status="{{ $s->status }}"
                                        data-observations="{{ e($s->observations) }}"
                                        data-file-url="{{ $s->siroc_file ? asset('storage/'.$s->siroc_file) : '' }}">
                                        <i class="ti ti-pencil me-1"></i> Editar
                                    </button>
                                    <form action="{{ route('suppliers.sirocs.destroy', [$s->supplier, $s]) }}" method="POST" class="js-del-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ti ti-trash me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">No hay registros SIROC.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="modal fade" id="sirocEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-pencil me-2"></i> Editar SIROC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="editSirocForm" autocomplete="off" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="modal-body">
                    <div id="editSirocErrors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                        <label class="form-label">Número de registro SIROC</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-hash"></i></span>
                            <input type="text" name="siroc_number" class="form-control text-uppercase" maxlength="50">
                        </div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label">Número de contrato (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-file-text"></i></span>
                            <input type="text" name="contract_number" class="form-control" maxlength="100">
                        </div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label">Nombre / descripción de la obra</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-clipboard-text"></i></span>
                            <input type="text" name="work_name" class="form-control" maxlength="255">
                        </div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label">Ubicación de la obra</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
                            <input type="text" name="work_location" class="form-control" maxlength="255">
                        </div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label">Fecha de inicio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label">Fecha de término (estimada/real)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-calendar-check"></i></span>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="form-text">Debe ser mayor o igual a la fecha de inicio.</div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label">Estatus de la obra</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-traffic-cone"></i></span>
                            <select name="status" class="form-select">
                            <option value="vigente">Vigente</option>
                            <option value="suspendido">Suspendido</option>
                            <option value="terminado">Terminado</option>
                            </select>
                        </div>
                        </div>

                        <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-2">
                            <span>Constancia SIROC (PDF)</span>
                            <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Si adjuntas un PDF, reemplazará al existente. Máx. 5 MB."></i>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-file-type-pdf"></i></span>
                            <input type="file" name="siroc_file" id="edit_siroc_file" class="form-control" accept="application/pdf">
                        </div>
                        <div class="form-text">
                            Actual: <a href="#" target="_blank" id="currentSirocFileLink">—</a>
                        </div>
                        </div>

                        <div class="col-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observations" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                    </div>

                    <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnUpdateSiroc">
                        <i class="ti ti-device-floppy me-1"></i> Guardar cambios
                    </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-del-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Eliminar SIROC?',
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
                            window.location.reload();
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo eliminar el SIROC.'
                        });
                    });
                }
            });
        });
    });
});
</script>
<script>
(() => {
    const modalEl   = document.getElementById('sirocEditModal');
    const modal     = new bootstrap.Modal(modalEl);
    const form      = document.getElementById('editSirocForm');
    const errBox    = document.getElementById('editSirocErrors');
    const btnUpdate = document.getElementById('btnUpdateSiroc');

    let currentRow  = null;      // <tr> que estamos editando
    let updateUrl   = null;      // endpoint PUT

    const setLoading = (is) => {
        btnUpdate.disabled = !!is;
        btnUpdate.innerHTML = is
        ? `<span class="spinner-border spinner-border-sm me-2"></span> Guardando...`
        : `<i class="ti ti-device-floppy me-1"></i> Guardar cambios`;
    };

    const statusBadge = (status) => {
        const map = {
            vigente:    {b:'success',   i:'check',          label:'Vigente'},
            suspendido: {b:'warning',   i:'alert-triangle', label:'Suspendido'},
            terminado:  {b:'secondary', i:'circle-off',     label:'Terminado'}
        };
        const m = map[status] || {b:'secondary', i:'info-circle', label: (status||'—')};
        return `<span class="badge bg-${m.b}"><i class="ti ti-${m.i} me-1"></i>${m.label}</span>`;
    };

    const fmt = (d) => (d || '').slice(0,10) || '—';

    // Delegación: clic en "Editar"
    document.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.js-edit-siroc');
        if (!btn) return;

        currentRow = btn.closest('tr');
        updateUrl  = btn.dataset.updateUrl;

        // Prefill modal
        form.siroc_number.value    = btn.dataset.sirocNumber || '';
        form.contract_number.value = btn.dataset.contractNumber || '';
        form.work_name.value       = btn.dataset.workName || '';
        form.work_location.value   = btn.dataset.workLocation || '';
        form.start_date.value      = btn.dataset.startDate || '';
        form.end_date.value        = btn.dataset.endDate || '';
        form.status.value          = btn.dataset.status || 'vigente';
        form.observations.value    = btn.dataset.observations || '';

        const link   = document.getElementById('currentSirocFileLink');
        const fileUrl= btn.dataset.fileUrl || '';
        link.textContent = fileUrl ? 'Abrir PDF actual' : '—';
        link.href = fileUrl || '#';

        errBox.classList.add('d-none');
        errBox.innerHTML = '';

        modal.show();
    });

    // Enviar PUT (AJAX)
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validación simple de fechas
        const s = form.start_date.value;
        const x = form.end_date.value;
        if (s && x && x < s) {
            Swal.fire({icon:'warning', title:'Revisa las fechas', text:'La fecha de término debe ser >= inicio.'});
            return;
        }

        const fd = new FormData(form); // incluye _method=PUT
        errBox.classList.add('d-none');
        errBox.innerHTML = '';

        try {
            setLoading(true);
            const res = await fetch(updateUrl, {
                method: 'POST', // FormData con _method=PUT
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });

            let json = {};
            try { json = await res.json(); } catch(_){}

            if (!res.ok) {
                if (res.status === 422 && json?.errors) {
                    const list = Object.values(json.errors).flat().map(e => `<li>${e}</li>`).join('');
                    errBox.classList.remove('d-none');
                    errBox.innerHTML = `<ul class="mb-0">${list}</ul>`;
                    Swal.fire({icon:'error', title:'Datos inválidos', html:`<ul class="text-start">${list}</ul>`});
                    return;
                }
                Swal.fire({icon:'error', title:'Error', text: json?.message || 'No se pudo actualizar.'});
                return;
            }

            // Éxito: actualiza celdas de la fila por CLASE, no por índice
            const d = json?.data || {};

            currentRow.querySelector('.col-number').innerHTML   = `<code>${d.siroc_number || '—'}</code>`;
            currentRow.querySelector('.col-work').textContent   = d.work_name || '—';
            currentRow.querySelector('.col-location').textContent = d.work_location || '—';
            currentRow.querySelector('.col-start').textContent  = fmt(d.start_date);
            currentRow.querySelector('.col-end').textContent    = fmt(d.end_date);
            currentRow.querySelector('.col-status').innerHTML   = statusBadge(d.status);
            currentRow.querySelector('.col-file').innerHTML     =
                d.siroc_file_url
                    ? `<a href="${d.siroc_file_url}" target="_blank" class="btn btn-xs btn-outline-primary">
                          <i class="ti ti-file-type-pdf"></i> PDF
                       </a>`
                    : `<span class="text-muted">—</span>`;

            // Refresca los data-* del botón Editar
            const editBtn = currentRow.querySelector('.js-edit-siroc');
            if (editBtn) {
                editBtn.dataset.sirocNumber    = d.siroc_number || '';
                editBtn.dataset.contractNumber = d.contract_number || '';
                editBtn.dataset.workName       = d.work_name || '';
                editBtn.dataset.workLocation   = d.work_location || '';
                editBtn.dataset.startDate      = d.start_date ? d.start_date.substring(0,10) : '';
                editBtn.dataset.endDate        = d.end_date ? d.end_date.substring(0,10) : '';
                editBtn.dataset.status         = d.status || 'vigente';
                editBtn.dataset.observations   = d.observations || '';
                editBtn.dataset.fileUrl        = d.siroc_file_url || '';
            }

            Swal.fire({icon:'success', title:'Actualizado', timer:1600, showConfirmButton:false});
            modal.hide();

        } catch (err) {
            console.error(err);
            Swal.fire({icon:'error', title:'Error de red', text:'No se pudo conectar con el servidor.'});
        } finally {
            setLoading(false);
            // Limpia el file input
            form.querySelector('#edit_siroc_file').value = '';
        }
    });
})();
</script>

@endpush
