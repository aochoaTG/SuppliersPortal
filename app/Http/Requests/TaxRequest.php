<?php

// app/Http/Requests/TaxRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta con Policies si lo necesitas
    }

    public function rules(): array
    {
        $id = $this->route('tax')?->id;

        return [
            'name'         => ['required','string','max:100','unique:taxes,name'.($id ? ",$id" : '')],
            'rate_percent' => ['required','numeric','min:0','max:100'],
            'is_active'    => ['nullable','boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'         => 'nombre',
            'rate_percent' => 'tasa (%)',
            'is_active'    => 'activo',
        ];
    }
}
