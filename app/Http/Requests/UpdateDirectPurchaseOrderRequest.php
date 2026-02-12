<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDirectPurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ocd = $this->route('directPurchaseOrder');

        // Solo se puede editar si está en DRAFT o RETURNED
        if (!in_array($ocd->status, ['DRAFT', 'RETURNED'])) {
            return false;
        }

        // Solo el creador puede editar su propia OCD
        if ($ocd->created_by !== Auth::id()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
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

            // Validación de tasa de IVA
            'items.*.iva_rate' => [
                'required',
                'numeric',
                'in:0,8,16',  // Solo permite 0%, 8% o 16%
            ],

            // Documentos (Opcionales en UPDATE)
            'quotation_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'support_documents' => ['nullable', 'array', 'max:5'],
            'support_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Proveedor
            'supplier_id.required' => 'Debe seleccionar un proveedor.',
            'supplier_id.exists' => 'El proveedor seleccionado no existe.',

            // Datos Presupuestales
            'cost_center_id.required' => 'Debe seleccionar un centro de costo.',
            'cost_center_id.exists' => 'El centro de costo seleccionado no existe.',

            'expense_category_id.required' => 'Debe seleccionar una categoría de gasto.',
            'expense_category_id.exists' => 'La categoría de gasto seleccionada no existe.',

            'application_month.required' => 'Debe seleccionar el mes de aplicación.',
            'application_month.date_format' => 'El formato del mes debe ser YYYY-MM.',

            // Justificación
            'justification.required' => 'La justificación es obligatoria.',
            'justification.min' => 'La justificación debe tener al menos 100 caracteres.',
            'justification.max' => 'La justificación no puede exceder 2000 caracteres.',

            // Partidas
            'items.required' => 'Debe agregar al menos una partida.',
            'items.min' => 'Debe agregar al menos una partida.',
            'items.*.description.required' => 'La descripción de la partida es obligatoria.',
            'items.*.description.max' => 'La descripción no puede exceder 500 caracteres.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.unit_price.required' => 'El precio unitario es obligatorio.',
            'items.*.unit_price.min' => 'El precio unitario debe ser mayor a 0.',

            // Tasa de IVA
            'items.*.iva_rate.required' => 'Debe seleccionar una tasa de IVA.',
            'items.*.iva_rate.in' => 'La tasa de IVA debe ser 0%, 8% o 16%.',

            // Documentos
            'quotation_file.mimes' => 'La cotización debe ser un archivo PDF o imagen (JPG, PNG).',
            'quotation_file.max' => 'La cotización no puede pesar más de 5MB.',

            'support_documents.max' => 'No puede adjuntar más de 5 documentos de soporte.',
            'support_documents.*.mimes' => 'Los documentos de soporte deben ser PDF, imágenes o documentos de Office.',
            'support_documents.*.max' => 'Cada documento no puede pesar más de 5MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'supplier_id' => 'proveedor',
            'cost_center_id' => 'centro de costo',
            'expense_category_id' => 'categoría de gasto',
            'application_month' => 'mes de aplicación',
            'justification' => 'justificación',
            'quotation_file' => 'cotización',
            'support_documents' => 'documentos de soporte',
            'items.*.description' => 'descripción',
            'items.*.quantity' => 'cantidad',
            'items.*.unit_price' => 'precio unitario',
            'items.*.iva_rate' => 'tasa de IVA',
        ];
    }

    /**
     * Validación adicional después de las reglas básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el TOTAL no exceda $250,000 MXN
            if ($this->has('items')) {
                $total = $this->calculateTotal();

                if ($total > 250000) {
                    $validator->errors()->add(
                        'total',
                        'El total de la OCD ($' . number_format($total, 2) . ') excede el límite máximo de $250,000.00 MXN. Para compras mayores debe usar el proceso de requisición regular.'
                    );
                }

                if ($total <= 0) {
                    $validator->errors()->add(
                        'total',
                        'El total de la OCD debe ser mayor a $0.00'
                    );
                }
            }
        });
    }

    /**
     * Calcular el total de la OCD considerando diferentes tasas de IVA
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

    /**
     * Get the error messages for authorization failures.
     */
    protected function failedAuthorization()
    {
        $ocd = $this->route('directPurchaseOrder');

        if (!in_array($ocd->status, ['DRAFT', 'RETURNED'])) {
            abort(403, 'Solo se pueden editar OCD en estado Borrador o Devueltas.');
        }

        if ($ocd->created_by !== Auth::id()) {
            abort(403, 'Solo puede editar sus propias OCD.');
        }
    }
}
