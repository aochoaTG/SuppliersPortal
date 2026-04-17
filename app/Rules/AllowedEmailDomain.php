<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class AllowedEmailDomain implements ValidationRule
{
    private const ALLOWED_DOMAINS = [
        'petrotal.com.mx',
        'totalgasolineras.com',
        'totalgasonline.mx',
        'rendilitrosjuarez.com',
        'prlsc.com',
        'rapigas.com',
        'aquacarwashclub.com',
        'aquacarclub.com',
        'energmedia.com',
        'petrodigitalmedia.com',
        'totaldigitalmedia.com',
        'fuelmedia.com.mx',
        'petrodigital.com.mx',
        'petromedia.com.mx',
        'totalmedia.mx',
        'masquegas.com',
        'gasolucion.com',
        'totalgasonline.com',
        'totalgasonline.net',
        'totalgasonline-ags.com',
        'totalgas.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(Str::after((string) $value, '@'));

        if (!in_array($domain, self::ALLOWED_DOMAINS, true)) {
            $fail('El dominio del correo no está permitido. Solo se aceptan correos corporativos del grupo TotalGas.');
        }
    }
}
