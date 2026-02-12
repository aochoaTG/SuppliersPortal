<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        $uniqueName = Rule::unique('categories', 'name');
        if ($category !== null) {
            $uniqueName->ignore($category);
        }

        return [
            'name' => ['required', 'string', 'max:80', $uniqueName],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe una categoría con ese nombre.',
        ];
    }
}
