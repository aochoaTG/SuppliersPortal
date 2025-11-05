<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enum\UnitOfMeasure;
use App\Enum\Currency;

class StoreRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyValues = array_keys(Currency::options());

        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'cost_center_id' => [
                'required',
                'exists:cost_centers,id',
                Rule::exists('cost_centers', 'id')->where('company_id', $this->company_id),
            ],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'currency_code' => ['required', 'string', 'in:' . implode(',', $currencyValues)],
            'required_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'justification' => ['nullable', 'string'],

            // Partidas
            'items' => ['required', 'array', 'min:1'],
            'items.*.line_number' => ['nullable', 'integer', 'min:1'],
            'items.*.item_category' => ['nullable', 'string', 'max:120'],
            'items.*.product_code' => ['nullable', 'string', 'max:80'],

            // 👇 Cambiado a FK + validación anti-EFOS
            'items.*.suggested_vendor_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where(function ($q) {
                    $q->whereNotExists(function ($sub) {
                        $sub->from('sat_efos_69b as e')
                            ->whereColumn('e.rfc', 'suppliers.rfc')
                            ->whereIn('e.situation', ['Definitivo', 'Presunto']);
                    });
                }),
            ],

            'items.*.notes' => ['nullable', 'string'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', Rule::enum(UnitOfMeasure::class)],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],

            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'cost_center_id.exists' => 'El Centro de Costo no pertenece a la Compañía seleccionada.',
            'items.*.suggested_vendor_id.exists' => 'El proveedor seleccionado no es válido o está listado como EFOS.',
        ];
    }
}
