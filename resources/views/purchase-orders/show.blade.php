@extends('layouts.zircos')

@section('page.title', 'Detalle de Orden de Compra: ' . $purchaseOrder->folio)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
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
                {{-- HEADER DE LA OC --}}
                <div class="row mb-4">
                    <div class="col-6">
                        <img src="{{ asset('images/logos/logo_TotalGas_hor.png') }}" alt="TotalGas" height="50" class="mb-3">
                        <h6 class="text-muted fw-bold">TOTALGAS MÉXICO</h6>
                        <p class="text-muted small">
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
                            <strong>Estado:</strong> 
                            <span class="badge bg-{{ $purchaseOrder->status == 'OPEN' ? 'success' : 'dark' }}">
                                {{ $purchaseOrder->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- INFORMACIÓN DE PROVEEDOR Y ENTREGA --}}
                <div class="row mb-5">
                    <div class="col-6 border-end">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">Datos del Proveedor</h6>
                        <h5 class="fw-bold mb-1">{{ $purchaseOrder->supplier->company_name }}</h5>
                        <p class="text-muted small mb-0">
                            <strong>RFC:</strong> {{ $purchaseOrder->supplier->rfc ?? 'N/A' }}<br>
                            <strong>Contacto:</strong> {{ $purchaseOrder->supplier->contact_name ?? 'Sin contacto' }}<br>
                            <strong>Email:</strong> {{ $purchaseOrder->supplier->email }}
                        </p>
                    </div>
                    <div class="col-6 ps-4">
                        <h6 class="text-primary text-uppercase fw-bold fs-12 mb-3">Destino y Control</h6>
                        <p class="text-muted small mb-0">
                            <strong>Requisición Origen:</strong> {{ $purchaseOrder->requisition->folio }}<br>
                            <strong>Departamento:</strong> {{ $purchaseOrder->requisition->department->name ?? 'N/A' }}<br>
                            <strong>Solicitado por:</strong> {{ $purchaseOrder->requisition->creator->name ?? 'Sistema' }}<br>
                            <strong>Autorizado por:</strong> {{ $purchaseOrder->creator->name ?? 'Superadmin' }}
                        </p>
                    </div>
                </div>

                {{-- TABLA DE PARTIDAS --}}
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-centered mb-0">
                        <thead class="table-light">
                            <tr class="text-dark fw-bold">
                                <th style="width: 50px">#</th>
                                <th>Descripción del Producto/Servicio</th>
                                <th class="text-center" style="width: 100px text-nowrap">Cantidad</th>
                                <th class="text-end" style="width: 150px">P. Unitario</th>
                                <th class="text-end" style="width: 150px">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $item->description }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                                    <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end fw-bold text-dark">${{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" rowspan="3" class="border-0 align-top">
                                    <div class="bg-light p-3 rounded mt-2">
                                        <h6 class="fw-bold text-dark mb-2">Términos y Condiciones:</h6>
                                        <ul class="small text-muted mb-0">
                                            <li><strong>Condiciones de Pago:</strong> {{ $purchaseOrder->payment_terms ?? 'Crédito 30 días' }}</li>
                                            <li><strong>Tiempo de Entrega:</strong> {{ $purchaseOrder->estimated_delivery_days }} días hábiles</li>
                                            <li><strong>Moneda:</strong> {{ $purchaseOrder->currency }}</li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="text-end border-0 fw-bold">Subtotal:</td>
                                <td class="text-end border-0 fw-bold">${{ number_format($purchaseOrder->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end border-0 fw-bold text-muted small">I.V.A (16%):</td>
                                <td class="text-end border-0 text-muted small">${{ number_format($purchaseOrder->iva_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end border-0 bg-soft-primary"><h5 class="fw-bold text-primary mb-0">TOTAL:</h5></td>
                                <td class="text-end border-0 bg-soft-primary"><h5 class="fw-bold text-primary mb-0">${{ number_format($purchaseOrder->total, 2) }}</h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- ÁREA DE FIRMAS --}}
                <div class="row mt-5 pt-5 text-center">
                    <div class="col-4">
                        <div class="border-top border-dark pt-2 mx-4">
                            <small class="text-muted d-block">Solicitado por</small>
                            <span class="fw-bold">{{ $purchaseOrder->requisition->creator->name ?? 'Compras' }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        {{-- Espacio para código QR o Sello Digital --}}
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
                            <span class="fw-bold text-primary">{{ $purchaseOrder->creator->name ?? 'Dirección General' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
</style>
@endpush
@endsection