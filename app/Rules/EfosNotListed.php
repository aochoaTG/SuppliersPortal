<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Regla personalizada de validación para RFC contra la lista EFOS 69-B.
 *
 * Esta regla revisa si el RFC aparece en la tabla `sat_efos_69b`
 * con situación "PRESUNTO" o "DEFINITIVO". En ese caso, la validación falla.
 *
 * 🚀 Uso en un FormRequest o controlador:
 *   'rfc' => ['required', new EfosNotListed]
 */
class EfosNotListed implements ValidationRule
{
    /**
     * Ejecuta la validación.
     *
     * @param  string  $attribute  Nombre del campo (ej. "rfc")
     * @param  mixed   $value      Valor recibido (ej. "ABC123...")
     * @param  Closure $fail       Callback que se llama si la validación falla
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Normalizamos el RFC: en mayúsculas y sin espacios
        $rfc = Str::upper(trim((string) $value));

        // Consultamos la tabla EFOS
        $listed = DB::table('sat_efos_69b')
            ->where('rfc', $rfc) // Coincidencia exacta del RFC
            ->where(function ($q) {
                // Buscamos en "situation" si contiene PRESUNTO o DEFINITIVO
                $q->whereRaw("UPPER(situation) LIKE '%PRESUNTO%'")
                  ->orWhereRaw("UPPER(situation) LIKE '%DEFINITIVO%'");
            })
            ->exists();

        // Si el RFC está listado, marcamos error
        if ($listed) {
            $fail("El RFC $rfc se encuentra en la lista EFOS 69-B como PRESUNTO o DEFINITIVO. No es posible continuar con el registro.");
        }
    }
}
