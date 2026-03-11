@extends('layouts.zircos')

@section('title', 'Detalle de Orden de Compra: ' . $purchaseOrder->folio)

@section('page.title', 'Detalle de Orden de Compra')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
    <li class="breadcrumb-item active">{{ $purchaseOrder->folio }}</li>
@endsection

@push('styles')
<style>
    @media print {
        .d-print-none { display: none !important; }
        .card { box-shadow: none !important; border: 0 !important; }
        body { background-color: white !important; }
        .sidenav-menu, .topbar { display: none !important; }
        .page-content { padding: 0 !important; margin: 0 !important; }
        .content-page { margin: 0 !important; }
        footer { display: none !important; }
    }
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .info-value {
        font-size: 0.875rem;
        color: #212529;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">

            {{-- Barra de acciones (no imprimible) --}}
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center d-print-none">
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Regresar al Listado
                </a>
                <div>
                    <button onclick="window.print();" class="btn btn-sm btn-primary">
                        <i class="ti ti-printer me-1"></i>Imprimir OC
                    </button>
                </div>
            </div>

            <div class="card-body p-5" id="printable-area">

                {{-- ═══════════════════════════════════════════════
                     ENCABEZADO: Logo + Datos de la OC
                ═══════════════════════════════════════════════ --}}
                <div class="row mb-4 align-items-start">
                    <div class="col-6">
                        <img src="{{ asset('images/logos/logo_TotalGas_hor.png') }}" alt="TotalGas" height="50" class="mb-3">
                        <h6 class="text-muted fw-bold mb-1">TOTALGAS MÉXICO</h6>
                        <p class="text-muted small mb-0">
                            RFC: TGM123456789<br>
                            Av. Tecnológico #1234<br>
                            Ciudad Juárez, Chihuahua.
                        </p>
                    </div>
                    <div class="col-6 text-end">
                        <h3 class="text-primary fw-bold mb-1">ORDEN DE COMPRA</h3>
                        <h4 class="text-dark mb-3">{{ $purchaseOrder->folio }}</h4>
                        <div class="text-muted small">
                            <strong>Fecha de Emisión:</strong> {{ $purchaseOrder->created_at->format('d/m/Y H:i') }}<br>
                            @if($purchaseOrder->approved_at)
                                <strong>Fecha de Aprobación:</strong> {{ $purchaseOrder->approved_at->format('d/m/Y H:i') }}<br>
                            @endif
                            <strong>Estado:</strong>
                            <span class="badge bg-{{ $purchaseOrder->getStatusBadgeClass() }}">
                                {{ $purchaseOrder->getStatusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- ═══════════════════════════════════════════════
                     SECCIÓN PRINCIPAL: Proveedor | Requisición | Entrega
                ═══════════════════════════════════════════════ --}}
                <div class="row mb-4">

                    {{-- Columna 1: Proveedor --}}
                    <div class="col-md-4 border-end pe-4">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">
                            <i class="ti ti-building-store me-1"></i>Datos del Proveedor
                        </h6>
                        <h5 class="fw-bold mb-2">{{ $purchaseOrder->supplier->company_name }}</h5>
                        <div class="mb-1">
                            <span class="info-label">RFC</span>
                            <div class="info-value">{{ $purchaseOrder->supplier->rfc ?? '—' }}</div>
                        </div>
                        <div class="mb-1">
                            <span class="info-label">Contacto</span>
                            <div class="info-value">{{ $purchaseOrder->supplier->contact_name ?? '—' }}</div>
                        </div>
                        <div class="mb-0">
                            <span class="info-label">Email</span>
                            <div class="info-value">{{ $purchaseOrder->supplier->email }}</div>
                        </div>
                    </div>

                    {{-- Columna 2: Requisición de Origen --}}
                    <div class="col-md-4 border-end px-4">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">
                            <i class="ti ti-clipboard-list me-1"></i>Requisición de Origen
                        </h6>
                        <div class="mb-1">
                            <span class="info-label">Folio Requisición</span>
                            <div class="info-value fw-bold">{{ $purchaseOrder->requisition->folio }}</div>
                        </div>
                        @if($purchaseOrder->requisition->company)
                        <div class="mb-1">
                            <span class="info-label">Empresa</span>
                            <div class="info-value">{{ $purchaseOrder->requisition->company->name }}</div>
                        </div>
                        @endif
                        @if($purchaseOrder->requisition->department)
                        <div class="mb-1">
                            <span class="info-label">Departamento</span>
                            <div class="info-value">{{ $purchaseOrder->requisition->department->name }}</div>
                        </div>
                        @endif
                        @if($purchaseOrder->requisition->costCenter)
                        <div class="mb-1">
                            <span class="info-label">Centro de Costos</span>
                            <div class="info-value">{{ $purchaseOrder->requisition->costCenter->name }}</div>
                        </div>
                        @endif
                        @if($purchaseOrder->requisition->required_date)
                        <div class="mb-0">
                            <span class="info-label">Fecha Requerida</span>
                            <div class="info-value fw-semibold text-danger">
                                <i class="ti ti-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($purchaseOrder->requisition->required_date)->format('d/m/Y') }}
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Columna 3: Entrega y Autorización --}}
                    <div class="col-md-4 ps-4">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">
                            <i class="ti ti-truck-delivery me-1"></i>Entrega y Autorización
                        </h6>
                        @if($purchaseOrder->requisition->receivingLocation)
                        <div class="mb-2 p-2 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25">
                            <span class="info-label"><i class="ti ti-map-pin me-1 text-warning"></i>Punto de Entrega</span>
                            <div class="info-value fw-semibold">
                                {{ $purchaseOrder->requisition->receivingLocation->code }}
                                — {{ $purchaseOrder->requisition->receivingLocation->name }}
                            </div>
                        </div>
                        @endif
                        <div class="mb-1">
                            <span class="info-label">Solicitado por</span>
                            <div class="info-value">
                                {{ $purchaseOrder->requisition->requester->name
                                   ?? $purchaseOrder->requisition->creator->name
                                   ?? '—' }}
                            </div>
                        </div>
                        <div class="mb-1">
                            <span class="info-label">Autorizado / Generado por</span>
                            <div class="info-value">{{ $purchaseOrder->creator->name ?? '—' }}</div>
                        </div>
                        @if($purchaseOrder->requisition->description)
                        <div class="mb-0">
                            <span class="info-label">Descripción de la Requisición</span>
                            <div class="info-value small text-muted">{{ $purchaseOrder->requisition->description }}</div>
                        </div>
                        @endif
                    </div>

                </div>

                {{-- ═══════════════════════════════════════════════
                     TABLA DE PARTIDAS
                ═══════════════════════════════════════════════ --}}
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-centered mb-0">
                        <thead class="table-light">
                            <tr class="text-dark fw-bold">
                                <th style="width: 40px">#</th>
                                <th>Descripción del Producto / Servicio</th>
                                <th class="text-center" style="width: 90px">Cant.</th>
                                <th class="text-center" style="width: 70px">Unidad</th>
                                <th class="text-end" style="width: 140px">P. Unitario</th>
                                <th class="text-end" style="width: 60px">IVA</th>
                                <th class="text-end" style="width: 140px">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $item)
                                @php
                                    $reqItem = $item->requisitionItem;
                                    $ivaRate = $item->subtotal > 0
                                        ? round(($item->iva_amount / $item->subtotal) * 100)
                                        : 16;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $item->description }}</span>
                                        @if($reqItem && $reqItem->notes)
                                            <small class="text-muted d-block fst-italic">{{ $reqItem->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-center text-muted small">{{ $reqItem->unit ?? '—' }}</td>
                                    <td class="text-end">
                                        {{ $purchaseOrder->currency === 'USD' ? 'US$' : '$' }}{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-end text-muted small">{{ $ivaRate }}%</td>
                                    <td class="text-end fw-bold text-dark">
                                        {{ $purchaseOrder->currency === 'USD' ? 'US$' : '$' }}{{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" rowspan="3" class="border-0 align-top">
                                    <div class="bg-light p-3 rounded mt-2">
                                        <h6 class="fw-bold text-dark mb-2">Términos y Condiciones:</h6>
                                        <ul class="small text-muted mb-0 ps-3">
                                            <li>
                                                <strong>Condiciones de Pago:</strong>
                                                {{ $purchaseOrder->payment_terms ?? '—' }}
                                            </li>
                                            <li>
                                                <strong>Tiempo de Entrega:</strong>
                                                {{ $purchaseOrder->estimated_delivery_days
                                                    ? $purchaseOrder->estimated_delivery_days . ' días hábiles'
                                                    : '—' }}
                                            </li>
                                            <li>
                                                <strong>Moneda:</strong>
                                                {{ $purchaseOrder->currency ?? 'MXN' }}
                                            </li>
                                            @if($purchaseOrder->requisition->receivingLocation)
                                            <li>
                                                <strong>Entregar en:</strong>
                                                {{ $purchaseOrder->requisition->receivingLocation->code }}
                                                — {{ $purchaseOrder->requisition->receivingLocation->name }}
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                                <td colspan="2" class="text-end border-0 fw-bold text-muted">Subtotal:</td>
                                <td class="text-end border-0 fw-bold">
                                    {{ $purchaseOrder->currency === 'USD' ? 'US$' : '$' }}{{ number_format($purchaseOrder->subtotal, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end border-0 fw-bold text-muted small">I.V.A:</td>
                                <td class="text-end border-0 text-muted small">
                                    {{ $purchaseOrder->currency === 'USD' ? 'US$' : '$' }}{{ number_format($purchaseOrder->iva_amount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end border-0 bg-soft-primary">
                                    <h5 class="fw-bold text-primary mb-0">TOTAL:</h5>
                                </td>
                                <td class="text-end border-0 bg-soft-primary">
                                    <h5 class="fw-bold text-primary mb-0">
                                        {{ $purchaseOrder->currency === 'USD' ? 'US$' : '$' }}{{ number_format($purchaseOrder->total, 2) }}
                                    </h5>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- ═══════════════════════════════════════════════
                     ÁREA DE FIRMAS
                ═══════════════════════════════════════════════ --}}
                <div class="row mt-5 pt-5 text-center">
                    <div class="col-4">
                        <div class="border-top border-dark pt-2 mx-4">
                            <small class="text-muted d-block">Solicitado por</small>
                            <span class="fw-bold">
                                {{ $purchaseOrder->requisition->requester->name
                                   ?? $purchaseOrder->requisition->creator->name
                                   ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex justify-content-center mb-2">
                            <div class="bg-light p-2 border" style="width: 80px; height: 80px; font-size: 10px;">
                                [QR DIGITAL SEAL]
                            </div>
                        </div>
                        <small class="text-muted">Documento validado por Portal de Proveedores</small>
                    </div>
                    <div class="col-4">
                        <div class="border-top border-dark pt-2 mx-4">
                            <small class="text-muted d-block">Autorizado por</small>
                            <span class="fw-bold text-primary">{{ $purchaseOrder->creator->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>

            </div>{{-- /card-body --}}
        </div>
    </div>
</div>
@endsection
