{{-- Fila existente de partida de requisición --}}
<tr class="req-item-row">
    {{-- # --}}
    <td class="align-middle">
        <input type="number" class="form-control form-control-sm line-number"
            name="items[{{ $idx }}][line_number]" value="{{ $it['line_number'] ?? $loop->iteration }}"
            min="1">
    </td>

    {{-- Categoría / Código --}}
    <td>
        <input type="text" class="form-control form-control-sm mb-1" name="items[{{ $idx }}][item_category]"
            value="{{ $it['item_category'] ?? '' }}" placeholder="Categoría">

        <input type="text" class="form-control form-control-sm" name="items[{{ $idx }}][product_code]"
            value="{{ $it['product_code'] ?? '' }}" placeholder="Código/SKU">
    </td>

    {{-- Descripción + Proveedor (AJAX) + Notas --}}
    <td>
        <input type="text" class="form-control form-control-sm mb-1" name="items[{{ $idx }}][description]"
            value="{{ $it['description'] ?? '' }}" placeholder="Descripción de la partida" required>

        <div class="mt-1">
            <select class="form-select-sm vendor-select form-select mb-1"
                name="items[{{ $idx }}][suggested_vendor_id]"
                data-ajax-url="{{ route('api.suppliers.search') }}" data-placeholder="Proveedor sugerido (opcional)"
                style="width:100%;">
                @if (!empty($it['suggested_vendor_id']) && !empty($it['suggested_vendor_name']))
                    <option value="{{ $it['suggested_vendor_id'] }}" selected>
                        {{ $it['suggested_vendor_name'] }}
                        @if (!empty($it['suggested_vendor_rfc']))
                            ({{ $it['suggested_vendor_rfc'] }})
                        @endif
                    </option>
                @endif
                {{-- Si no hay datos, Select2 cargará por AJAX cuando el usuario escriba --}}
            </select>

            <textarea class="form-control form-control-sm" rows="1" name="items[{{ $idx }}][notes]"
                placeholder="Notas (opcional)">{{ $it['notes'] ?? '' }}</textarea>
        </div>
    </td>

    {{-- Cantidad / Unidad --}}
    <td>
        <div class="d-flex flex-column gap-1">
            <input type="number" step="0.001" min="0.001" class="form-control form-control-sm qty-input"
                name="items[{{ $idx }}][quantity]" value="{{ $it['quantity'] ?? 1 }}" required>

            <select name="items[{{ $idx }}][unit]" class="form-select-sm form-select" required>
                <option value="">-- Unidad --</option>
                @foreach ($unitOptions as $group => $opts)
                    <optgroup label="{{ $group }}">
                        @foreach ($opts as $val => $label)
                            <option value="{{ $val }}" {{ ($it['unit'] ?? '') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </td>

    {{-- Precio, IVA, Subtotal/Total, Acción (igual que ya tienes) --}}
    <td>
        <input type="number" step="0.0001" min="0" class="form-control form-control-sm price-input"
            name="items[{{ $idx }}][unit_price]" value="{{ $it['unit_price'] ?? 0 }}" required>
    </td>

    @php
        $taxRate = $it['tax_rate'] ?? 0;
    @endphp
    <td>
        <div class="d-flex gap-1">
            <select name="items[{{ $idx }}][tax_id]" class="form-select-sm tax-select form-select"
                data-target-rate="#tax_rate_{{ $idx }}">
                <option value="">-- Impuesto --</option>
                @foreach ($taxes as $tax)
                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate_percent }}"
                        {{ (string) ($it['tax_id'] ?? '') === (string) $tax->id ? 'selected' : '' }}>
                        {{ $tax->name }} ({{ number_format($tax->rate_percent, 2) }}%)
                    </option>
                @endforeach
            </select>

            <input type="number" step="0.01" min="0" max="100"
                class="form-control form-control-sm tax-input" id="tax_rate_{{ $idx }}"
                name="items[{{ $idx }}][tax_rate]" value="{{ $taxRate }}">
        </div>
    </td>

    @php
        $qty = (float) ($it['quantity'] ?? 1);
        $price = (float) ($it['unit_price'] ?? 0);
        $rate = (float) $taxRate;
        $subtotal = $qty * $price;
        $total = $subtotal + $subtotal * ($rate / 100);
    @endphp
    <td class="text-end align-middle"><span class="line-subtotal">{{ number_format($subtotal, 2, '.', '') }}</span>
    </td>
    <td class="text-end align-middle"><span class="line-total">{{ number_format($total, 2, '.', '') }}</span></td>

    <td class="text-center align-middle">
        <button type="button" class="btn btn-sm btn-outline-danger js-remove-row" title="Eliminar">
            <i class="ti ti-trash"></i>
        </button>
    </td>
</tr>
