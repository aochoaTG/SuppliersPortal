<?php

namespace App\Enum;

/**
 * Currency
 * Catálogo simple de monedas soportadas en el portal.
 * Suma/quita las que apliquen. El valor del enum es el código ISO-4217.
 */
enum Currency: string
{
    case MXN = 'MXN';
    case USD = 'USD';
    case EUR = 'EUR';
    // Agrega otras si aplica: case CAD = 'CAD';

    /**
     * Devuelve una lista [valor => etiqueta] útil para selects.
     */
    public static function options(): array
    {
        return [
            self::MXN->value => 'MXN – Peso mexicano',
            self::USD->value => 'USD – Dólar estadounidense',
            self::EUR->value => 'EUR – Euro',
        ];
    }
}
