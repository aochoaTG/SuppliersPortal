<?php

namespace App\Enum;

enum SupplierClassification: string
{
    case GENERAL = 'GENERAL';
    case REPSE = 'REPSE';
    case CONSTRUCCION = 'CONSTRUCCION';
    case COMBUSTIBLE = 'COMBUSTIBLE';

    /**
     * Devuelve una lista [valor => etiqueta] util para selects.
     */
    public static function options(): array
    {
        return [
            self::GENERAL->value => 'Generales',
            self::REPSE->value => 'REPSE',
            self::CONSTRUCCION->value => 'Construccion',
            self::COMBUSTIBLE->value => 'Combustible',
        ];
    }

    /**
     * Retorna la clasificacion por defecto del sistema.
     */
    public static function default(): string
    {
        return self::GENERAL->value;
    }
}
