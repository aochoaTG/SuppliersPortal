@extends('layouts.zircos')

@section('title', 'Recepciones Pendientes')
@section('page.title', 'Recepciones Pendientes')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Recepciones Pendientes</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        {{-- Alertas de sesión --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="ti ti-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-package-import me-2"></i>Órdenes Pendientes de Recepción</h5>
                <span class="badge bg-warning fs-12">
                    {{ $purchaseOrders->count() + $directOrders->count() }} pendientes
                </span>
            </div>
            <div class="card-body">

                {{-- Tabs OC Regular / OCD --}}
                <ul class="nav nav-tabs mb-3" id="receptionTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-regular">
                            OC Estándar
                            <span class="badge bg-secondary ms-1">{{ $purchaseOrders->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-direct">
                            OC Directas
                            <span class="badge bg-secondary ms-1">{{ $directOrders->count() }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- OC Estándar --}}
                    <div class="tab-pane fade show active" id="tab-regular">
                        @if($purchaseOrders->isEmpty())
                            <p class="text-muted text-center py-4">
                                <i class="ti ti-mood-happy fs-24 d-block mb-2"></i>
                                No hay órdenes de compra pendientes de recepción.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Folio</th>
                                            <th>Proveedor</th>
                                            <th>Punto de Entrega</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Emitida</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrders as $po)
                                        <tr>
                                            <td class="fw-bold">{{ $po->folio }}</td>
                                            <td>{{ $po->supplier->company_name ?? '—' }}</td>
                                            <td>
                                                @if($po->receivingLocation)
                                                    <span class="badge bg-soft-info text-info">
                                                        {{ $po->receivingLocation->code }}
                                                    </span>
                                                    {{ $po->receivingLocation->name }}
                                                @else
                                                    <span class="text-danger">Sin locación</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-primary">
                                                ${{ number_format($po->total, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $po->getStatusBadgeClass() }}">
                                                    {{ $po->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $po->issued_at?->format('d/m/Y') ?? $po->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('receptions.create', $po) }}"
                                                   class="btn btn-sm btn-success">
                                                    <i class="ti ti-package-import me-1"></i>Recibir
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- OC Directas --}}
                    <div class="tab-pane fade" id="tab-direct">
                        @if($directOrders->isEmpty())
                            <p class="text-muted text-center py-4">
                                <i class="ti ti-mood-happy fs-24 d-block mb-2"></i>
                                No hay órdenes directas pendientes de recepción.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Folio</th>
                                            <th>Proveedor</th>
                                            <th>Punto de Entrega</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Emitida</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($directOrders as $ocd)
                                        <tr>
                                            <td class="fw-bold">{{ $ocd->folio }}</td>
                                            <td>{{ $ocd->supplier->company_name ?? '—' }}</td>
                                            <td>
                                                @if($ocd->receivingLocation)
                                                    <span class="badge bg-soft-info text-info">
                                                        {{ $ocd->receivingLocation->code }}
                                                    </span>
                                                    {{ $ocd->receivingLocation->name }}
                                                @else
                                                    <span class="text-danger">Sin locación</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-primary">
                                                ${{ number_format($ocd->total, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $ocd->getStatusBadgeClass() }}">
                                                    {{ $ocd->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $ocd->issued_at?->format('d/m/Y') ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('receptions.create-direct', $ocd) }}"
                                                   class="btn btn-sm btn-success">
                                                    <i class="ti ti-package-import me-1"></i>Recibir
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>{{-- /tab-content --}}
            </div>
        </div>

    </div>
</div>
@endsection
