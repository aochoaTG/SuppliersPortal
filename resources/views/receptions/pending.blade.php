@extends('layouts.zircos')

@section('title', 'Recepciones Pendientes')
@section('page.title', 'Recepciones Pendientes')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Recepciones Pendientes</li>
@endsection

@push('styles')
<style>
@keyframes pulse-urgente {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}
.urgente-pulse {
    animation: pulse-urgente 1.4s ease-in-out infinite;
}
</style>
@endpush

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
                    {{ $purchaseOrders->total() + $directOrders->total() }} pendientes
                </span>
            </div>
            <div class="card-body">

                {{-- Tabs OC Regular / OCD --}}
                <ul class="nav nav-tabs mb-3" id="receptionTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-regular">
                            OC Estándar
                            <span class="badge bg-secondary ms-1">{{ $purchaseOrders->total() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-direct">
                            OC Directas
                            <span class="badge bg-secondary ms-1">{{ $directOrders->total() }}</span>
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
                                            <th class="text-center">Días Restantes</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrders as $po)
                                        @php
                                            $isUrgent = $po->status === 'DELIVERED_PENDING_RECEPTION';
                                            $daysLeft = $isUrgent && $po->reception_deadline_at
                                                ? (int) now()->diffInWeekdays($po->reception_deadline_at, false)
                                                : null;
                                            $daysBadgeClass = match(true) {
                                                $daysLeft === null => '',
                                                $daysLeft >= 3     => 'bg-success',
                                                $daysLeft === 2    => 'bg-warning text-dark',
                                                default            => 'bg-danger',
                                            };
                                            $daysLabel = match(true) {
                                                $daysLeft === null => '—',
                                                $daysLeft > 0      => "{$daysLeft} día(s)",
                                                $daysLeft === 0    => 'Vence hoy',
                                                default            => 'Vencida',
                                            };
                                        @endphp
                                        <tr class="{{ $isUrgent ? 'table-danger' : '' }}">
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
                                                @if($isUrgent)
                                                    <span class="badge bg-danger urgente-pulse ms-1">URGENTE</span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                {{ $po->issued_at?->format('d/m/Y') ?? $po->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center">
                                                @if($daysLeft !== null)
                                                    <span class="badge {{ $daysBadgeClass }}">
                                                        <i class="ti ti-clock me-1"></i>{{ $daysLabel }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('receptions.create', $po) }}"
                                                   class="btn btn-sm {{ $isUrgent ? 'btn-danger' : 'btn-success' }}">
                                                    <i class="ti {{ $isUrgent ? 'ti-clock' : 'ti-package-import' }} me-1"></i>Recibir
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $purchaseOrders->links() }}
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
                                            <th class="text-center">Días Restantes</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($directOrders as $ocd)
                                        @php
                                            $isUrgent = $ocd->status === 'DELIVERED_PENDING_RECEPTION';
                                            $daysLeft = $isUrgent && $ocd->reception_deadline_at
                                                ? (int) now()->diffInWeekdays($ocd->reception_deadline_at, false)
                                                : null;
                                            $daysBadgeClass = match(true) {
                                                $daysLeft === null => '',
                                                $daysLeft >= 3     => 'bg-success',
                                                $daysLeft === 2    => 'bg-warning text-dark',
                                                default            => 'bg-danger',
                                            };
                                            $daysLabel = match(true) {
                                                $daysLeft === null => '—',
                                                $daysLeft > 0      => "{$daysLeft} día(s)",
                                                $daysLeft === 0    => 'Vence hoy',
                                                default            => 'Vencida',
                                            };
                                        @endphp
                                        <tr class="{{ $isUrgent ? 'table-danger' : '' }}">
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
                                                @if($isUrgent)
                                                    <span class="badge bg-danger urgente-pulse ms-1">URGENTE</span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                {{ $ocd->issued_at?->format('d/m/Y') ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                @if($daysLeft !== null)
                                                    <span class="badge {{ $daysBadgeClass }}">
                                                        <i class="ti ti-clock me-1"></i>{{ $daysLabel }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('receptions.create-direct', $ocd) }}"
                                                   class="btn btn-sm {{ $isUrgent ? 'btn-danger' : 'btn-success' }}">
                                                    <i class="ti {{ $isUrgent ? 'ti-clock' : 'ti-package-import' }} me-1"></i>Recibir
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $directOrders->links() }}
                        @endif
                    </div>

                </div>{{-- /tab-content --}}
            </div>
        </div>

    </div>
</div>
@endsection
