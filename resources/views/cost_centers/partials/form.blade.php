<div class="row g-3">
    {{-- ===== SECCION 1: DATOS BASE ===== --}}
    <div class="col-md-4">
        <label for="code" class="form-label">Codigo <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
            value="{{ old('code', $costCenter->code ?? '') }}" placeholder="Ej.: E04188, CORP01, PROY-MIGUEL">
        @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Identificador unico para reportes/integraciones.</div>
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $costCenter->name ?? '') }}" placeholder="Ej.: Estacion 07 Gemela Grande">
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="description" class="form-label">Descripcion</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
            rows="3" placeholder="Descripcion detallada del centro de costo (opcional)">{{ old('description', $costCenter->description ?? '') }}</textarea>
        @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="purchase_type" class="form-label">Tipo de Compra <span class="text-danger">*</span></label>
        <select id="purchase_type" name="purchase_type" class="@error('purchase_type') is-invalid @enderror form-select" required>
            <option value="">Seleccionar tipo de compra</option>
            @foreach (['Gasto Operativo', 'Gasto Staff', 'Gasto Corporativo'] as $purchaseType)
            <option value="{{ $purchaseType }}"
                {{ old('purchase_type', $costCenter->purchase_type?->value ?? $costCenter->purchase_type ?? '') === $purchaseType ? 'selected' : '' }}>
                {{ $purchaseType }}
            </option>
            @endforeach
        </select>
        @error('purchase_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ===== SECCION 2: RELACIONES ORGANIZACIONALES ===== --}}
    <div class="col-12">
        <hr>
        <h6 class="mb-3"><i class="ti ti-org"></i> Organizacion</h6>
    </div>

    <div class="col-md-4">
        <label for="company_id" class="form-label">Empresa <span class="text-danger">*</span></label>
        <select id="company_id" name="company_id" class="@error('company_id') is-invalid @enderror form-select">
            <option value="">-- Selecciona empresa --</option>
            @foreach ($companies as $company)
            <option value="{{ $company->id }}"
                {{ (int) old('company_id', $costCenter->company_id ?? 0) === (int) $company->id ? 'selected' : '' }}>
                {{ $company->name }}
            </option>
            @endforeach
        </select>
        @error('company_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="category_id" class="form-label">Categoria <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="@error('category_id') is-invalid @enderror form-select">
            <option value="">-- Selecciona categoria --</option>
            @foreach ($categories as $cat)
            <option value="{{ $cat->id }}"
                {{ (int) old('category_id', $costCenter->category_id ?? 0) === (int) $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
            @endforeach
        </select>
        @error('category_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="responsible_user_id" class="form-label">Responsable (Jefe de Area) <span
                class="text-danger">*</span></label>
        <select id="responsible_user_id" name="responsible_user_id"
            class="@error('responsible_user_id') is-invalid @enderror form-select">
            <option value="">-- Selecciona responsable --</option>
            @foreach ($users as $user)
            <option value="{{ $user->id }}"
                {{ (int) old('responsible_user_id', $costCenter->responsible_user_id ?? 0) === (int) $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
            @endforeach
        </select>
        @error('responsible_user_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Jefe de Area responsable de este centro de costo.</div>
    </div>

    {{-- ===== SECCION 3: TIPO DE PRESUPUESTO ===== --}}
    <div class="col-12">
        <hr>
        <h6 class="mb-3"><i class="ti ti-coin"></i> Presupuesto</h6>
    </div>

    <div class="col-md-4">
        <label for="budget_type" class="form-label">Tipo de Presupuesto <span class="text-danger">*</span></label>
        <select id="budget_type" name="budget_type" class="@error('budget_type') is-invalid @enderror form-select">
            <option value="">-- Selecciona tipo --</option>
            <option value="ANNUAL"
                {{ old('budget_type', $costCenter->budget_type ?? '') === 'ANNUAL' ? 'selected' : '' }}>
                Presupuesto Anual
            </option>
            <option value="FREE_CONSUMPTION"
                {{ old('budget_type', $costCenter->budget_type ?? '') === 'FREE_CONSUMPTION' ? 'selected' : '' }}>
                Consumo Libre
            </option>
        </select>
        @error('budget_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            <strong>Anual:</strong> Presupuesto dividido mensualmente.<br>
            <strong>Consumo Libre:</strong> Monto global sin limites temporales.
        </div>
    </div>

    <div id="freeConsumptionFields" class="col-md-4" style="display: none;">
        <label for="global_amount" class="form-label">Monto Global Autorizado <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" class="form-control @error('global_amount') is-invalid @enderror"
                id="global_amount" name="global_amount"
                value="{{ old('global_amount', $costCenter->global_amount ?? '') }}" placeholder="0.00">
        </div>
        @error('global_amount')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Monto total autorizado para consumo libre.</div>
    </div>

    <div id="validityDateField" class="col-md-4" style="display: none;">
        <label for="validity_date" class="form-label">
            Fecha de Vigencia <span class="text-danger">*</span>
            @if (!auth()->user()->hasRole('superadmin'))
            <span class="badge bg-warning-subtle text-warning ms-2">
                <i class="ti ti-lock"></i> Solo lectura
            </span>
            @endif
        </label>
        <input type="date"
            class="form-control @error('validity_date') is-invalid @enderror"
            id="validity_date"
            name="validity_date"
            value="{{ old('validity_date', $costCenter->validity_date?->format('Y-m-d') ?? '') }}"
            min="{{ date('Y-m-d') }}"
            {{ !auth()->user()->hasRole('superadmin') ? 'readonly' : '' }}>
        @error('validity_date')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            @if (auth()->user()->hasRole('superadmin'))
            Fecha limite de vigencia del centro de consumo libre.
            @else
            Campo bloqueado. Solo modificable por superadministradores.
            @endif
        </div>
    </div>

    <div id="freeConsumptionJustification" class="col-md-12" style="display: none;">
        <label for="free_consumption_justification" class="form-label">Justificacion <span class="text-danger">*</span></label>
        <textarea class="form-control @error('free_consumption_justification') is-invalid @enderror"
            id="free_consumption_justification" name="free_consumption_justification" rows="3"
            placeholder="Justificacion del consumo libre (obra, proyecto, uso continuo, etc.)">{{ old('free_consumption_justification', $costCenter->free_consumption_justification ?? '') }}</textarea>
        @error('free_consumption_justification')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Explica por que este centro requiere consumo libre.</div>
    </div>

    {{-- ===== SECCION 4: ESTADO ===== --}}
    <div class="col-12">
        <hr>
        <h6 class="mb-3"><i class="ti ti-status"></i> Estado</h6>
    </div>

    <div class="col-md-12">
        <label for="cost_center_status" class="form-label">Estado <span class="text-danger">*</span></label>
        <select id="cost_center_status" name="status" class="@error('status') is-invalid @enderror form-select">
            <option value="">-- Selecciona estado --</option>
            <option value="ACTIVO"
                {{ old('status', $costCenter->status ?? 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>
                Activo
            </option>
            <option value="INACTIVO"
                {{ old('status', $costCenter->status ?? 'ACTIVO') === 'INACTIVO' ? 'selected' : '' }}>
                Inactivo
            </option>
        </select>
        @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Centros inactivos no pueden usarse en nuevas requisiciones.</div>
    </div>

    {{-- ===== AUDITORIA (solo lectura en edicion) ===== --}}
    @if ($costCenter->id)
    <div class="col-12">
        <hr>
        <h6 class="mb-3"><i class="ti ti-history"></i> Auditoria</h6>
    </div>

    <div class="col-md-6">
        <label class="form-label">Creado por</label>
        <div class="form-control-plaintext">
            {{ $costCenter->createdBy?->name ?? '-' }}
            <small class="text-muted d-block">{{ $costCenter->created_at?->format('d/m/Y H:i') ?? '-' }}</small>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Ultimo cambio</label>
        <div class="form-control-plaintext">
            {{ $costCenter->updatedBy?->name ?? '-' }}
            <small class="text-muted d-block">{{ $costCenter->updated_at?->format('d/m/Y H:i') ?? '-' }}</small>
        </div>
    </div>

    @if ($costCenter->deleted_at)
    <div class="col-md-6">
        <label class="form-label">Eliminado por</label>
        <div class="form-control-plaintext text-danger">
            {{ $costCenter->deletedBy?->name ?? '-' }}
            <small class="text-muted d-block">{{ $costCenter->deleted_at?->format('d/m/Y H:i') ?? '-' }}</small>
        </div>
    </div>
    @endif
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const budgetTypeSelect = document.getElementById('budget_type');
        const freeConsumptionFields = document.getElementById('freeConsumptionFields');
        const validityDateField = document.getElementById('validityDateField');
        const freeConsumptionJustification = document.getElementById('freeConsumptionJustification');

        function toggleFreeConsumptionFields() {
            const isFreeConsumption = budgetTypeSelect.value === 'FREE_CONSUMPTION';
            freeConsumptionFields.style.display = isFreeConsumption ? 'block' : 'none';
            validityDateField.style.display = isFreeConsumption ? 'block' : 'none';
            freeConsumptionJustification.style.display = isFreeConsumption ? 'block' : 'none';
        }

        toggleFreeConsumptionFields();
        budgetTypeSelect.addEventListener('change', toggleFreeConsumptionFields);
    });
</script>
@endpush
