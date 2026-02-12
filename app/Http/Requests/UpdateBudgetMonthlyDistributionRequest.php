<?php

namespace App\Http\Requests;

use App\Models\AnnualBudget;
use App\Models\BudgetMonthlyDistribution;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetMonthlyDistributionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar políticas de autorización según roles
        // Por ahora, permitir a todos los usuarios autenticados
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'annual_budget_id' => [
                'required',
                'integer',
                'exists:annual_budgets,id',
            ],

            // Las distribuciones vienen como array: distributions[{distribution_id}]
            'distributions' => [
                'required',
                'array',
            ],

            'distributions.*.id' => [
                'required',
                'integer',
                'exists:budget_monthly_distributions,id',
            ],

            'distributions.*.assigned_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'annual_budget_id.required' => 'El presupuesto anual es obligatorio.',
            'annual_budget_id.exists' => 'El presupuesto anual seleccionado no existe.',

            'distributions.required' => 'Debe proporcionar las distribuciones mensuales.',
            'distributions.array' => 'El formato de las distribuciones es inválido.',

            'distributions.*.id.required' => 'El ID de la distribución es obligatorio.',
            'distributions.*.id.exists' => 'La distribución seleccionada no existe.',

            'distributions.*.assigned_amount.required' => 'El monto asignado es obligatorio.',
            'distributions.*.assigned_amount.numeric' => 'El monto debe ser un valor numérico.',
            'distributions.*.assigned_amount.min' => 'El monto no puede ser negativo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'annual_budget_id' => 'presupuesto anual',
            'distributions' => 'distribuciones mensuales',
        ];
    }

    /**
     * Configurar validaciones adicionales después de la validación base.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->failed()) {
                return;
            }

            // Validar que el presupuesto esté en estado PLANIFICACION
            $annualBudget = AnnualBudget::find($this->annual_budget_id);

            if (!$annualBudget) {
                $validator->errors()->add('annual_budget_id', 'El presupuesto anual no existe.');
                return;
            }

            if ($annualBudget->status !== 'PLANIFICACION') {
                $validator->errors()->add(
                    'annual_budget_id',
                    'Solo se pueden modificar distribuciones de presupuestos en estado PLANIFICACION.'
                );
                return;
            }

            // Validar que todas las distribuciones pertenezcan al presupuesto anual
            $distributionIds = collect($this->distributions)->pluck('id')->toArray();
            $validDistributions = BudgetMonthlyDistribution::whereIn('id', $distributionIds)
                ->where('annual_budget_id', $this->annual_budget_id)
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

            // Validar que el monto asignado sea >= consumido + comprometido
            foreach ($this->distributions as $index => $distData) {
                $distribution = BudgetMonthlyDistribution::find($distData['id']);

                if ($distribution) {
                    $minimumRequired = (float)$distribution->consumed_amount
                        + (float)$distribution->committed_amount;

                    $newAmount = (float)$distData['assigned_amount'];

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
        });
    }
}
