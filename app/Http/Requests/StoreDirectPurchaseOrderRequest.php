<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos Presupuestales
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'application_month' => [
                'required',
                'date_format:Y-m',
                function ($attribute, $value, $fail) {
                    $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $value)->startOfMonth();
                    $currentMonth = \Carbon\Carbon::now()->startOfMonth();

                    if ($selectedMonth->lt($currentMonth)) {
                        $fail('No se pueden crear OCD para meses pasados.');
                    }
                },
            ],

            // Justificación
            'justification' => ['required', 'string', 'min:100', 'max:2000'],

            // Condiciones del Proveedor (Opcionales)
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'estimated_delivery_days' => ['nullable', 'integer', 'min:1', 'max:365'],

            // PARTIDAS CON TASA DE IVA
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],

            // ← NUEVO: Validación de tasa de IVA
            'items.*.iva_rate' => [
                'required',
                'numeric',
                'in:0,8,16',  // Solo permite 0%, 8% o 16%
            ],

            // Documentos
            'quotation_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'support_documents' => ['nullable', 'array', 'max:5'],
            'support_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            // Mensajes existentes...
            'supplier_id.required' => 'Debe seleccionar un proveedor.',
            'supplier_id.exists' => 'El proveedor seleccionado no existe.',

            'cost_center_id.required' => 'Debe seleccionar un centro de costo.',
            'expense_category_id.required' => 'Debe seleccionar una categoría de gasto.',
            'application_month.required' => 'Debe seleccionar el mes de aplicación.',

            'justification.required' => 'La justificación es obligatoria.',
            'justification.min' => 'La justificación debe tener al menos 100 caracteres.',
            'justification.max' => 'La justificación no puede exceder 2000 caracteres.',

            'items.required' => 'Debe agregar al menos una partida.',
            'items.min' => 'Debe agregar al menos una partida.',
            'items.*.description.required' => 'La descripción es obligatoria.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.unit_price.required' => 'El precio unitario es obligatorio.',
            'items.*.unit_price.min' => 'El precio unitario debe ser mayor a 0.',

            // ← NUEVO: Mensajes para tasa de IVA
            'items.*.iva_rate.required' => 'Debe seleccionar una tasa de IVA.',
            'items.*.iva_rate.in' => 'La tasa de IVA debe ser 0%, 8% o 16%.',

            'quotation_file.required' => 'Debe adjuntar la cotización del proveedor.',
            'quotation_file.mimes' => 'La cotización debe ser PDF o imagen (JPG, PNG).',
            'quotation_file.max' => 'La cotización no puede pesar más de 5MB.',

            'support_documents.max' => 'No puede adjuntar más de 5 documentos de soporte.',
            'support_documents.*.max' => 'Cada documento no puede pesar más de 5MB.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items')) {
                $total = $this->calculateTotal();

                if ($total > 250000) {
                    $validator->errors()->add(
                        'total',
                        'El total de la OCD ($' . number_format($total, 2) . ') excede el límite máximo de $250,000.00 MXN.'
                    );
                }

                if ($total <= 0) {
                    $validator->errors()->add('total', 'El total debe ser mayor a $0.00');
                }
            }
        });
    }

    /**
     * Calcular el total considerando diferentes tasas de IVA
     */
    protected function calculateTotal(): float
    {
        $subtotal = 0;
        $ivaTotal = 0;

        if ($this->has('items') && is_array($this->items)) {
            foreach ($this->items as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $ivaRate = floatval($item['iva_rate'] ?? 16); // Default 16%

                $itemSubtotal = $quantity * $unitPrice;
                $itemIva = $itemSubtotal * ($ivaRate / 100);

                $subtotal += $itemSubtotal;
                $ivaTotal += $itemIva;
            }
        }

        return round($subtotal + $ivaTotal, 2);
    }
}
