<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enum\UnitOfMeasure;

class UpdateRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza algunos campos antes de validar (opcional pero útil).
     */
    protected function prepareForValidation(): void
    {
        // Si el front envía strings, convertimos a números donde corresponde
        $this->merge([
            'company_id' => $this->input('company_id') !== null ? (int) $this->input('company_id') : null,
            'cost_center_id' => $this->input('cost_center_id') !== null ? (int) $this->input('cost_center_id') : null,
            'department_id' => $this->input('department_id') !== null ? (int) $this->input('department_id') : null,
            'fiscal_year' => $this->input('fiscal_year') !== null ? (int) $this->input('fiscal_year') : null,
        ]);

        if (is_array($this->items)) {
            $items = collect($this->items)->map(function ($it) {
                $it['quantity'] = isset($it['quantity']) ? (float) $it['quantity'] : null;
                $it['unit_price'] = isset($it['unit_price']) ? (float) $it['unit_price'] : null;
                $it['tax_rate'] = isset($it['tax_rate']) ? (float) $it['tax_rate'] : null;
                return $it;
            })->all();
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        $currencyValues = array_keys(\App\Enum\Currency::options());

        return [
            // Acción del footer: save | submit_review
            'action' => ['nullable', 'string', Rule::in(['save', 'submit_review'])],

            'company_id' => ['required', 'integer', 'exists:companies,id'],

            'cost_center_id' => [
                'required',
                'integer',
                // Debe existir y pertenecer a la compañía seleccionada
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

            'items.*.id' => ['nullable', 'integer', 'exists:requisition_items,id'],
            'items.*.line_number' => ['nullable', 'integer', 'min:1', 'distinct'],
            'items.*.item_category' => ['nullable', 'string', 'max:120'],
            'items.*.product_code' => ['nullable', 'string', 'max:80'],

            // FK + anti-EFOS
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

            // 🔒 IMPORTANTE: no se permite cambiar a estados que NO son del update
            // (aprobaciones/rechazos/cancelaciones se hacen vía RequisitionWorkflowController)
            'status' => [
                'nullable',
                Rule::notIn(['approved', 'rejected', 'cancelled', 'budget_exception', 'invoiced', 'received', 'closed', 'partially_received', 'partially_invoiced']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'Acción inválida.',

            'cost_center_id.exists' => 'El Centro de Costo no pertenece a la Compañía seleccionada.',
            'items.*.suggested_vendor_id.exists' => 'El proveedor seleccionado no es válido o está listado como EFOS.',

            'status.not_in' => 'No puedes cambiar la requisición a ese estado desde esta pantalla. Usa las acciones de flujo.',
        ];
    }

    /**
     * Reglas post-validación:
     * - Evita que intenten forzar "approved" desde el form.
     * - Si se envía a revisión, puedes validar algún campo adicional si lo necesitas.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // 🚫 Blindaje adicional: si alguien manda status=approved desde el form, lo bloqueamos explícitamente.
            $nextStatus = (string) $this->input('status', '');
            if (in_array($nextStatus, ['approved', 'rejected', 'cancelled', 'budget_exception', 'invoiced', 'received', 'closed', 'partially_received', 'partially_invoiced'], true)) {
                $v->errors()->add('status', 'Las aprobaciones/rechazos se realizan desde la bandeja correspondiente.');
            }

            // ✅ Si la acción es "submit_review", puedes exigir mínima justificación o fecha requerida (opcional)
            if ($this->input('action') === 'submit_review') {
                // Ejemplo opcional:
                // if (!$this->filled('required_date')) {
                //     $v->errors()->add('required_date', 'Para enviar a revisión, captura la fecha requerida.');
                // }
            }

            // (Opcional) Verificación soft de total > 0 con los datos de las partidas
            if (is_array($this->items)) {
                $sum = 0.0;
                foreach ($this->items as $it) {
                    $qty = (float) ($it['quantity'] ?? 0);
                    $price = (float) ($it['unit_price'] ?? 0);
                    $tax = (float) ($it['tax_rate'] ?? 0);
                    $subtotal = $qty * $price;
                    $sum += $subtotal + ($subtotal * ($tax / 100));
                }
                if ($sum <= 0) {
                    $v->errors()->add('items', 'El total calculado debe ser mayor a cero.');
                }
            }
        });
    }
}
