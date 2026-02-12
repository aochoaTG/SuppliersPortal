<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateCostCenterRequest
 *
 * Valida actualización de centros de costo.
 * El 'code' debe ser único ignorando el propio ID.
 */
class UpdateCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        // Ajusta según policies
    }

    public function rules(): array
    {
        $costCenterId = $this->route('cost_center');

        return [
            // ===== DATOS BASE =====
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cost_centers', 'code')
                    ->ignore($costCenterId)
                    ->whereNull('deleted_at'), // Ignora soft-deleted
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],

            // ===== RELACIONES ORGANIZACIONALES =====
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'responsible_user_id' => ['required', 'integer', 'exists:users,id'],

            // ===== TIPO DE PRESUPUESTO =====
            'budget_type' => ['required', 'string', 'in:ANNUAL,FREE_CONSUMPTION'],

            // ===== CAMPOS PARA CONSUMO LIBRE (condicionales) =====
            'global_amount' => [
                'nullable',
                'required_if:budget_type,FREE_CONSUMPTION',
                'numeric',
                'min:0.01',
            ],
            'validity_date' => [
                Rule::requiredIf(fn() => $this->input('budget_type') === 'FREE_CONSUMPTION'),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'free_consumption_justification' => [
                'nullable',
                'required_if:budget_type,FREE_CONSUMPTION',
                'string',
                'min:10',
                'max:1000',
            ],

            // ===== ESTADO =====
            'status' => ['required', 'string', 'in:ACTIVO,INACTIVO'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'name' => 'nombre',
            'description' => 'descripción',
            'category_id' => 'categoría',
            'company_id' => 'empresa',
            'responsible_user_id' => 'responsable',
            'budget_type' => 'tipo de presupuesto',
            'global_amount' => 'monto global',
            'validity_date' => 'fecha de vigencia',
            'free_consumption_justification' => 'justificación',
            'status' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'El código ya existe en el sistema.',
            'code.max' => 'El código no debe exceder 50 caracteres.',
            'name.max' => 'El nombre no debe exceder 200 caracteres.',
            'global_amount.required_if' => 'El monto global es requerido para centros de consumo libre.',
            'global_amount.min' => 'El monto global debe ser mayor a 0.',
            'free_consumption_justification.required_if' => 'La justificación es requerida para centros de consumo libre.',
            'free_consumption_justification.min' => 'La justificación debe tener al menos 10 caracteres.',
            'validity_date.required_if' => 'La fecha de vigencia es obligatoria para centros de consumo libre.',
            'validity_date.after_or_equal' => 'La fecha de vigencia debe ser igual o posterior a la fecha actual.',
            'validity_date.date' => 'La fecha de vigencia debe ser una fecha válida.',
            'budget_type.in' => 'El tipo de presupuesto debe ser ANNUAL o FREE_CONSUMPTION.',
            'status.in' => 'El estado debe ser ACTIVO o INACTIVO.',
        ];
    }
}
