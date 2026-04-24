<?php

namespace App\Enum;

enum PurchaseType: string
{
    case Operativo = 'Gasto Operativo';
    case Staff = 'Gasto Staff';
    case Corporativo = 'Gasto Corporativo';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
