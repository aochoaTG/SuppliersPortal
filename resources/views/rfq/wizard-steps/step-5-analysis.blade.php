{{-- ID único para detectar el paso --}}
<div id="analysisStep">
    <div class="row mb-4">
        <div class="col-md-8">
            <h5 class="text-primary fw-bold">
                <i class="ti ti-chart-bar me-2"></i>Seguimiento y Análisis de Cotizaciones
            </h5>
            <p class="text-muted small">
                Monitorea en tiempo real las respuestas de los proveedores para esta requisición.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex justify-content-end gap-2">
                <div class="text-center px-3 border-end">
                    <h4 class="mb-0 fw-bold" id="totalRfqsStep5">0</h4>
                    <small class="text-muted">Total RFQs</small>
                </div>
                <div class="text-center px-3">
                    <h4 class="mb-0 fw-bold text-success" id="completedRfqsStep5">0</h4>
                    <small class="text-muted">Con Respuestas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="rfq-analysis-table" class="table table-hover nowrap w-100 border">
            <thead class="table-light">
                <tr>
                    <th>Folio RFQ</th>
                    <th>Grupo / Título</th>
                    <th width="20%">Progreso de Respuestas</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{-- Cargado vía AJAX --}}
            </tbody>
        </table>
    </div>
</div>

{{-- Modal para Detalles (AJAX) --}}
<div class="modal fade" id="infoAjaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="modal-loader-content">
            <div class="p-5 text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Cargando información detallada...</p>
            </div>
        </div>
    </div>
</div>