<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnualBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Route Model Binding: {annual_budget}
        $id = $this->route('annual_budget'); // puede ser modelo o id; Rule::ignore lo maneja

        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'cost_center_id' => [
                'required',
                'integer',
                // Debe existir y pertenecer a la compañía seleccionada
                Rule::exists('cost_centers', 'id')->where(function ($q) {
                    $q->where('company_id', $this->input('company_id'));
                }),
                // Unicidad por centro + año, ignorando el registro actual
                Rule::unique('annual_budgets')->ignore($id)->where(function ($q) {
                    $q->where('fiscal_year', (int) $this->input('fiscal_year'));
                }),
            ],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'amount_assigned' => ['required', 'numeric', 'min:0.01'],
            // Caches opcionales
            'amount_committed' => ['nullable', 'numeric', 'min:0'],
            'amount_consumed' => ['nullable', 'numeric', 'min:0'],
            'amount_released' => ['nullable', 'numeric', 'min:0'],
            'amount_adjusted' => ['nullable', 'numeric', 'min:0'],
            'amount_available' => ['nullable', 'numeric'],
            'is_closed' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Selecciona la compañía.',
            'company_id.exists' => 'La compañía indicada no existe.',
            'cost_center_id.required' => 'Selecciona el centro de costo.',
            'cost_center_id.exists' => 'El centro de costo no existe o no pertenece a la compañía seleccionada.',
            'cost_center_id.unique' => 'Ya existe un presupuesto para este centro y año fiscal.',
            'fiscal_year.required' => 'El año fiscal es obligatorio.',
            'fiscal_year.min' => 'El año fiscal debe ser 2000 o mayor.',
            'fiscal_year.max' => 'El año fiscal debe ser 2100 o menor.',
            'amount_assigned.required' => 'Indica el monto asignado.',
            'amount_assigned.min' => 'El monto asignado debe ser mayor a cero.',
        ];
    }

    public function attributes(): array
    {
        return [
            'company_id' => 'compañía',
            'cost_center_id' => 'centro de costo',
            'fiscal_year' => 'año fiscal',
            'amount_assigned' => 'monto asignado',
        ];
    }
}
