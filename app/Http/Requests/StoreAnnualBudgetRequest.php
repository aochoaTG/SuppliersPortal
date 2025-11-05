<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnualBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta si usas policies
    }

    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],
            'cost_center_id' => [
                'required',
                'integer',
                // Debe existir y pertenecer a la compañía seleccionada
                Rule::exists('cost_centers', 'id')->where(function ($q) {
                    $q->where('company_id', $this->input('company_id'));
                }),
                // Unicidad compuesta con el año fiscal
                Rule::unique('annual_budgets')->where(function ($q) {
                    $q->where('fiscal_year', $this->input('fiscal_year'));
                }),
            ],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'amount_assigned' => ['required', 'numeric', 'min:0.01'],
            // Campos cache opcionales (si los manejas en DB)
            'amount_committed' => ['nullable', 'numeric', 'min:0'],
            'amount_consumed' => ['nullable', 'numeric', 'min:0'],
            'amount_released' => ['nullable', 'numeric', 'min:0'],
            'amount_adjusted' => ['nullable', 'numeric', 'min:0'],
            'amount_available' => ['nullable', 'numeric'], // si viene vacío, el controller lo iguala a assigned
            'is_closed' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
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

    public function messages(): array
    {
        return [
            'cost_center_id.exists' => 'El centro de costo no pertenece a la compañía seleccionada.',
            'cost_center_id.unique' => 'Ya existe un presupuesto para este centro y año fiscal.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => $this->toInt($this->input('company_id')),
            'cost_center_id' => $this->toInt($this->input('cost_center_id')),
            'fiscal_year' => $this->toInt($this->input('fiscal_year')),
        ]);
    }

    private function toInt($v): ?int
    {
        return is_null($v) || $v === '' ? null : (int) $v;
    }
}
