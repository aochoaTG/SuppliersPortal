<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verificar que la requisición pueda ser editada
        $requisition = $this->route('requisition');

        // Solo se puede editar si está en draft o paused
        return $requisition && $requisition->canBeEdited();
    }

    public function rules(): array
    {
        return [
            // ======= Datos de la Requisición =======
            'cost_center_id' => [
                'sometimes',
                'required',
                'exists:cost_centers,id',
                // Valida que el Centro de Costos pertenezca a la Compañía de la requisición
                Rule::exists('cost_centers', 'id')->where('company_id', $this->route('requisition')->company_id),
            ],

            'department_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:departments,id'
            ],

            'required_date' => [
                'nullable',
                'date',
                'after_or_equal:today'
            ],

            'description' => [
                'nullable',
                'string',
                'max:500'
            ],

            // ======= Partidas de la Requisición =======
            // Si se envían items, debe haber al menos uno
            'items' => [
                'sometimes',
                'required',
                'array',
                'min:1'
            ],

            // RN-001: Solo productos del catálogo pueden ser requisitados
            'items.*.product_service_id' => [
                'required',
                'integer',
                'exists:products_services,id'
            ],

            // RN-010A, RN-010B: Categoría de gasto OBLIGATORIA, definida por REQUISITOR
            'items.*.expense_category_id' => [
                'required',
                'integer',
                'exists:expense_categories,id'
            ],

            // Cantidad solicitada
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
                'max:999999.999'
            ],

            // Campos heredados del catálogo
            'items.*.description' => [
                'nullable',
                'string',
                'max:1000'
            ],

            'items.*.unit' => [
                'nullable',
                'string',
                'max:30'
            ],

            'items.*.item_category' => [
                'nullable',
                'string',
                'max:120'
            ],

            'items.*.product_code' => [
                'nullable',
                'string',
                'max:80'
            ],

            // Proveedor sugerido (Anti-EFOS)
            'items.*.suggested_vendor_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where(function ($q) {
                    $q->whereNotExists(function ($sub) {
                        $sub->from('sat_efos_69b as e')
                            ->whereColumn('e.rfc', 'suppliers.rfc')
                            ->whereIn('e.situation', ['Definitivo', 'Presunto']);
                    });
                }),
            ],

            // Observaciones del requisitor
            'items.*.notes' => [
                'nullable',
                'string',
                'max:1000'
            ],

            // ID de la partida (para actualizar partidas existentes)
            'items.*.id' => [
                'nullable',
                'integer',
                'exists:requisition_items,id'
            ],

            // Número de línea
            'items.*.line_number' => [
                'nullable',
                'integer',
                'min:1'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Requisición
            'cost_center_id.required' => 'El centro de costos es obligatorio.',
            'cost_center_id.exists' => 'El centro de costos no pertenece a la compañía de la requisición.',

            'department_id.required' => 'El departamento es obligatorio.',
            'department_id.exists' => 'El departamento seleccionado no existe.',

            'required_date.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy.',

            'description.max' => 'La descripción no puede exceder 500 caracteres.',

            // Partidas
            'items.required' => 'Debe tener al menos una partida en la requisición (RN-003).',
            'items.min' => 'Debe tener al menos una partida en la requisición (RN-003).',

            // Producto del catálogo (RN-001)
            'items.*.product_service_id.required' => 'Debe seleccionar un producto del catálogo (RN-001).',
            'items.*.product_service_id.exists' => 'El producto seleccionado no existe en el catálogo.',

            // Categoría de gasto (RN-010A)
            'items.*.expense_category_id.required' => 'La categoría de gasto es obligatoria (RN-010A).',
            'items.*.expense_category_id.exists' => 'La categoría de gasto seleccionada no existe.',

            // Cantidad
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.numeric' => 'La cantidad debe ser un número.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a cero.',
            'items.*.quantity.max' => 'La cantidad no puede exceder 999,999.999',

            // Descripción
            'items.*.description.max' => 'La descripción de la partida no puede exceder 1000 caracteres.',

            // Unidad
            'items.*.unit.max' => 'La unidad de medida no puede exceder 30 caracteres.',

            // Notas
            'items.*.notes.max' => 'Las observaciones no pueden exceder 1000 caracteres.',

            // Proveedor sugerido (Anti-EFOS)
            'items.*.suggested_vendor_id.exists' => 'El proveedor seleccionado no es válido o está listado como EFOS.',

            // ID de partida
            'items.*.id.exists' => 'La partida seleccionada no existe.',

            // Número de línea
            'items.*.line_number.min' => 'El número de línea debe ser mayor a cero.',
        ];
    }

    /**
     * Configurar validador personalizado.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $requisition = $this->route('requisition');

            // Validar que la requisición pueda ser editada
            if ($requisition && !$requisition->canBeEdited()) {
                $validator->errors()->add(
                    'status',
                    'No se puede editar una requisición en estado "' . $requisition->status->label() . '". Solo se pueden editar requisiciones en borrador o pausadas.'
                );
            }

            // Validar que el centro de costos tenga presupuesto (si se está cambiando)
            if ($this->cost_center_id && $requisition) {
                $costCenter = \App\Models\CostCenter::find($this->cost_center_id);

                // Usar el año fiscal de la requisición
                $fiscalYear = $requisition->fiscal_year ?? $requisition->created_at->year;

                if ($costCenter && !$costCenter->hasAnnualBudget($fiscalYear)) {
                    $validator->errors()->add(
                        'cost_center_id',
                        'El centro de costos no tiene presupuesto asignado para el año fiscal de la requisición (RN-004).'
                    );
                }
            }

            // Validar que no haya partidas duplicadas (mismo producto)
            if ($this->items && is_array($this->items)) {
                $productIds = array_column($this->items, 'product_service_id');
                $duplicates = array_diff_assoc($productIds, array_unique($productIds));

                if (!empty($duplicates)) {
                    $validator->errors()->add(
                        'items',
                        'No puede agregar el mismo producto más de una vez en la requisición.'
                    );
                }
            }

            // Validar que las partidas con ID pertenezcan a esta requisición
            if ($this->items && is_array($this->items) && $requisition) {
                foreach ($this->items as $index => $item) {
                    if (isset($item['id']) && !empty($item['id'])) {
                        $existingItem = \App\Models\RequisitionItem::find($item['id']);

                        // Usar comparación NO estricta o casting explícito
                        if ($existingItem && (int)$existingItem->requisition_id !== (int)$requisition->id) {
                            $validator->errors()->add(
                                "items.{$index}.id",
                                'La partida no pertenece a esta requisición.'
                            );
                        }
                    }
                }
            }

            // Validar cantidades mínimas y máximas del catálogo
            if ($this->items && is_array($this->items)) {
                foreach ($this->items as $index => $item) {
                    if (isset($item['product_service_id']) && isset($item['quantity'])) {
                        $product = \App\Models\ProductService::find($item['product_service_id']);

                        if ($product) {
                            // Validar cantidad mínima
                            if ($product->minimum_quantity && $item['quantity'] < $product->minimum_quantity) {
                                $validator->errors()->add(
                                    "items.{$index}.quantity",
                                    "La cantidad debe ser mayor o igual a {$product->minimum_quantity} {$product->unit_of_measure}."
                                );
                            }

                            // Validar cantidad máxima
                            if ($product->maximum_quantity && $item['quantity'] > $product->maximum_quantity) {
                                $validator->errors()->add(
                                    "items.{$index}.quantity",
                                    "La cantidad no puede exceder {$product->maximum_quantity} {$product->unit_of_measure}."
                                );
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Mensajes personalizados para errores de autorización.
     */
    protected function failedAuthorization()
    {
        $requisition = $this->route('requisition');

        if ($requisition && !$requisition->canBeEdited()) {
            abort(403, 'No se puede editar una requisición en estado "' . $requisition->status->label() . '".');
        }

        abort(403, 'No tiene permisos para editar esta requisición.');
    }
}
