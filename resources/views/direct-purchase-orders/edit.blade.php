@extends('layouts.zircos')

@section('title', 'Editar Orden de Compra Directa')

@section('page.title', 'Editar Orden de Compra Directa')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes de Compra</a></li>
    <li class="breadcrumb-item active">Editar OCD</li>
@endsection

@section('content')
<div class="page-container">
    <div class="container-fluid">

        {{-- Encabezado de Página ZIRCOS --}}
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title mb-0">Editar OCD</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-end m-0">
                        <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Órdenes</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('direct-purchase-orders.show', $directPurchaseOrder->id) }}">{{ $directPurchaseOrder->folio ?? 'BORRADOR' }}</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Alerta de Estado --}}
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-1"></i>
            <strong>Editando OCD:</strong> {{ $directPurchaseOrder->folio ?? 'BORRADOR' }} 
            - Estado: <strong>{{ $directPurchaseOrder->getStatusLabel() }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <form action="{{ route('direct-purchase-orders.update', $directPurchaseOrder->id) }}" method="POST" enctype="multipart/form-data" id="ocd-form">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            
                            {{-- FILA 1: PROVEEDOR Y CONDICIONES --}}
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Proveedor <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-building"></i></span>
                                        <select name="supplier_id" id="supplier_id" class="form-select form-select-sm @error('supplier_id') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" 
                                                        data-payment-terms="{{ $supplier->default_payment_terms }}"
                                                        data-delivery-days="{{ $supplier->avg_delivery_time }}"
                                                        {{ old('supplier_id', $directPurchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('supplier_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="ti ti-building"></i></span>
                                    <select name="supplier_id" id="supplier_id" class="form-select form-select-sm @error('supplier_id') is-invalid @enderror" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                    data-payment-terms="{{ $supplier->default_payment_terms }}"
                                                    data-delivery-days="{{ $supplier->avg_delivery_time }}"
                                                    {{ old('supplier_id', $directPurchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Días Entrega</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-clock"></i></span>
                                        <input type="number" name="estimated_delivery_days" id="estimated_delivery_days" class="form-control form-control-sm" value="{{ old('estimated_delivery_days', $directPurchaseOrder->estimated_delivery_days) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- FILA 2: PRESUPUESTO --}}
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Centro de Costo <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-chart-pie"></i></span>
                                        <select name="cost_center_id" id="cost_center_id" class="form-select form-select-sm @error('cost_center_id') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($costCenters as $cc)
                                                <option value="{{ $cc->id }}" {{ old('cost_center_id', $directPurchaseOrder->cost_center_id) == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('cost_center_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Categoría de Gasto <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-category"></i></span>
                                        <select name="expense_category_id" id="expense_category_id" class="form-select form-select-sm @error('expense_category_id') is-invalid @enderror" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($expenseCategories as $category)
                                                <option value="{{ $category->id }}" {{ old('expense_category_id', $directPurchaseOrder->expense_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('expense_category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Mes de Aplicación <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                        <input type="month" name="application_month" id="application_month" class="form-control form-control-sm @error('application_month') is-invalid @enderror" value="{{ old('application_month', $directPurchaseOrder->application_month) }}" min="{{ now()->format('Y-m') }}" required>
                                    </div>
                                    @error('application_month') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- JUSTIFICACIÓN COMPACTA --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Justificación de Compra Directa <span class="text-danger">*</span></label>
                                <textarea name="justification" id="justification" rows="3" class="form-control form-control-sm @error('justification') is-invalid @enderror" placeholder="Mínimo 100 caracteres..." required minlength="100" maxlength="2000">{{ old('justification', $directPurchaseOrder->justification) }}</textarea>
                                <div class="text-end small text-muted"><span id="char-count">0</span> / 2000</div>
                                @error('justification') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- TABLA DE PARTIDAS CON TASA DE IVA --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="m-0 fw-bold text-uppercase small text-primary">Partidas</h6>
                                <button type="button" class="btn btn-xs btn-outline-primary" id="add-item-btn"><i class="ti ti-plus"></i> Agregar</button>
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover mb-0" id="items-table">
                                    <thead class="table-light small">
                                        <tr>
                                            <th width="28%" class="ps-2">Descripción</th>
                                            <th width="8%">Cant.</th>
                                            <th width="10%">P. Unit.</th>
                                            <th width="9%">IVA</th>
                                            <th width="11%">Subtotal</th>
                                            <th width="11%">IVA $</th>
                                            <th width="11%">Total</th>
                                            <th width="3%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        @foreach($directPurchaseOrder->items as $index => $item)
                                            <tr class="item-row">
                                                <td class="ps-2">
                                                    <input type="text" 
                                                           name="items[{{ $index }}][description]" 
                                                           class="form-control form-control-sm border-0 item-description" 
                                                           value="{{ old("items.$index.description", $item->description) }}"
                                                           required 
                                                           maxlength="500">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           name="items[{{ $index }}][quantity]" 
                                                           class="form-control form-control-sm border-0 item-quantity" 
                                                           value="{{ old("items.$index.quantity", $item->quantity) }}"
                                                           step="0.01" 
                                                           min="0.01" 
                                                           required>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           name="items[{{ $index }}][unit_price]" 
                                                           class="form-control form-control-sm border-0 item-unit-price" 
                                                           value="{{ old("items.$index.unit_price", $item->unit_price) }}"
                                                           step="0.01" 
                                                           min="0.01" 
                                                           required>
                                                </td>
                                                <td>
                                                    <select name="items[{{ $index }}][iva_rate]" 
                                                            class="form-select form-select-sm border-0 item-iva-rate" 
                                                            required>
                                                        <option value="16" {{ old("items.$index.iva_rate", $item->iva_rate) == 16 ? 'selected' : '' }}>16%</option>
                                                        <option value="8" {{ old("items.$index.iva_rate", $item->iva_rate) == 8 ? 'selected' : '' }}>8%</option>
                                                        <option value="0" {{ old("items.$index.iva_rate", $item->iva_rate) == 0 ? 'selected' : '' }}>0%</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm border-0 bg-transparent item-subtotal text-end" 
                                                           value="${{ number_format($item->subtotal, 2) }}" 
                                                           readonly 
                                                           tabindex="-1">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm border-0 bg-transparent item-iva text-end" 
                                                           value="${{ number_format($item->iva_amount, 2) }}" 
                                                           readonly 
                                                           tabindex="-1">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm border-0 bg-transparent item-total text-end fw-bold" 
                                                           value="${{ number_format($item->total, 2) }}" 
                                                           readonly 
                                                           tabindex="-1">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" 
                                                            class="btn btn-link text-danger p-0 remove-item-btn" 
                                                            title="Eliminar">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('items') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                            {{-- ADJUNTOS COMPACTOS --}}
                            <div class="row g-2 mt-3">
                                <div class="col-md-12 mb-2">
                                    @if($directPurchaseOrder->documents->count() > 0)
                                        <div class="alert alert-info py-2 mb-2">
                                            <small>
                                                <strong><i class="ti ti-paperclip me-1"></i>Documentos actuales:</strong>
                                                @foreach($directPurchaseOrder->documents as $doc)
                                                    <span class="badge bg-light text-dark ms-1">{{ $doc->original_filename }}</span>
                                                @endforeach
                                            </small>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nueva Cotización (Opcional)</label>
                                    <input type="file" name="quotation_file" id="quotation_file" class="form-control form-control-sm @error('quotation_file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Reemplazará la cotización actual</small>
                                    @error('quotation_file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nuevos Anexos (Opcional)</label>
                                    <input type="file" name="support_documents[]" id="support_documents" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" multiple>
                                    <small class="text-muted">Se agregarán a los existentes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RESUMEN LATERAL --}}
                <div class="col-lg-3">
                    <div class="card shadow-none border">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 small fw-bold">Resumen</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0 small">
                                <tbody>
                                    <tr>
                                        <td class="ps-3 border-0">Subtotal</td>
                                        <td class="text-end pe-3 border-0" id="summary-subtotal">${{ number_format($directPurchaseOrder->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">IVA</td>
                                        <td class="text-end pe-3" id="summary-iva">${{ number_format($directPurchaseOrder->iva_amount, 2) }}</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td class="ps-3">TOTAL</td>
                                        <td class="text-end pe-3 text-primary fs-6" id="summary-total">${{ number_format($directPurchaseOrder->total, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="px-3 py-2 bg-light border-top">
                                <small class="text-muted">
                                    <i class="ti ti-alert-circle me-1"></i>Límite: <strong>$250,000</strong>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer p-2 bg-transparent border-top-0">
                            <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">
                                <i class="ti ti-device-floppy me-1"></i>Guardar Cambios
                            </button>
                            <a href="{{ route('direct-purchase-orders.show', $directPurchaseOrder->id) }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="ti ti-x me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // Contador de caracteres
    $('#justification').on('input', function() {
        const count = $(this).val().length;
        $('#char-count').text(count).toggleClass('text-danger', count < 100).toggleClass('text-success', count >= 100);
    }).trigger('input');

    // Calcular montos al cambiar valores
    $(document).on('input change', '.item-quantity, .item-unit-price, .item-iva-rate', function() {
        calculateItemRow($(this).closest('tr'));
        calculateTotals();
    });

    // Calcular fila individual
    function calculateItemRow(row) {
        const qty = parseFloat(row.find('.item-quantity').val()) || 0;
        const price = parseFloat(row.find('.item-unit-price').val()) || 0;
        const ivaRate = parseFloat(row.find('.item-iva-rate').val()) || 0;
        
        const subtotal = qty * price;
        const iva = subtotal * (ivaRate / 100);
        const total = subtotal + iva;
        
        row.find('.item-subtotal').val('$' + subtotal.toFixed(2));
        row.find('.item-iva').val('$' + iva.toFixed(2));
        row.find('.item-total').val('$' + total.toFixed(2));
    }

    // Calcular totales generales
    function calculateTotals() {
        let subtotal = 0;
        let ivaTotal = 0;
        
        $('.item-row').each(function() {
            const qty = parseFloat($(this).find('.item-quantity').val()) || 0;
            const price = parseFloat($(this).find('.item-unit-price').val()) || 0;
            const ivaRate = parseFloat($(this).find('.item-iva-rate').val()) || 0;
            
            const itemSubtotal = qty * price;
            const itemIva = itemSubtotal * (ivaRate / 100);
            
            subtotal += itemSubtotal;
            ivaTotal += itemIva;
        });
        
        const total = subtotal + ivaTotal;
        
        $('#summary-subtotal').text('$' + subtotal.toFixed(2));
        $('#summary-iva').text('$' + ivaTotal.toFixed(2));
        $('#summary-total').text('$' + total.toFixed(2)).toggleClass('text-danger', total > 250000).toggleClass('text-primary', total <= 250000);
    }

    // Agregar partida
    let itemIndex = {{ $directPurchaseOrder->items->count() }};
    $('#add-item-btn').on('click', function() {
        const newRow = `
            <tr class="item-row">
                <td class="ps-2"><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm border-0 item-description" required maxlength="500"></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm border-0 item-quantity" step="0.01" min="0.01" required></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm border-0 item-unit-price" step="0.01" min="0.01" required></td>
                <td>
                    <select name="items[${itemIndex}][iva_rate]" class="form-select form-select-sm border-0 item-iva-rate" required>
                        <option value="16">16%</option>
                        <option value="8">8%</option>
                        <option value="0">0%</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm border-0 bg-transparent item-subtotal text-end" value="$0.00" readonly tabindex="-1"></td>
                <td><input type="text" class="form-control form-control-sm border-0 bg-transparent item-iva text-end" value="$0.00" readonly tabindex="-1"></td>
                <td><input type="text" class="form-control form-control-sm border-0 bg-transparent item-total text-end fw-bold" value="$0.00" readonly tabindex="-1"></td>
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0 remove-item-btn"><i class="ti ti-trash"></i></button></td>
            </tr>
        `;
        $('#items-tbody').append(newRow);
        itemIndex++;
    });

    // Eliminar partida
    $(document).on('click', '.remove-item-btn', function() {
        if ($('#items-tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            alert('Debe haber al menos una partida.');
        }
    });

    // Validar antes de enviar
    $('#ocd-form').on('submit', function(e) {
        const total = parseFloat($('#summary-total').text().replace(/[$,]/g, ''));
        if (total > 250000) {
            e.preventDefault();
            alert('El total excede $250,000 MXN.');
            return false;
        }
        if ($('#justification').val().length < 100) {
            e.preventDefault();
            alert('La justificación debe tener al menos 100 caracteres.');
            return false;
        }
    });

    // Calcular totales iniciales
    $('.item-row').each(function() {
        calculateItemRow($(this));
    });
    calculateTotals();
});
</script>
@endpush