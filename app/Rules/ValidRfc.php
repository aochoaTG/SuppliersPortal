<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regla para validar RFC con formato oficial.
 */
class ValidRfc implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rfc = strtoupper(trim((string)$value));

        // Expresión regular oficial SAT (simplificada)
        $pattern = '/^([A-ZÑ&]{3,4})(\d{6})([A-Z0-9]{3})$/';

        if (!preg_match($pattern, $rfc)) {
            $fail("El RFC $rfc no tiene un formato válido.");
        }
    }
}
