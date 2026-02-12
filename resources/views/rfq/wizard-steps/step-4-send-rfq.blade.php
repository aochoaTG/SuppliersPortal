{{-- ID único para detectar el paso --}}
<div id="sendRfqStep">
    {{-- Información de la Requisición --}}
    <div class="alert alert-primary mb-3">
        <div class="row">
            <div class="col-md-3">
                <small class="text-muted d-block">Folio</small>
                <strong>{{ $requisition->folio }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">RFQs Creadas</small>
                <span class="badge bg-primary">{{ $requisition->rfqs->count() }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Borradores</small>
                <span class="badge bg-warning" id="draftCountBadge">{{ $requisition->rfqs->where('status', 'DRAFT')->count() }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Enviadas</small>
                <span class="badge bg-success" id="sentCountBadge">{{ $requisition->rfqs->where('status', 'SENT')->count() }}</span>
            </div>
        </div>
    </div>

    {{-- Instrucciones --}}
    <div class="alert alert-info mb-3">
        <i class="ti ti-info-circle me-2"></i>
        <strong>Instrucciones:</strong> Revisa las solicitudes creadas y envíalas a los proveedores. 
        Puedes enviarlas individualmente o todas a la vez.
    </div>

    {{-- Tabla de RFQs --}}
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="ti ti-list me-2"></i>Solicitudes de Cotización
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="rfqsWizardTable" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="12%">Folio</th>
                            <th width="18%">Grupo/Partida</th>
                            <th width="15%">Proveedores</th>
                            <th width="12%">Estado</th>
                            <th width="12%">Fecha Límite</th>
                            <th width="12%">Días Restantes</th>
                            <th width="19%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables carga aquí --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
.status-badge {
    font-size: 0.85rem;
    padding: 0.35rem 0.65rem;
    font-weight: 500;
}
.days-remaining {
    font-weight: 600;
}
</style>
@endpush