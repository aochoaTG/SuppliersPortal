<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateCostCenterRequest
 *
 * Similar a Store, pero 'code' debe ser único ignorando el propio ID.
 */
class UpdateCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $costCenterId = $this->route('cost_center'); // Route Model Binding (ver rutas)

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cost_centers', 'code')->ignore($costCenterId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'company_id' => ['nullable', 'integer'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'category_id' => 'categoría',
            'company_id' => 'empresa',
            'is_active' => 'activo',
        ];
    }
}
