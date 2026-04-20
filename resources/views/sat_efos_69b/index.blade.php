@extends('layouts.zircos')

@section('title', 'Listado EFOS 69-B')
@section('page.title', 'Listado EFOS 69-B')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Listado EFOS 69-B</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">RFC listados en EFOS 69-B</h5>
        <button id="btn-sync-efos" class="btn btn-primary btn-sm">
            <i class="ti ti-refresh me-1"></i> Sincronizar SAT
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table-bordered table-hover w-100 table" id="efosTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>RFC</th>
                        <th>Razón Social</th>
                        <th>Situación</th>
                        <th>Presunción SAT</th>
                        <th>Presunción DOF</th>
                        <th>Definitivo SAT</th>
                        <th>Definitivo DOF</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function fmt(d) {
        if (!d) return '—';
        const dt = new Date(d);
        if (isNaN(dt)) return d;
        const dd = String(dt.getDate()).padStart(2,'0');
        const mm = String(dt.getMonth()+1).padStart(2,'0');
        return `${dd}/${mm}/${dt.getFullYear()}`;
    }

    function badge(sit) {
        const s = (sit || '').toString().toUpperCase();
        let cls = 'secondary';
        if (s.includes('DEFINITIVO'))         cls = 'danger';
        else if (s.includes('PRESUNTO'))      cls = 'warning';
        else if (s.includes('SENTENCIA'))     cls = 'primary';
        return `<span class="badge bg-${cls}">${sit ?? ''}</span>`;
    }

    window.efosTable = new DataTable('#efosTable', {
        ajax:        { url: "{{ route('sat_efos_69b.data') }}", dataSrc: 'data' },
        paging:      true,
        searching:   true,
        ordering:    true,
        responsive:  false,
        lengthMenu:  [10, 25, 50, 100],
        pageLength:  100,
        order:       [[1, 'asc']],
        buttons: [{
            extend:    'excel',
            text:      '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
            className: 'btn btn-success btn-sm',
            title:     'EFOS_69B_' + new Date().toISOString().split('T')[0]
        }],
        dom: '<"top"Bf>rt<"bottom"lip>',
        columns: [
            { data: 'number',       name: 'number' },
            { data: 'rfc',          name: 'rfc',          render: d => `<code>${d ?? ''}</code>` },
            { data: 'company_name', name: 'company_name' },
            { data: 'situation',    name: 'situation',    render: badge },
            { data: 'sat_presumption_notice_date', name: 'sat_presumption_notice_date', render: d => d || '—' },
            { data: 'dof_presumption_notice_date', name: 'dof_presumption_notice_date', render: d => d || '—' },
            { data: 'sat_definitive_publication_date', name: 'sat_definitive_publication_date', render: fmt },
            { data: 'dof_definitive_publication_date', name: 'dof_definitive_publication_date', render: fmt },
        ],
        language:   { url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}" },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        }
    });

    // ── Sync button ──────────────────────────────────────────────────────────
    const syncUrl       = "{{ route('sat_efos_69b.sync') }}";
    const syncStatusUrl = "{{ route('sat_efos_69b.sync.status', ':jobId') }}";
    const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let activeSyncInterval = null;

    document.getElementById('btn-sync-efos').addEventListener('click', function () {
        Swal.fire({
            title:             'Sincronización SAT EFOS',
            html:              buildModalHtml('Iniciando descarga del CSV...', 0, 0),
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen:           () => startSync(),
            willClose:         () => { clearInterval(activeSyncInterval); activeSyncInterval = null; },
        });
    });

    function buildModalHtml(msg, processed, total) {
        const pct = total > 0 ? Math.round((processed / total) * 100) : 0;
        const counter = total > 0
            ? `${processed.toLocaleString('es-MX')} / ${total.toLocaleString('es-MX')} registros`
            : '';
        return `
            <p class="mb-2" id="swal-msg">${msg}</p>
            <div class="progress mb-2" style="height:20px">
                <div id="swal-bar"
                     class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     role="progressbar"
                     style="width:${pct}%"
                     aria-valuenow="${pct}"
                     aria-valuemax="100">${pct > 0 ? pct + '%' : ''}</div>
            </div>
            <small class="text-muted" id="swal-counter">${counter}</small>`;
    }

    function startSync() {
        const btn = document.getElementById('btn-sync-efos');
        if (btn) btn.disabled = true;
        fetch(syncUrl, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(res => {
            if (res.status === 409) {
                if (btn) btn.disabled = false;
                Swal.fire('En curso', 'Ya hay una sincronización activa. Intenta más tarde.', 'warning');
                return null;
            }
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!data?.job_id) return;
            pollStatus(data.job_id);
        })
        .catch(() => {
            if (btn) btn.disabled = false;
            Swal.fire('Error', 'No se pudo iniciar la sincronización.', 'error');
        });
    }

    function pollStatus(jobId) {
        let networkErrors = 0;
        const url = syncStatusUrl.replace(':jobId', jobId);

        activeSyncInterval = setInterval(() => {
            fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                networkErrors = 0;
                updateModal(data);

                if (data.status === 'completed') {
                    clearInterval(activeSyncInterval);
                    const b = document.getElementById('btn-sync-efos');
                    if (b) b.disabled = false;
                    Swal.fire({
                        icon:  'success',
                        title: 'Sincronización completada',
                        text:  `${(data.processed || 0).toLocaleString('es-MX')} registros procesados.`,
                    }).then(() => window.efosTable.ajax.reload());
                } else if (data.status === 'failed') {
                    clearInterval(activeSyncInterval);
                    const b = document.getElementById('btn-sync-efos');
                    if (b) b.disabled = false;
                    Swal.fire('Error en sincronización', data.message || 'El proceso falló.', 'error');
                }
            })
            .catch(() => {
                networkErrors++;
                if (networkErrors >= 3) {
                    clearInterval(activeSyncInterval);
                    const b = document.getElementById('btn-sync-efos');
                    if (b) b.disabled = false;
                    Swal.fire('Error de red', 'No se pudo obtener el estado del proceso.', 'error');
                }
            });
        }, 2000);
    }

    function updateModal(data) {
        const msg     = document.getElementById('swal-msg');
        const bar     = document.getElementById('swal-bar');
        const counter = document.getElementById('swal-counter');
        if (!msg || !bar) return;

        const processed = data.processed || 0;
        const total     = data.total     || 0;
        const pct       = total > 0 ? Math.round((processed / total) * 100) : 0;

        bar.style.width = pct + '%';
        bar.setAttribute('aria-valuenow', pct);
        bar.textContent = pct > 0 ? pct + '%' : '';
        msg.textContent = data.message || 'Procesando...';
        if (counter && total > 0) {
            counter.textContent = `${processed.toLocaleString('es-MX')} / ${total.toLocaleString('es-MX')} registros`;
        }
    }
});
</script>
@endpush
