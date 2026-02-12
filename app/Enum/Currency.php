<?php

namespace App\Enum;

enum Currency: string
{
    case MXN = 'MXN';
    case USD = 'USD';
    case EUR = 'EUR';

    /**
     * Devuelve una lista [valor => etiqueta] útil para selects.
     */
    public static function options(): array
    {
        return [
            self::MXN->value => 'MXN - Peso mexicano',
            self::USD->value => 'USD - Dólar estadounidense',
            self::EUR->value => 'EUR - Euro',
        ];
    }

    /**
     * Retorna la moneda por defecto del sistema
     */
    public static function default(): string
    {
        return self::MXN->value;
    }
}