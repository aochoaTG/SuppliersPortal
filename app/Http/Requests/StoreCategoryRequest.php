<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreCategoryRequest
 *
 * Valida la creación de una categoría:
 * - name: requerido, string corto, único
 * - description: opcional
 * - is_active: booleano
 */
class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Si usas Policies/Permisos, cámbialo por la verificación correspondiente.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe una categoría con ese nombre.',
        ];
    }
}
