@extends('layouts.zircos')

@section('title', 'Requisición ' . $requisition->folio)
@section('page.title', 'Requisición ' . $requisition->folio)

@section('content')
    @php
        // Map sencillo para badge de estatus (ajústalo a tus valores reales)
        $statusClass =
            [
                'draft' => 'badge bg-secondary',
                'pending' => 'badge bg-warning',
                'approved' => 'badge bg-success',
                'rejected' => 'badge bg-danger',
                'closed' => 'badge bg-dark',
            ][$requisition->status] ?? 'badge bg-secondary';

        $totalGeneral = $requisition->items->sum('line_total');
    @endphp

    {{-- <div class="d-flex gap-2">
        @if ($requisition->status !== 'approved')
            <form method="POST" action="{{ route('requisitions.approve', $requisition) }}">
                @csrf
                <button class="btn btn-success">
                    <i class="ti ti-check"></i> Aprobar
                </button>
            </form>
        @endif

        @if ($requisition->status !== 'cancelled')
            <form method="POST" action="{{ route('requisitions.cancel', $requisition) }}">
                @csrf
                <button class="btn btn-outline-danger">
                    <i class="ti ti-x"></i> Cancelar
                </button>
            </form>
        @endif

        @if ($requisition->status !== 'rejected')
            <form method="POST" action="{{ route('requisitions.reject', $requisition) }}">
                @csrf
                <button class="btn btn-outline-warning">
                    <i class="ti ti-ban"></i> Rechazar
                </button>
            </form>
        @endif
    </div> --}}

    {{-- Flash Alerts globales (ponlo en tu layout si no lo tienes) --}}
    @if (session('success'))
        <div class="alert alert-success mt-3"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning mt-3"><i class="ti ti-alert-triangle"></i> {{ session('warning') }}</div>
    @endif
    @if (session('danger'))
        <div class="alert alert-danger mt-3"><i class="ti ti-octagon"></i> {{ session('danger') }}</div>
    @endif
    @if (session('info'))
        <div class="alert alert-info mt-3"><i class="ti ti-info-circle"></i> {{ session('info') }}</div>
    @endif

    {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConsume">
        <i class="ti ti-credit-card"></i> Registrar consumo
    </button>

    <div class="modal fade" id="modalConsume" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('requisitions.consume', $requisition) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-credit-card"></i> Consumo presupuestal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Monto</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fuente</label>
                        <select name="source" class="form-select" required>
                            <option value="PO">Orden de compra</option>
                            <option value="GR">Recepción</option>
                            <option value="INVOICE">Factura</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nota (opcional)</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i>
                        El consumo reduce el disponible de forma definitiva.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary"><i class="ti ti-send"></i> Registrar</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div> --}}

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="d-flex align-items-center mb-0 gap-2">
                <i class="ti ti-file-text"></i>
                <span>Requisición</span>
                <span class="text-muted">#{{ $requisition->folio }}</span>
            </h5>

            <div class="d-flex align-items-center gap-2">
                <span class="{{ $statusClass }}">
                    <i class="ti ti-clipboard-check me-1"></i>{{ $requisition->statusLabel() }}
                </span>
            </div>
        </div>

        <div class="card-body">
            {{-- Meta: dos columnas, etiquetas con icono discreto --}}
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-building-bank me-1"></i> Compañía
                        </dt>
                        <dd class="col-sm-7 fw-semibold">
                            {{ $requisition->company?->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-coin me-1"></i> Moneda
                        </dt>
                        <dd class="col-sm-7">
                            <span class="fw-semibold">{{ $requisition->currency_code }}</span>
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-calendar-stats me-1"></i> Año fiscal
                        </dt>
                        <dd class="col-sm-7">
                            <span class="fw-semibold">{{ $requisition->fiscal_year }}</span>
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-calendar-event me-1"></i> Fecha creación
                        </dt>
                        <dd class="col-sm-7">
                            <span class="fw-semibold">{{ $requisition->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-user-search me-1"></i> Revisado por
                        </dt>
                        <dd class="col-sm-7 fw-semibold">
                            {{ $requisition->reviewer?->name ?? '—' }}
                        </dd>
                    </dl>
                </div>

                <div class="col-12 col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-hierarchy-3 me-1"></i> Centro de costo
                        </dt>
                        <dd class="col-sm-7 fw-semibold">
                            {{ $requisition->costCenter?->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-building me-1"></i> Departamento
                        </dt>
                        <dd class="col-sm-7 fw-semibold">
                            {{ $requisition->department?->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-user-check me-1"></i> Solicitado por
                        </dt>
                        <dd class="col-sm-7 fw-semibold">
                            {{ $requisition->requester?->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-calendar-time me-1"></i> Última actualización
                        </dt>
                        <dd class="col-sm-7">
                            <span class="fw-semibold">{{ $requisition->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </dd>

                        <dt class="col-sm-5 text-muted">
                            <i class="ti ti-user-check me-1"></i> Aprobado por
                        </dt>
                        <dd class="col-sm-7 fw-semibold">
                            {{ $requisition->approver?->name ?? '—' }}
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Separador visual sutil --}}
            <hr class="my-4">

            <div class="d-flex align-items-center justify-content-between">
                <h6 class="d-flex align-items-center mb-2 gap-2">
                    <i class="ti ti-list-details"></i>
                    <span>Partidas</span>
                </h6>
                <div class="text-muted small">
                    <i class="ti ti-sum"></i>
                    Total: <span class="fw-semibold">${{ number_format($totalGeneral, 2) }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-sm table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">#</th>
                            <th>Descripción</th>
                            <th class="text-nowrap text-end">Cantidad</th>
                            <th class="text-nowrap">Unidad</th>
                            <th class="text-nowrap text-end">P.U.</th>
                            <th class="text-nowrap text-end">IVA %</th>
                            <th class="text-nowrap text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requisition->items as $it)
                            <tr>
                                <td class="text-muted">{{ $it->line_number }}</td>
                                <td class="fw-medium">{{ $it->description }}</td>
                                <td class="text-nowrap text-end">{{ number_format($it->quantity, 3) }}</td>
                                <td class="text-nowrap">{{ $it->unit }}</td>
                                <td class="text-nowrap text-end">${{ number_format($it->unit_price, 2) }}</td>
                                <td class="text-nowrap text-end">{{ number_format($it->tax_rate, 2) }}</td>
                                <td class="fw-semibold text-nowrap text-end">${{ number_format($it->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted text-center">
                                    <i class="ti ti-inbox"></i> Sin partidas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($requisition->items->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total</th>
                                <th class="text-nowrap text-end">${{ number_format($totalGeneral, 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
