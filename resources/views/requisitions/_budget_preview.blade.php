<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <strong>Budget Preview</strong>
        <span id="bpCurrency" class="badge bg-light text-dark">MXN</span>
    </div>
    <div class="card-body">
        <div id="bpAlert" class="alert d-none" role="alert"></div>

        <dl class="row mb-2">
            <dt class="col-7">Asignado</dt>
            <dd class="col-5 text-end" data-kpi="assigned">—</dd>
            <dt class="col-7">Comprometido</dt>
            <dd class="col-5 text-end" data-kpi="committed">—</dd>
            <dt class="col-7">Consumido</dt>
            <dd class="col-5 text-end" data-kpi="consumed">—</dd>
            <dt class="col-7">Liberado</dt>
            <dd class="col-5 text-end" data-kpi="released">—</dd>
            <dt class="col-7">Ajustes</dt>
            <dd class="col-5 text-end" data-kpi="adjusted">—</dd>
        </dl>

        <div class="d-flex justify-content-between align-items-center">
            <div><strong>Disponible</strong></div>
            <div><span class="badge" id="bpAvailable">—</span></div>
        </div>

        <div class="d-grid mt-3 gap-2">
            <button type="button" class="btn btn-outline-warning btn-sm d-none" id="btnException">
                <i class="ti ti-alert-triangle"></i> Solicitar excepción
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        async function loadBudgetPreview() {
            const cc = document.getElementById('cost_center_id')?.value;
            const fy = document.getElementById('fiscal_year')?.value;
            const alertBox = document.getElementById('bpAlert');
            const avBadge = document.getElementById('bpAvailable');
            const curBadge = document.getElementById('bpCurrency');

            if (!cc || !fy) {
                return;
            }

            try {
                const url = `{{ route('budget.snapshot') }}?cost_center_id=${cc}&fiscal_year=${fy}`;
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) return;
                const k = await res.json();

                curBadge.textContent = k.currency || 'MXN';

                const fmt = v => (parseFloat(v || 0)).toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                ['assigned', 'committed', 'consumed', 'released', 'adjusted'].forEach(kp => {
                    const el = document.querySelector(`[data-kpi="${kp}"]`);
                    if (el) el.textContent = fmt(k[kp] ?? 0);
                });

                const requested = parseFloat((document.getElementById('amount_requested').value || '0').toString()
                    .replace(/,/g, ''));
                const available = parseFloat(k.available ?? 0);
                avBadge.textContent = fmt(available);

                // Color del badge disponible
                let cls = 'bg-success';
                if (available === requested) cls = 'bg-warning';
                if (available < requested) cls = 'bg-danger';
                avBadge.className = 'badge ' + cls;

                // Alerta/alarma clara
                alertBox.classList.add('d-none');
                alertBox.className = 'alert';

                if (available < requested) {
                    alertBox.classList.remove('d-none');
                    alertBox.classList.add('alert-danger');
                    alertBox.innerHTML =
                        `<strong>¡Alerta!</strong> El monto solicitado (${fmt(requested)}) supera el disponible (${fmt(available)}).`;
                    document.getElementById('btnException').classList.remove('d-none');
                } else if (available === requested && requested > 0) {
                    alertBox.classList.remove('d-none');
                    alertBox.classList.add('alert-warning');
                    alertBox.textContent = 'Advertencia: el disponible quedará en 0 si apruebas esta requisición.';
                    document.getElementById('btnException').classList.add('d-none');
                } else {
                    document.getElementById('btnException').classList.add('d-none');
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Hooks
        document.addEventListener('DOMContentLoaded', loadBudgetPreview);
        document.getElementById('cost_center_id')?.addEventListener('change', loadBudgetPreview);
        document.getElementById('fiscal_year')?.addEventListener('input', loadBudgetPreview);
        document.getElementById('itemsTable')?.addEventListener('input', function(e) {
            if (e.target.matches('.qty-input, .price-input, .tax-input')) {
                loadBudgetPreview();
            }
        });
    </script>
@endpush
