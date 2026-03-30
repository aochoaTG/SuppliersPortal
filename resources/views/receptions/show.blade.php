@extends('layouts.zircos')

@section('title', 'Recepción ' . $reception->folio)
@section('page.title', 'Comprobante de Recepción')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('receptions.overview') }}">Recepciones</a></li>
    @if($reception->receivable)
        <li class="breadcrumb-item">
            @if($reception->receivable instanceof \App\Models\PurchaseOrder)
                <a href="{{ route('purchase-orders.show', $reception->receivable) }}">
                    {{ $reception->receivable->folio }}
                </a>
            @else
                <a href="{{ route('direct-purchase-orders.show', $reception->receivable) }}">
                    {{ $reception->receivable->folio }}
                </a>
            @endif
        </li>
    @endif
    <li class="breadcrumb-item active">{{ $reception->folio }}</li>
@endsection

@push('styles')
<style>
    @media print {
        .d-print-none { display: none !important; }
        .card { box-shadow: none !important; }
        body { background: white !important; }
        .sidenav-menu, .topbar, footer { display: none !important; }
        .page-content { padding: 0 !important; margin: 0 !important; }
        .content-page { margin: 0 !important; }
        .nonconf-row { background-color: #fff3cd !important; -webkit-print-color-adjust: exact; }
    }
    .photo-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform .15s;
    }
    .photo-thumb:hover { transform: scale(1.08); }
</style>
@endpush

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
                <div class="d-flex gap-2">
                    <a href="{{ route('receptions.overview') }}" class="btn btn-sm btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Recepciones
                    </a>
                    @if($reception->receivable)
                        @if($reception->receivable instanceof \App\Models\PurchaseOrder)
                            <a href="{{ route('purchase-orders.show', $reception->receivable) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-file-description me-1"></i>Ver OC
                            </a>
                        @else
                            <a href="{{ route('direct-purchase-orders.show', $reception->receivable) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="ti ti-file-description me-1"></i>Ver OCD
                            </a>
                        @endif
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $reception->getStatusBadgeClass() }} fs-13">
                        {{ $reception->getStatusLabel() }}
                    </span>
                    <button onclick="window.print();" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-printer me-1"></i>Imprimir
                    </button>
                </div>
            </div>

            <div class="card-body p-4">

                {{-- ── Encabezado ───────────────────────────────────────── --}}
                <div class="row mb-4 align-items-start">
                    <div class="col-6">
                        <p class="text-muted small text-uppercase fw-bold mb-1 tracking-wider">TotalGas México</p>
                        <h5 class="fw-bold mb-1">COMPROBANTE DE RECEPCIÓN</h5>
                        <h3 class="text-primary fw-bold mb-0">{{ $reception->folio }}</h3>
                    </div>
                    <div class="col-6 text-end text-muted small">
                        <div><strong>Fecha de Recepción:</strong>
                            {{ $reception->received_at->format('d/m/Y H:i') }}
                        </div>
                        <div><strong>Registrado por:</strong>
                            {{ $reception->receiver->name ?? '—' }}
                        </div>
                        <div><strong>Ubicación:</strong>
                            {{ $reception->receivingLocation->name ?? '—' }}
                        </div>
                        @if($reception->delivery_reference)
                            <div><strong>Ref. Remisión:</strong>
                                {{ $reception->delivery_reference }}
                            </div>
                        @endif
                        @if($reception->remission_path)
                            <div class="mt-1 d-print-none">
                                <a href="{{ route('receptions.remission.download', $reception) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Descargar archivo de remisión">
                                    <i class="ti ti-paperclip me-1"></i>Descargar Remisión
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <hr>

                {{-- ── Orden origen + notas ────────────────────────────── --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-2">
                            <i class="ti ti-file-text me-1"></i>Orden de Origen
                        </h6>
                        @if($reception->receivable)
                            <div class="fw-bold fs-15">{{ $reception->receivable->folio }}</div>
                            <div class="text-muted small mb-1">
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
                                <i class="ti ti-notes me-1"></i>Notas Generales
                            </h6>
                            <p class="text-muted small mb-0">{{ $reception->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- ── Resumen de conformidad ───────────────────────────── --}}
                @php
                    $totalItems   = $reception->items->count();
                    $confItems    = $reception->items->filter->isConforming()->count();
                    $noConfItems  = $reception->items->filter->isNonConforming()->count();
                @endphp
                @if($totalItems > 0)
                    <div class="d-flex gap-3 mb-4">
                        <div class="px-3 py-2 rounded border border-success bg-success bg-opacity-10 text-center">
                            <div class="fw-bold text-success fs-18">{{ $confItems }}</div>
                            <div class="small text-success">
                                <i class="ti ti-circle-check me-1"></i>Conformes
                            </div>
                        </div>
                        @if($noConfItems > 0)
                            <div class="px-3 py-2 rounded border border-danger bg-danger bg-opacity-10 text-center">
                                <div class="fw-bold text-danger fs-18">{{ $noConfItems }}</div>
                                <div class="small text-danger">
                                    <i class="ti ti-circle-x me-1"></i>No Conformes
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ── Tabla de partidas ───────────────────────────────── --}}
                <h6 class="text-primary text-uppercase fw-bold fs-12 mb-2">
                    <i class="ti ti-list-check me-1"></i>Partidas Recibidas
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:36px">#</th>
                                <th>Descripción</th>
                                <th class="text-center" style="width:100px">Cant. Recibida</th>
                                <th class="text-center" style="width:130px">Conformidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reception->items as $index => $item)
                                {{-- Fila principal --}}
                                <tr class="{{ $item->isNonConforming() ? 'table-warning' : '' }}">
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold">
                                            {{ $item->receivableItem->description ?? '—' }}
                                        </span>
                                        {{-- Fotos (miniaturas) --}}
                                        @if($item->hasPhotos())
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                @foreach($item->photos as $photo)
                                                    <a href="{{ Storage::url($photo) }}" target="_blank">
                                                        <img src="{{ Storage::url($photo) }}"
                                                             class="photo-thumb"
                                                             alt="Evidencia fotográfica">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold">
                                        {{ number_format($item->quantity_received, 3) }}
                                    </td>
                                    <td class="text-center">
                                        @if($item->isConforming())
                                            <span class="badge bg-success">
                                                <i class="ti ti-circle-check me-1"></i>Conforme
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="ti ti-circle-x me-1"></i>No Conforme
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Fila de detalle de no conformidad --}}
                                @if($item->isNonConforming())
                                    <tr class="nonconf-row bg-danger-subtle">
                                        <td></td>
                                        <td colspan="3" class="py-2 ps-3">
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <span class="small text-muted fw-bold text-uppercase">Tipo:</span>
                                                    <div class="small fw-semibold text-danger">
                                                        {{ $item->getNonconformityLabel() }}
                                                    </div>
                                                </div>
                                                @if($item->nonconformity_notes)
                                                    <div class="col-md-9">
                                                        <span class="small text-muted fw-bold text-uppercase">Descripción:</span>
                                                        <div class="small">{{ $item->nonconformity_notes }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>{{-- /card-body --}}
        </div>
    </div>
</div>
@endsection
