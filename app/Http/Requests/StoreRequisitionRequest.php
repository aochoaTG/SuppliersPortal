<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ======= Datos de la Requisición =======
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id'
            ],

            'cost_center_id' => [
                'required',
                'exists:cost_centers,id',
                // Valida que el Centro de Costos pertenezca a la Compañía
                Rule::exists('cost_centers', 'id')->where('company_id', $this->company_id),
            ],

            'department_id' => [
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
            // RN-003: Una requisición debe tener al menos una partida
            'items' => [
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

            // Campos heredados del catálogo (opcionales, se pueden sobrescribir)
            'items.*.description' => [
                'nullable',
                'string',
                'max:255'
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

            // Proveedor sugerido (del catálogo, no vinculante)
            // Validación Anti-EFOS (Proveedores en lista negra del SAT)
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

            // Número de línea (opcional, se asigna automáticamente)
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
            'company_id.required' => 'La compañía es obligatoria.',
            'company_id.exists' => 'La compañía seleccionada no existe.',

            'cost_center_id.required' => 'El centro de costos es obligatorio.',
            'cost_center_id.exists' => 'El centro de costos no pertenece a la compañía seleccionada.',

            'department_id.required' => 'El departamento es obligatorio.',
            'department_id.exists' => 'El departamento seleccionado no existe.',

            'required_date.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy.',

            // Partidas
            'items.required' => 'Debe agregar al menos una partida a la requisición (RN-003).',
            'items.min' => 'Debe agregar al menos una partida a la requisición (RN-003).',

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

            // Proveedor sugerido (Anti-EFOS)
            'items.*.suggested_vendor_id.exists' => 'El proveedor seleccionado no es válido o está listado como EFOS (empresas que facturan operaciones simuladas).',
        ];
    }

    /**
     * Configurar validador personalizado.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el centro de costos tenga presupuesto asignado (RN-004)
            if ($this->cost_center_id) {
                $costCenter = \App\Models\CostCenter::find($this->cost_center_id);

                // Usar el año actual para verificar presupuesto
                $currentYear = (int) date('Y');

                if ($costCenter && !$costCenter->hasAnnualBudget($currentYear)) {
                    $validator->errors()->add(
                        'cost_center_id',
                        'El centro de costos no tiene presupuesto asignado para el año actual (RN-004).'
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
        });
    }
}
