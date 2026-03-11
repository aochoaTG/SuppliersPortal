{{-- Plantilla de fila para clonar. Usa __INDEX__ y __LINE__ como placeholders --}}
<tr class="req-item-row">
    {{-- # --}}
    <td class="align-middle">
        <input type="number" class="form-control form-control-sm line-number" name="items[__INDEX__][line_number]"
            value="__LINE__" min="1">
    </td>

    {{-- Categoría / Código --}}
    <td>
        <input type="text" class="form-control form-control-sm mb-1" name="items[__INDEX__][item_category]"
            placeholder="Categoría">
        <input type="text" class="form-control form-control-sm" name="items[__INDEX__][product_code]"
            placeholder="Código/SKU">
    </td>

    {{-- Descripción + Proveedor (AJAX) + Notas --}}
    <td>
        <input type="text" class="form-control form-control-sm mb-1" name="items[__INDEX__][description]"
            placeholder="Descripción de la partida" required>

        <div class="mt-1">
            {{-- SELECT2 AJAX: proveedor sugerido (opcional) --}}
            <select class="form-select-sm vendor-select form-select mb-1" name="items[__INDEX__][suggested_vendor_id]"
                data-ajax-url="{{ route('api.suppliers.search') }}" data-placeholder="Proveedor sugerido (opcional)"
                style="width:100%;">
                {{-- vacío; Select2 lo llenará por AJAX --}}
            </select>

            <textarea class="form-control form-control-sm" rows="1" name="items[__INDEX__][notes]"
                placeholder="Notas (opcional)"></textarea>
        </div>
    </td>

    {{-- Cantidad / Unidad --}}
    <td>
        <div class="d-flex flex-column gap-1">
            <input type="number" step="0.001" min="0.001" class="form-control form-control-sm qty-input"
                name="items[__INDEX__][quantity]" value="1" required>

            <select name="items[__INDEX__][unit]" class="form-select-sm form-select" required>
                <option value="">-- Unidad --</option>
                @foreach ($unitOptions as $group => $opts)
                    <optgroup label="{{ $group }}">
                        @foreach ($opts as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </td>

    {{-- Precio unitario --}}
    <td>
        <input type="number" step="0.0001" min="0" class="form-control form-control-sm price-input"
            name="items[__INDEX__][unit_price]" value="0" required>
    </td>

    {{-- IVA selector + porcentaje --}}
    <td>
        <div class="d-flex gap-1">
            <select name="items[__INDEX__][tax_id]" class="form-select-sm tax-select form-select"
                data-target-rate="#tax_rate___INDEX__">
                <option value="">-- Impuesto --</option>
                @foreach ($taxes as $tax)
                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate_percent }}">
                        {{ $tax->name }} ({{ number_format($tax->rate_percent, 2) }}%)
                    </option>
                @endforeach
            </select>

            <input type="number" step="0.01" min="0" max="100"
                class="form-control form-control-sm tax-input" id="tax_rate___INDEX__" name="items[__INDEX__][tax_rate]"
                value="0">
        </div>
    </td>

    {{-- Subtotal / Total / Acción --}}
    <td class="text-end align-middle"><span class="line-subtotal">0.00</span></td>
    <td class="text-end align-middle"><span class="line-total">0.00</span></td>
    <td class="text-center align-middle">
        <button type="button" class="btn btn-sm btn-outline-danger js-remove-row" title="Eliminar">
            <i class="ti ti-trash"></i>
        </button>
    </td>
</tr>
