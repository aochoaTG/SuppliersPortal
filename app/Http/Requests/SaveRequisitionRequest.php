<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $requisition = $this->route('requisition');

        if ($requisition) {
            return $requisition->canBeEdited();
        }

        return true;
    }

    public function rules(): array
    {
        $requisition = $this->route('requisition');
        $isUpdate = $requisition !== null;

        // Determinar el company_id para la validación cruzada del centro de costos
        $companyId = $isUpdate
            ? $requisition->company_id
            : $this->company_id;

        $rules = [
            // ======= Datos de la Requisición =======
            'cost_center_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'required',
                'exists:cost_centers,id',
                Rule::exists('cost_centers', 'id')->where('company_id', $companyId),
            ],

            'department_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'required',
                'integer',
                'exists:departments,id',
            ],

            'required_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            // ======= Partidas de la Requisición =======
            'items' => [
                $isUpdate ? 'sometimes' : 'required',
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_service_id' => [
                'required',
                'integer',
                'exists:products_services,id',
            ],

            'items.*.expense_category_id' => [
                'required',
                'integer',
                'exists:expense_categories,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
                'max:999999.999',
            ],

            'items.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items.*.unit' => [
                'nullable',
                'string',
                'max:30',
            ],

            'items.*.item_category' => [
                'nullable',
                'string',
                'max:120',
            ],

            'items.*.product_code' => [
                'nullable',
                'string',
                'max:80',
            ],

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

            'items.*.notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items.*.line_number' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];

        if (!$isUpdate) {
            $rules['company_id'] = [
                'required',
                'integer',
                'exists:companies,id',
            ];
        }

        if ($isUpdate) {
            $rules['items.*.id'] = [
                'nullable',
                'integer',
                'exists:requisition_items,id',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // Requisición
            'company_id.required' => 'La compañía es obligatoria.',
            'company_id.exists' => 'La compañía seleccionada no existe.',

            'cost_center_id.required' => 'El centro de costos es obligatorio.',
            'cost_center_id.exists' => 'El centro de costos no pertenece a la compañía seleccionada.',

            'department_id.required' => 'El departamento es obligatorio.',
            'department_id.exists' => 'El departamento seleccionado no existe.',

            'required_date.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy.',

            'description.max' => 'La descripción no puede exceder 500 caracteres.',

            // Partidas
            'items.required' => 'Debe agregar al menos una partida a la requisición (RN-003).',
            'items.min' => 'Debe agregar al menos una partida a la requisición (RN-003).',

            'items.*.product_service_id.required' => 'Debe seleccionar un producto del catálogo (RN-001).',
            'items.*.product_service_id.exists' => 'El producto seleccionado no existe en el catálogo.',

            'items.*.expense_category_id.required' => 'La categoría de gasto es obligatoria (RN-010A).',
            'items.*.expense_category_id.exists' => 'La categoría de gasto seleccionada no existe.',

            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.numeric' => 'La cantidad debe ser un número.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a cero.',
            'items.*.quantity.max' => 'La cantidad no puede exceder 999,999.999',

            'items.*.description.max' => 'La descripción de la partida no puede exceder 1000 caracteres.',
            'items.*.unit.max' => 'La unidad de medida no puede exceder 30 caracteres.',
            'items.*.notes.max' => 'Las observaciones no pueden exceder 1000 caracteres.',

            'items.*.suggested_vendor_id.exists' => 'El proveedor seleccionado no es válido o está listado como EFOS (empresas que facturan operaciones simuladas).',

            'items.*.id.exists' => 'La partida seleccionada no existe.',
            'items.*.line_number.min' => 'El número de línea debe ser mayor a cero.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $requisition = $this->route('requisition');
            $isUpdate = $requisition !== null;

            // En update, validar que la requisición pueda ser editada
            if ($isUpdate && !$requisition->canBeEdited()) {
                $validator->errors()->add(
                    'status',
                    'No se puede editar una requisición en estado "' . $requisition->status->label() . '". Solo se pueden editar requisiciones en borrador o pausadas.'
                );
            }

            // Validar que el centro de costos tenga presupuesto (RN-004)
            if ($this->cost_center_id) {
                $costCenter = \App\Models\CostCenter::find($this->cost_center_id);

                $fiscalYear = ($isUpdate && $requisition)
                    ? ($requisition->fiscal_year ?? $requisition->created_at->year)
                    : (int) date('Y');

                if ($costCenter && !$costCenter->hasAnnualBudget($fiscalYear)) {
                    $validator->errors()->add(
                        'cost_center_id',
                        'El centro de costos no tiene presupuesto asignado para el año fiscal (RN-004).'
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
                        'No puede agregar el mismo producto más de una vez. Si necesita diferentes cantidades, ajuste la partida existente.'
                    );
                }
            }

            // En update, validar que las partidas con ID pertenezcan a esta requisición
            if ($isUpdate && $this->items && is_array($this->items)) {
                foreach ($this->items as $index => $item) {
                    if (isset($item['id']) && !empty($item['id'])) {
                        $existingItem = \App\Models\RequisitionItem::find($item['id']);

                        if ($existingItem && (int) $existingItem->requisition_id !== (int) $requisition->id) {
                            $validator->errors()->add(
                                "items.{$index}.id",
                                'La partida no pertenece a esta requisición.'
                            );
                        }
                    }
                }

                // Validar cantidades mínimas y máximas del catálogo
                foreach ($this->items as $index => $item) {
                    if (isset($item['product_service_id']) && isset($item['quantity'])) {
                        $product = \App\Models\ProductService::find($item['product_service_id']);

                        if ($product) {
                            if ($product->minimum_quantity && $item['quantity'] < $product->minimum_quantity) {
                                $validator->errors()->add(
                                    "items.{$index}.quantity",
                                    "La cantidad debe ser mayor o igual a {$product->minimum_quantity} {$product->unit_of_measure}."
                                );
                            }

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

    protected function failedAuthorization()
    {
        $requisition = $this->route('requisition');

        if ($requisition && !$requisition->canBeEdited()) {
            abort(403, 'No se puede editar una requisición en estado "' . $requisition->status->label() . '".');
        }

        abort(403, 'No tiene permisos para editar esta requisición.');
    }
}
