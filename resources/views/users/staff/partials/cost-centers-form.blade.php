{{-- resources/views/users/staff/partials/cost-centers-form.blade.php --}}

<form id="userForm" 
      action="{{ route('users.cost-centers.update', $user->id) }}" 
      method="POST"
      data-form-type="cost-centers">
    @csrf
    @method('PATCH')

    {{-- HEADER DEL MODAL --}}
    <div class="modal-header bg-light">
        <h5 class="modal-title">
            <i class="ti ti-building-bank me-2"></i>
            Centros de Costo: {{ $user->name }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>

    {{-- BODY DEL MODAL --}}
    <div class="modal-body">
        
        {{-- Mensajes de error --}}
        <div id="formErrors" class="d-none"></div>

        {{-- Información importante --}}
        <div class="alert alert-info border-info d-flex align-items-start mb-4">
            <i class="ti ti-info-circle me-2 fs-5 mt-1"></i>
            <div>
                <strong>¿Cómo funciona?</strong>
                <ul class="mb-0 mt-2 small">
                    <li>Solo puedes asignar centros de costo de las <strong>compañías que ya tiene el usuario</strong>.</li>
                    <li>Puedes marcar uno como <strong>predeterminado</strong> (aparecerá seleccionado por defecto en formularios).</li>
                    <li>Los centros de costo están agrupados por compañía.</li>
                </ul>
            </div>
        </div>

        {{-- Verificar si tiene compañías --}}
        @if($user->companies->isEmpty())
            <div class="alert alert-warning border-warning">
                <i class="ti ti-alert-triangle me-2"></i>
                <strong>Sin compañías asignadas</strong>
                <p class="mb-0 mt-2 small">
                    Este usuario no tiene compañías asignadas. Primero debes asignarle al menos una compañía 
                    antes de poder asignarle centros de costo.
                </p>
            </div>
        @else
            {{-- Listado de centros de costo agrupados por compañía --}}
            <div class="accordion" id="costCentersAccordion">
                @foreach($user->companies as $company)
                    @php
                        $companyCostCenters = $company->costCenters;
                        $hasAssignedInCompany = $companyCostCenters->intersect($assignedCostCenters)->isNotEmpty();
                    @endphp
                    
                    <div class="accordion-item border">
                        <h2 class="accordion-header" id="heading-{{ $company->id }}">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse-{{ $company->id }}" 
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                    aria-controls="collapse-{{ $company->id }}">
                                <div class="d-flex align-items-center w-100">
                                    <span class="badge bg-primary me-2">{{ $company->code }}</span>
                                    <strong>{{ $company->name }}</strong>
                                    <span class="badge bg-secondary ms-auto me-2">
                                        {{ $companyCostCenters->count() }} centro(s)
                                    </span>
                                    @if($hasAssignedInCompany)
                                        <i class="ti ti-check text-success" title="Tiene centros asignados"></i>
                                    @endif
                                </div>
                            </button>
                        </h2>
                        <div id="collapse-{{ $company->id }}" 
                             class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                             aria-labelledby="heading-{{ $company->id }}" 
                             data-bs-parent="#costCentersAccordion">
                            <div class="accordion-body">
                                
                                @if($companyCostCenters->isEmpty())
                                    <div class="text-muted small">
                                        <i class="ti ti-info-circle me-1"></i>
                                        No hay centros de costo registrados para esta compañía.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50">
                                                        <div class="form-check">
                                                            <input class="form-check-input company-select-all" 
                                                                   type="checkbox" 
                                                                   data-company="{{ $company->id }}"
                                                                   id="selectAll-{{ $company->id }}">
                                                            <label class="form-check-label small" for="selectAll-{{ $company->id }}">
                                                                Todos
                                                            </label>
                                                        </div>
                                                    </th>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>Tipo</th>
                                                    <th width="100" class="text-center">Predeterminado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($companyCostCenters as $costCenter)
                                                    @php
                                                        $isAssigned = $assignedCostCenters->has($costCenter->id);
                                                        $isDefault = $isAssigned && $assignedCostCenters->get($costCenter->id)->pivot->is_default;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input cost-center-checkbox" 
                                                                       type="checkbox" 
                                                                       name="cost_centers[]" 
                                                                       value="{{ $costCenter->id }}"
                                                                       id="cc-{{ $costCenter->id }}"
                                                                       data-company="{{ $company->id }}"
                                                                       {{ $isAssigned ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <code class="small">{{ $costCenter->code }}</code>
                                                        </td>
                                                        <td>
                                                            <label for="cc-{{ $costCenter->id }}" class="form-label mb-0 cursor-pointer">
                                                                {{ $costCenter->name }}
                                                            </label>
                                                        </td>
                                                        <td>
                                                            @if($costCenter->budget_type === 'ANNUAL')
                                                                <span class="badge bg-info">Anual</span>
                                                            @else
                                                                <span class="badge bg-success">Consumo Libre</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check d-inline-block">
                                                                <input class="form-check-input default-radio" 
                                                                       type="radio" 
                                                                       name="default_cost_center" 
                                                                       value="{{ $costCenter->id }}"
                                                                       id="default-{{ $costCenter->id }}"
                                                                       data-cc-id="{{ $costCenter->id }}"
                                                                       {{ $isDefault ? 'checked' : '' }}
                                                                       {{ !$isAssigned ? 'disabled' : '' }}>
                                                                <label class="form-check-label visually-hidden" 
                                                                       for="default-{{ $costCenter->id }}">
                                                                    Predeterminado
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Nota sobre el predeterminado --}}
            <div class="alert alert-light border mt-3 mb-0">
                <small class="text-muted">
                    <i class="ti ti-info-circle me-1"></i>
                    <strong>Centro predeterminado:</strong> Marca un centro de costo como predeterminado 
                    para que aparezca seleccionado automáticamente en formularios.
                </small>
            </div>
        @endif
    </div>

    {{-- FOOTER DEL MODAL --}}
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>Cancelar
        </button>
        @if(!$user->companies->isEmpty())
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>Guardar Cambios
            </button>
        @endif
    </div>
</form>

{{-- JavaScript para el comportamiento del formulario --}}
<script>
$(document).ready(function() {
    
    // Seleccionar/deseleccionar todos los centros de una compañía
    $('.company-select-all').on('change', function() {
        const companyId = $(this).data('company');
        const isChecked = $(this).prop('checked');
        
        $(`.cost-center-checkbox[data-company="${companyId}"]`).prop('checked', isChecked).trigger('change');
    });
    
    // Cuando se marca/desmarca un centro de costo
    $('.cost-center-checkbox').on('change', function() {
        const ccId = $(this).val();
        const isChecked = $(this).prop('checked');
        const $defaultRadio = $(`.default-radio[data-cc-id="${ccId}"]`);
        
        // Habilitar/deshabilitar el radio de predeterminado
        $defaultRadio.prop('disabled', !isChecked);
        
        // Si se desmarca y era el predeterminado, limpiarlo
        if (!isChecked && $defaultRadio.prop('checked')) {
            $defaultRadio.prop('checked', false);
        }
        
        // Actualizar el estado del "Seleccionar todos" de la compañía
        const companyId = $(this).data('company');
        updateSelectAllState(companyId);
    });
    
    // Actualizar estado del checkbox "Seleccionar todos"
    function updateSelectAllState(companyId) {
        const $checkboxes = $(`.cost-center-checkbox[data-company="${companyId}"]`);
        const totalCheckboxes = $checkboxes.length;
        const checkedCheckboxes = $checkboxes.filter(':checked').length;
        
        const $selectAll = $(`.company-select-all[data-company="${companyId}"]`);
        
        if (checkedCheckboxes === 0) {
            $selectAll.prop('checked', false);
            $selectAll.prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $selectAll.prop('checked', true);
            $selectAll.prop('indeterminate', false);
        } else {
            $selectAll.prop('checked', false);
            $selectAll.prop('indeterminate', true);
        }
    }
    
    // Inicializar el estado de "Seleccionar todos" al cargar
    $('.company-select-all').each(function() {
        const companyId = $(this).data('company');
        updateSelectAllState(companyId);
    });
});
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #212529;
}
</style>