<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnualBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Por ahora permitir
    }

    public function rules(): array
    {
        return [
            'cost_center_id' => [
                'required',
                'integer',
                'exists:cost_centers,id',
                // Validar que sea centro ANNUAL
                function ($attribute, $value, $fail) {
                    $costCenter = \App\Models\CostCenter::find($value);
                    if ($costCenter && $costCenter->budget_type !== 'ANNUAL') {
                        $fail('El centro de costo debe ser de tipo ANNUAL.');
                    }
                    if ($costCenter && $costCenter->status !== 'ACTIVO') {
                        $fail('El centro de costo debe estar ACTIVO.');
                    }

                    // ✅ VALIDAR QUE NO EXISTA PRESUPUESTO DUPLICADO
                    if ($costCenter && $this->fiscal_year) {
                        $exists = \App\Models\AnnualBudget::where('cost_center_id', $value)
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
