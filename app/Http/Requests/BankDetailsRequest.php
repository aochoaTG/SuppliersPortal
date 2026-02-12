<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta si usas policies
    }

    public function prepareForValidation(): void
    {
        $input = $this->all();

        foreach (['bank_name','bank_address','account_number','clabe','currency','swift_bic','iban','aba_routing','us_bank_name'] as $k) {
            if (array_key_exists($k, $input) && is_string($input[$k])) {
                $input[$k] = trim($input[$k]);
                if ($input[$k] === '') $input[$k] = null;
            }
        }

        // Normalizaciones específicas
        if (!empty($input['currency']))     $input['currency']   = strtoupper($input['currency']);
        if (!empty($input['swift_bic']))    $input['swift_bic']  = strtoupper($input['swift_bic']);
        if (!empty($input['iban']))         $input['iban']       = strtoupper(str_replace([' ', '-'], '', $input['iban']));
        if (!empty($input['clabe']))        $input['clabe']      = preg_replace('/\D+/', '', $input['clabe']);
        if (!empty($input['aba_routing']))  $input['aba_routing']= preg_replace('/\D+/', '', $input['aba_routing']);

        $this->replace($input);
    }

    public function rules(): array
    {
        return [
            // usa "sometimes" para parches parciales
            'bank_name'      => ['sometimes','nullable','string','max:100'],
            'bank_address'   => ['sometimes','nullable','string','max:255'],
            'account_number' => ['sometimes','nullable','string','max:20'],
            'clabe'          => ['sometimes','nullable','digits:18'],
            'currency'       => ['sometimes','nullable','string','size:3'],

            // Internacionales
            'swift_bic'      => ['sometimes','nullable','regex:/^[A-Z0-9]{8}([A-Z0-9]{3})?$/i'],
            'iban'           => ['sometimes','nullable','regex:/^[A-Z0-9]{15,34}$/i'],
            'aba_routing'    => ['sometimes','nullable','digits:9'],
            'us_bank_name' => ['sometimes','nullable','string','max:100'],

        ];
    }

    public function attributes(): array
    {
        return [
            'bank_name'      => 'nombre del banco',
            'bank_address'   => 'dirección del banco',
            'account_number' => 'número de cuenta',
            'clabe'          => 'CLABE',
            'currency'       => 'moneda',
            'swift_bic'      => 'SWIFT/BIC',
            'iban'           => 'IBAN',
            'aba_routing'    => 'ABA routing',
            'us_bank_name' => 'banco en EE.UU.',

        ];
    }
}
