@extends('layouts.zircos')

@section('title', 'Recepción ' . $reception->folio)
@section('page.title', 'Detalle de Recepción')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('receptions.pending') }}">Recepciones</a></li>
    <li class="breadcrumb-item active">{{ $reception->folio }}</li>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="ti ti-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">

            {{-- Barra de acciones --}}
            <div class="card-header d-flex justify-content-between align-items-center d-print-none">
                <a href="{{ route('receptions.pending') }}" class="btn btn-sm btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Regresar
                </a>
                <div>
                    <span class="badge bg-{{ $reception->getStatusBadgeClass() }} fs-13 me-2">
                        {{ $reception->getStatusLabel() }}
                    </span>
                    <button onclick="window.print();" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-printer me-1"></i>Imprimir
                    </button>
                </div>
            </div>

            <div class="card-body p-4">

                {{-- Encabezado --}}
                <div class="row mb-4 align-items-start">
                    <div class="col-6">
                        <h5 class="fw-bold mb-1">COMPROBANTE DE RECEPCIÓN</h5>
                        <h3 class="text-primary fw-bold">{{ $reception->folio }}</h3>
                    </div>
                    <div class="col-6 text-end text-muted small">
                        <div><strong>Fecha de Recepción:</strong> {{ $reception->received_at->format('d/m/Y H:i') }}</div>
                        <div><strong>Registrado por:</strong> {{ $reception->receiver->name ?? '—' }}</div>
                        <div><strong>Ubicación:</strong> {{ $reception->receivingLocation->name ?? '—' }}</div>
                        @if($reception->delivery_reference)
                            <div><strong>Ref. Proveedor:</strong> {{ $reception->delivery_reference }}</div>
                        @endif
                    </div>
                </div>

                <hr>

                {{-- Orden origen --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-2">
                            <i class="ti ti-file-text me-1"></i>Orden de Origen
                        </h6>
                        @if($reception->receivable)
                            <div class="fw-bold">{{ $reception->receivable->folio }}</div>
                            <div class="text-muted small">
                                {{ $reception->receivable instanceof \App\Models\PurchaseOrder
                                    ? 'Orden de Compra Estándar'
                                    : 'Orden de Compra Directa' }}
                            </div>
                            <div>{{ $reception->receivable->supplier->company_name ?? '—' }}</div>
                        @endif
                    </div>
                    @if($reception->notes)
                    <div class="col-md-6">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-2">
                            <i class="ti ti-notes me-1"></i>Notas
                        </h6>
                        <p class="text-muted small mb-0">{{ $reception->notes }}</p>
                    </div>
                    @endif
                </div>

                {{-- Tabla de ítems recibidos --}}
                <h6 class="text-primary text-uppercase fw-bold fs-12 mb-2">
                    <i class="ti ti-list-check me-1"></i>Partidas Recibidas
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Descripción</th>
                                <th class="text-center">Recibido</th>
                                <th class="text-center">Rechazado</th>
                                <th class="text-center">Aceptado</th>
                                <th>Motivo Rechazo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reception->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->receivableItem->description ?? '—' }}</td>
                                <td class="text-center">{{ number_format($item->quantity_received, 2) }}</td>
                                <td class="text-center {{ $item->hasRejections() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ number_format($item->quantity_rejected, 2) }}
                                </td>
                                <td class="text-center fw-semibold text-success">
                                    {{ number_format($item->quantity_accepted, 2) }}
                                </td>
                                <td class="text-muted small">{{ $item->rejection_reason ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
