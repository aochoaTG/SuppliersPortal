<?php

namespace App\Http\Requests;

use App\Models\AnnualBudget;
use App\Models\BudgetMonthlyDistribution;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;

class SaveBudgetMonthlyDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('annual_budget') !== null;

        $rules = [
            'annual_budget_id' => [
                'required',
                'integer',
                'exists:annual_budgets,id',
            ],
            'distributions' => [
                'required',
                'array',
            ],
        ];

        if ($isUpdate) {
            $rules['distributions.*.id'] = [
                'required',
                'integer',
                'exists:budget_monthly_distributions,id',
            ];
            $rules['distributions.*.assigned_amount'] = [
                'required',
                'numeric',
                'min:0',
            ];
        } else {
            $rules['distributions.*'] = [
                'required',
                'array',
                'size:12',
            ];
            $rules['distributions.*.*'] = [
                'required',
                'numeric',
                'min:0',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'annual_budget_id.required' => 'El presupuesto anual es obligatorio.',
            'annual_budget_id.exists' => 'El presupuesto anual seleccionado no existe.',

            'distributions.required' => 'Debe proporcionar las distribuciones mensuales.',
            'distributions.array' => 'El formato de las distribuciones es inválido.',

            // Store
            'distributions.*.array' => 'Cada categoría debe tener 12 meses de distribución.',
            'distributions.*.size' => 'Cada categoría debe tener exactamente 12 meses.',
            'distributions.*.*.required' => 'Todos los montos mensuales son obligatorios.',
            'distributions.*.*.numeric' => 'Los montos deben ser valores numéricos.',
            'distributions.*.*.min' => 'Los montos no pueden ser negativos.',

            // Update
            'distributions.*.id.required' => 'El ID de la distribución es obligatorio.',
            'distributions.*.id.exists' => 'La distribución seleccionada no existe.',
            'distributions.*.assigned_amount.required' => 'El monto asignado es obligatorio.',
            'distributions.*.assigned_amount.numeric' => 'El monto debe ser un valor numérico.',
            'distributions.*.assigned_amount.min' => 'El monto no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'annual_budget_id' => 'presupuesto anual',
            'distributions' => 'distribuciones mensuales',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->failed()) {
                return;
            }

            $annualBudget = AnnualBudget::find($this->annual_budget_id);

            if (!$annualBudget) {
                $validator->errors()->add('annual_budget_id', 'El presupuesto anual no existe.');
                return;
            }

            if ($annualBudget->status !== 'PLANIFICACION') {
                $action = $this->route('annual_budget') !== null ? 'modificar' : 'crear';
                $validator->errors()->add(
                    'annual_budget_id',
                    "Solo se pueden {$action} distribuciones para presupuestos en estado PLANIFICACION."
                );
                return;
            }

            if ($this->route('annual_budget') !== null) {
                $this->validateUpdate($validator, $annualBudget);
            } else {
                $this->validateStore($validator);
            }
        });
    }

    protected function validateStore($validator): void
    {
        $categoryIds = array_keys($this->distributions);
        $existingCategories = ExpenseCategory::whereIn('id', $categoryIds)->pluck('id')->toArray();

        foreach ($categoryIds as $categoryId) {
            if (!in_array($categoryId, $existingCategories)) {
                $validator->errors()->add(
                    "distributions.{$categoryId}",
                    "La categoría de gasto con ID {$categoryId} no existe."
                );
            }
        }

        foreach ($this->distributions as $categoryId => $months) {
            foreach ($months as $month => $amount) {
                if ($month < 1 || $month > 12) {
                    $validator->errors()->add(
                        "distributions.{$categoryId}.{$month}",
                        "El mes debe estar entre 1 y 12."
                    );
                }
            }
        }
    }

    protected function validateUpdate($validator, AnnualBudget $annualBudget): void
    {
        $distributionIds = collect($this->distributions)->pluck('id')->toArray();
        $validDistributions = BudgetMonthlyDistribution::whereIn('id', $distributionIds)
            ->where('annual_budget_id', $annualBudget->id)
            ->pluck('id')
            ->toArray();

        foreach ($distributionIds as $distId) {
            if (!in_array($distId, $validDistributions)) {
                $validator->errors()->add(
                    "distributions.{$distId}",
                    "La distribución con ID {$distId} no pertenece al presupuesto anual especificado."
                );
            }
        }

        foreach ($this->distributions as $index => $distData) {
            $distribution = BudgetMonthlyDistribution::find($distData['id']);

            if ($distribution) {
                $minimumRequired = (float) $distribution->consumed_amount
                    + (float) $distribution->committed_amount;

                $newAmount = (float) $distData['assigned_amount'];

                if ($newAmount < $minimumRequired) {
                    $validator->errors()->add(
                        "distributions.{$index}.assigned_amount",
                        "El monto asignado no puede ser menor a la suma de consumido (" .
                            number_format($distribution->consumed_amount, 2) .
                            ") + comprometido (" .
                            number_format($distribution->committed_amount, 2) .
                            "). Mínimo requerido: $" . number_format($minimumRequired, 2)
                    );
                }
            }
        }
    }

    protected function prepareForValidation(): void
    {
        // Solo aplica para store (matriz category => months)
        if ($this->route('annual_budget') === null && $this->has('distributions')) {
            $distributions = [];

            foreach ($this->distributions as $categoryId => $months) {
                $distributions[(int) $categoryId] = [];

                foreach ($months as $month => $amount) {
                    $distributions[(int) $categoryId][(int) $month] = $amount;
                }
            }

            $this->merge([
                'distributions' => $distributions,
            ]);
        }
    }
}
