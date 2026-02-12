<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reporter_name'   => ['required','string','max:120'],
            'reporter_email'  => ['required','email','max:190'],
            'module'          => ['required','string','max:190'],
            'severity'        => ['required','in:bloqueante,alta,media,baja'],
            'title'           => ['required','string','max:120'],
            'steps'           => ['required','string'],
            'expected'        => ['required','string'],
            'actual'          => ['required','string'],
            'reproducibility' => ['required','in:siempre,frecuente,intermitente,raro'],
            'impact'          => ['required','in:todos,equipo,usuario'],
            'happened_at'     => ['nullable','date'],
            'can_contact'     => ['accepted'],
            'current_url'     => ['nullable','string','max:500'],
            'user_agent'      => ['nullable','string','max:1000'],

            // Solo 1 imagen opcional, máx. 5MB
            'image'           => ['nullable', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,application/pdf'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reporter_name' => 'nombre',
            'reporter_email'=> 'email',
            'happened_at'   => 'fecha/hora',
            'image'         => 'imagen',
        ];
    }
}
