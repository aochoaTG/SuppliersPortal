<?php

namespace App\Http\Requests;

use App\Models\AnnualBudget;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetMonthlyDistributionRequest extends FormRequest
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

            // Las distribuciones vienen como array: distributions[{category_id}][{month}]
            'distributions' => [
                'required',
                'array',
            ],

            'distributions.*' => [
                'required',
                'array',
                'size:12', // Exactamente 12 meses
            ],

            'distributions.*.*' => [
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

            'distributions.*.array' => 'Cada categoría debe tener 12 meses de distribución.',
            'distributions.*.size' => 'Cada categoría debe tener exactamente 12 meses.',

            'distributions.*.*.required' => 'Todos los montos mensuales son obligatorios.',
            'distributions.*.*.numeric' => 'Los montos deben ser valores numéricos.',
            'distributions.*.*.min' => 'Los montos no pueden ser negativos.',
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
                    'Solo se pueden crear distribuciones para presupuestos en estado PLANIFICACION.'
                );
                return;
            }

            // Validar que las categorías existan
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

            // Validar que los meses estén en rango 1-12
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
        });
    }

    /**
     * Preparar datos para validación.
     * Convertir las claves a enteros si vienen como strings.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('distributions')) {
            $distributions = [];

            foreach ($this->distributions as $categoryId => $months) {
                $distributions[(int)$categoryId] = [];

                foreach ($months as $month => $amount) {
                    $distributions[(int)$categoryId][(int)$month] = $amount;
                }
            }

            $this->merge([
                'distributions' => $distributions,
            ]);
        }
    }
}
