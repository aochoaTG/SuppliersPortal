<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreCostCenterRequest
 *
 * Valida creación:
 * - code: requerido, único, corto
 * - name: requerido
 * - category_id: requerido y existente
 * - company_id: nullable (si aplica)
 * - is_active: booleano
 */
class StoreCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajusta si usas policies.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:cost_centers,code'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'company_id' => ['nullable', 'integer'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        // Para mensajes en español con nombres de campos amigables
        return [
            'code' => 'código',
            'name' => 'nombre',
            'category_id' => 'categoría',
            'company_id' => 'empresa',
            'is_active' => 'activo',
        ];
    }
}
