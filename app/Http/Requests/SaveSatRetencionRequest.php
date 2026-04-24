<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSatRetencionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $retencion = $this->route('sat_retencion');

        $uniqueClave = Rule::unique('sat_retenciones', 'clave');
        if ($retencion !== null) {
            $uniqueClave->ignore($retencion);
        }

        return [
            'clave'                   => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9\-]{1,20}$/', $uniqueClave],
            'nombre'                  => ['required', 'string', 'max:100'],
            'impuesto'                => ['required', Rule::in(['ISR', 'IVA'])],
            'porcentaje'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'porcentaje_display'      => ['required', 'string', 'max:100'],
            'base_calculo'            => ['required', 'string', 'max:255'],
            'aplica_cuando'           => ['required', 'string', 'max:255'],
            'base_legal'              => ['required', 'string', 'max:100'],
            'descripcion'             => ['required', 'string', 'max:255'],
            'requiere_cfdi_retencion' => ['required', 'boolean'],
            'notas'                   => ['nullable', 'string'],
            'activo'                  => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'clave.required'              => 'La clave es obligatoria.',
            'clave.max'                   => 'La clave no puede superar 20 caracteres.',
            'clave.regex'                 => 'La clave solo puede contener letras mayúsculas, números y guiones.',
            'clave.unique'                => 'Ya existe una retención con esa clave.',
            'nombre.required'             => 'El nombre es obligatorio.',
            'nombre.max'                  => 'El nombre no puede superar 100 caracteres.',
            'impuesto.required'           => 'Selecciona el tipo de impuesto.',
            'impuesto.in'                 => 'El impuesto debe ser ISR o IVA.',
            'porcentaje.numeric'          => 'El porcentaje debe ser un número.',
            'porcentaje.min'              => 'El porcentaje no puede ser negativo.',
            'porcentaje.max'              => 'El porcentaje no puede superar 100.',
            'porcentaje_display.required' => 'El texto de porcentaje es obligatorio.',
            'base_calculo.required'       => 'La base de cálculo es obligatoria.',
            'aplica_cuando.required'      => 'El campo "aplica cuando" es obligatorio.',
            'base_legal.required'         => 'La base legal es obligatoria.',
            'descripcion.required'        => 'La descripción es obligatoria.',
        ];
    }
}
