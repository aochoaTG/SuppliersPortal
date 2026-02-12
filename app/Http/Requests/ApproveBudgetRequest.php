<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo Director General puede aprobar
        // return auth()->user()->can('approve', $this->route('annual_budget'));
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'notes' => 'Notas de aprobación',
        ];
    }
}
