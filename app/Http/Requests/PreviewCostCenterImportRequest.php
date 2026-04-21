<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewCostCenterImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx'],
        ];
    }

    public function attributes(): array
    {
        return [
            'excel_file' => 'archivo Excel',
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Debes seleccionar un archivo Excel.',
            'excel_file.file' => 'El archivo seleccionado no es válido.',
            'excel_file.mimes' => 'El archivo debe ser un .xlsx generado con el layout del sistema.',
        ];
    }
}
