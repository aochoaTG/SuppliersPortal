@php
    // Defaults y old()
    $fiscalYear = old('fiscal_year', $annualBudget->fiscal_year ?? date('Y'));
    $selectedCompanyId = (int) old('company_id', $annualBudget->company_id ?? 0);
    $selectedCostCenterId = (int) old('cost_center_id', $annualBudget->cost_center_id ?? 0);
@endphp

<div class="row g-3">
    {{-- Compañía --}}
    <div class="col-md-6">
        <label for="company_id" class="form-label">Compañía <span class="text-danger">*</span></label>
        <select id="company_id" name="company_id" class="@error('company_id') is-invalid @enderror form-select"
            data-url-costcenters="{{ route('api.cost-centers.by-company', ['company' => '__CID__']) }}"
            data-selected-cc="{{ $selectedCostCenterId }}">
            <option value="">-- Selecciona --</option>
            @foreach ($companies as $c)
                <option value="{{ $c->id }}" {{ (int) $selectedCompanyId === (int) $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>
        @error('company_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Año fiscal --}}
    <div class="col-md-3">
        <label for="fiscal_year" class="form-label">Año fiscal <span class="text-danger">*</span></label>
        <input type="number" min="2000" max="2100" id="fiscal_year" name="fiscal_year"
            class="form-control @error('fiscal_year') is-invalid @enderror" value="{{ $fiscalYear }}">
        @error('fiscal_year')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Centro de costo (dependiente de compañía) --}}
    <div class="col-md-6">
        <label for="cost_center_id" class="form-label">Centro de costo <span class="text-danger">*</span></label>
        <select id="cost_center_id" name="cost_center_id"
            class="@error('cost_center_id') is-invalid @enderror form-select">
            <option value="">-- Selecciona --</option>
            @foreach ($costCenters as $cc)
                @if ((int) $cc->company_id === (int) $selectedCompanyId)
                    <option value="{{ $cc->id }}"
                        {{ (int) $selectedCostCenterId === (int) $cc->id ? 'selected' : '' }}>
                        {{ $cc->code ? "[$cc->code] " : '' }}{{ $cc->name }}
                    </option>
                @endif
            @endforeach
        </select>
        @error('cost_center_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">El presupuesto aplica al centro seleccionado para el año fiscal indicado.</div>
    </div>

    {{-- Monto asignado --}}
    <div class="col-md-3">
        <label for="amount_assigned" class="form-label">Monto asignado <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="amount_assigned" name="amount_assigned"
            class="form-control @error('amount_assigned') is-invalid @enderror"
            value="{{ old('amount_assigned', $annualBudget->amount_assigned ?? '') }}" placeholder="0.00">
        @error('amount_assigned')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const $company = document.getElementById('company_id');
            const $cc = document.getElementById('cost_center_id');

            async function loadCostCenters(companyId, selectedCcId) {
                if (!companyId) {
                    $cc.innerHTML = '<option value="">-- Selecciona --</option>';
                    return;
                }

                const tpl = ($company.getAttribute('data-url-costcenters') || '').replace('__CID__', companyId);
                try {
                    const res = await fetch(tpl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Error al cargar centros');
                    const data = await res.json(); // espera [{id,name,code},...]

                    let options = '<option value="">-- Selecciona --</option>';
                    data.forEach(item => {
                        const label = (item.code ? `[${item.code}] ` : '') + item.name;
                        const sel = Number(selectedCcId) === Number(item.id) ? 'selected' : '';
                        options += `<option value="${item.id}" ${sel}>${label}</option>`;
                    });
                    $cc.innerHTML = options;
                } catch (e) {
                    console.error(e);
                    $cc.innerHTML = '<option value="">(No fue posible cargar centros)</option>';
                }
            }

            // Al cambiar compañía, recarga centros
            $company?.addEventListener('change', function() {
                const selected = this.value || '';
                loadCostCenters(selected, '');
            });

            // Inicializa (si trae preselecciones)
            document.addEventListener('DOMContentLoaded', function() {
                const compId = $company?.value || '';
                const selectedCcId = $company?.getAttribute('data-selected-cc') || '';
                if (compId) loadCostCenters(compId, selectedCcId);
            });
        })();
    </script>
@endpush
