<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnualBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Por ahora permitir
    }

    public function rules(): array
    {
        $budgetId = $this->route('annual_budget')->id;

        return [
            'cost_center_id' => [
                'required',
                'integer',
                'exists:cost_centers,id',
                function ($attribute, $value, $fail) use ($budgetId) {
                    // No permitir cambiar el centro de costo una vez creado
                    $currentBudget = \App\Models\AnnualBudget::find($budgetId);
                    if ($currentBudget && $currentBudget->cost_center_id != $value) {
                        $fail('No se puede cambiar el centro de costo de un presupuesto existente.');
                    }
                },
            ],
            'fiscal_year' => [
                'required',
                'integer',
                'min:' . (date('Y') - 1),
                'max:' . (date('Y') + 10),
                function ($attribute, $value, $fail) use ($budgetId) {
                    // No permitir cambiar el año fiscal una vez creado
                    $currentBudget = \App\Models\AnnualBudget::find($budgetId);
                    if ($currentBudget && $currentBudget->fiscal_year != $value) {
                        $fail('No se puede cambiar el año fiscal de un presupuesto existente.');
                    }
                },
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
}
