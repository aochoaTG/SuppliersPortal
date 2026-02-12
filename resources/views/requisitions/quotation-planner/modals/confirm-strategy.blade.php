{{--
    Modal: Confirmar Estrategia de Cotización
--}}

<div class="modal fade" id="confirmStrategyModal" tabindex="-1" aria-labelledby="confirmStrategyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmStrategyModalLabel">
                    <i class="ti ti-check-circle me-2"></i>
                    Confirmar Estrategia de Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Estás a punto de continuar al siguiente paso:</strong> Selección de Proveedores
                </div>

                <h6 class="mb-3">
                    <i class="ti ti-chart-pie me-2"></i>
                    Resumen de tu Estrategia:
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <i class="ti ti-folders text-primary" style="font-size: 2rem;"></i>
                                <h3 class="mt-2 mb-1" id="confirmGroupsCount">{{ $groups->count() }}</h3>
                                <p class="text-muted small mb-0">Grupos Creados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <i class="ti ti-package text-success" style="font-size: 2rem;"></i>
                                <h3 class="mt-2 mb-1" id="confirmAssignedItems">
                                    {{ $requisition->items->count() - $unassignedItems->count() }}
                                </h3>
                                <p class="text-muted small mb-0">Partidas Agrupadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning h-100">
                            <div class="card-body text-center">
                                <i class="ti ti-alert-circle text-warning" style="font-size: 2rem;"></i>
                                <h3 class="mt-2 mb-1" id="confirmUnassignedItems">{{ $unassignedItems->count() }}</h3>
                                <p class="text-muted small mb-0">Partidas Individuales</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($unassignedItems->count() > 0)
                <div class="alert alert-warning" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>
                    <strong>Importante:</strong> Tienes <strong id="confirmUnassignedItemsText">{{ $unassignedItems->count() }}</strong> 
                    partidas sin agrupar que se cotizarán de manera individual. Esto puede generar múltiples órdenes de compra.
                </div>
                @endif

                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-2">
                            <i class="ti ti-info-circle me-2"></i>
                            ¿Qué sucederá a continuación?
                        </h6>
                        <ol class="mb-0 ps-3">
                            <li class="mb-2">
                                <strong>Selección de Proveedores:</strong> Para cada grupo y partida individual, 
                                seleccionarás mínimo 2 proveedores para solicitar cotizaciones.
                            </li>
                            <li class="mb-2">
                                <strong>Envío de RFQs:</strong> Se enviarán automáticamente las solicitudes 
                                de cotización a los proveedores seleccionados.
                            </li>
                            <li class="mb-0">
                                <strong>Recepción y Comparación:</strong> Los proveedores responderán con sus 
                                cotizaciones y podrás compararlas para seleccionar las mejores opciones.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmUnderstand">
                    <label class="form-check-label" for="confirmUnderstand">
                        Entiendo la estrategia y deseo continuar
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmStrategyBtn" disabled>
                    <i class="ti ti-arrow-right"></i> Continuar a Proveedores
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
$(document).ready(function() {
    // Habilitar botón solo si acepta el checkbox
    $('#confirmUnderstand').change(function() {
        $('#confirmStrategyBtn').prop('disabled', !this.checked);
    });

    // Actualizar contadores al abrir modal
    $('#confirmStrategyModal').on('show.bs.modal', function() {
        // Actualizar desde el resumen de la página
        $('#confirmGroupsCount').text($('#groupsCount').text());
        $('#confirmAssignedItems').text($('#assignedItemsCount').text());
        $('#confirmUnassignedItems').text($('#unassignedItemsCount').text());
        $('#confirmUnassignedItemsText').text($('#unassignedItemsCount').text());
    });

    // Limpiar checkbox al cerrar modal
    $('#confirmStrategyModal').on('hidden.bs.modal', function() {
        $('#confirmUnderstand').prop('checked', false);
        $('#confirmStrategyBtn').prop('disabled', true);
    });
});
</script>
@endpush
@endonce