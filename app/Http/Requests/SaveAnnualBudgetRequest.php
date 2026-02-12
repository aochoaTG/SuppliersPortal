<?php

namespace App\Http\Requests;

use App\Models\AnnualBudget;
use App\Models\CostCenter;
use Illuminate\Foundation\Http\FormRequest;

class SaveAnnualBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $budget = $this->route('annual_budget');
        $isUpdate = $budget !== null;

        return [
            'cost_center_id' => [
                'required',
                'integer',
                'exists:cost_centers,id',
                function ($attribute, $value, $fail) use ($budget, $isUpdate) {
                    if ($isUpdate) {
                        if ($budget->cost_center_id != $value) {
                            $fail('No se puede cambiar el centro de costo de un presupuesto existente.');
                        }
                        return;
                    }

                    $costCenter = CostCenter::find($value);
                    if ($costCenter && $costCenter->budget_type !== 'ANNUAL') {
                        $fail('El centro de costo debe ser de tipo ANNUAL.');
                    }
                    if ($costCenter && $costCenter->status !== 'ACTIVO') {
                        $fail('El centro de costo debe estar ACTIVO.');
                    }

                    if ($costCenter && $this->fiscal_year) {
                        $exists = AnnualBudget::where('cost_center_id', $value)
                            ->where('fiscal_year', $this->fiscal_year)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($exists) {
                            $fail('Ya existe un presupuesto para este centro de costo en el año ' . $this->fiscal_year);
                        }
                    }
                },
            ],
            'fiscal_year' => [
                'required',
                'integer',
                'min:' . (date('Y') - 1),
                'max:' . (date('Y') + 10),
                ...($isUpdate ? [
                    function ($attribute, $value, $fail) use ($budget) {
                        if ($budget->fiscal_year != $value) {
                            $fail('No se puede cambiar el año fiscal de un presupuesto existente.');
                        }
                    },
                ] : []),
            ],
            'total_annual_amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'cost_center_id' => 'Centro de Costo',
            'fiscal_year' => 'Año Fiscal',
            'total_annual_amount' => 'Monto Total Anual',
        ];
    }

    public function messages(): array
    {
        return [
            'cost_center_id.required' => 'El centro de costo es obligatorio.',
            'cost_center_id.exists' => 'El centro de costo seleccionado no existe.',
            'fiscal_year.required' => 'El año fiscal es obligatorio.',
            'fiscal_year.min' => 'El año fiscal debe ser igual o mayor al año anterior.',
            'total_annual_amount.required' => 'El monto total anual es obligatorio.',
            'total_annual_amount.min' => 'El monto total anual debe ser mayor a 0.',
        ];
    }
}
