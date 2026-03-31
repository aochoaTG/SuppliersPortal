@extends('layouts.zircos')

@section('title', 'Entregas — Portal de Proveedores')
@section('page.title', 'Mis Entregas')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('supplier.dashboard') }}">Portal</a></li>
    <li class="breadcrumb-item active">Entregas</li>
@endsection

@section('content')
<div class="row g-3">

    {{-- Alertas de sesión --}}
    @if(session('success'))
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- Tabs: OC Estándar / OC Directas --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tabRegular" role="tab">
                            <i class="ti ti-file-invoice me-1"></i>
                            OC Estándar
                            <span class="badge bg-secondary ms-1">{{ $purchaseOrders->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabDirect" role="tab">
                            <i class="ti ti-file-invoice me-1"></i>
                            OC Directas
                            <span class="badge bg-secondary ms-1">{{ $directOrders->count() }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    {{-- Tab OC Estándar --}}
                    <div class="tab-pane show active" id="tabRegular" role="tabpanel">
                        @if($purchaseOrders->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                No tienes órdenes de compra estándar pendientes de entrega.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Folio</th>
                                            <th>Punto de Entrega</th>
                                            <th>Total</th>
                                            <th>Estatus</th>
                                            <th>Fecha Emisión</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrders as $po)
                                        <tr>
                                            <td><strong>{{ $po->folio }}</strong></td>
                                            <td>
                                                @if($po->receivingLocation)
                                                    <span class="badge bg-soft-info text-info">{{ $po->receivingLocation->code }}</span>
                                                    {{ $po->receivingLocation->name }}
                                                @else
                                                    <span class="text-muted">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td>${{ number_format($po->total, 2) }} {{ $po->currency }}</td>
                                            <td>
                                                <span class="badge bg-{{ $po->getStatusBadgeClass() }}">
                                                    {{ $po->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $po->issued_at?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-center">
                                                @if($po->canReceiveSupplierDelivery())
                                                    <a href="{{ route('supplier.deliveries.create', ['type' => 'standard', 'id' => $po->id]) }}"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="ti ti-truck-delivery me-1"></i> Registrar Entrega
                                                    </a>
                                                @elseif($po->isDeliveredPendingReception())
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ti ti-clock me-1"></i>
                                                        Esperando captura de estación
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Tab OC Directas --}}
                    <div class="tab-pane" id="tabDirect" role="tabpanel">
                        @if($directOrders->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                No tienes órdenes de compra directas pendientes de entrega.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Folio</th>
                                            <th>Punto de Entrega</th>
                                            <th>Total</th>
                                            <th>Estatus</th>
                                            <th>Fecha Emisión</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($directOrders as $ocd)
                                        <tr>
                                            <td><strong>{{ $ocd->folio }}</strong></td>
                                            <td>
                                                @if($ocd->receivingLocation)
                                                    <span class="badge bg-soft-info text-info">{{ $ocd->receivingLocation->code }}</span>
                                                    {{ $ocd->receivingLocation->name }}
                                                @else
                                                    <span class="text-muted">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td>${{ number_format($ocd->total, 2) }} {{ $ocd->currency }}</td>
                                            <td>
                                                <span class="badge bg-{{ $ocd->getStatusBadgeClass() }}">
                                                    {{ $ocd->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $ocd->issued_at?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-center">
                                                @if($ocd->canReceiveSupplierDelivery())
                                                    <a href="{{ route('supplier.deliveries.create', ['type' => 'direct', 'id' => $ocd->id]) }}"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="ti ti-truck-delivery me-1"></i> Registrar Entrega
                                                    </a>
                                                @elseif($ocd->isDeliveredPendingReception())
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ti ti-clock me-1"></i>
                                                        Esperando captura de estación
                                                    </span>
                                                @endif
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
        </div>
    </div>
</div>
@endsection
