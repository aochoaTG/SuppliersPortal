<?php

namespace App\Enum;

/**
 * Estados del Catálogo de Productos y Servicios
 * según ESPECIFICACIONES_TECNICAS_SISTEMA_CONTROL_PRESUPUESTAL.md
 * Sección 5.1: RF-CAT-002
 */
enum ProductServiceStatus: string
{
    case PENDING = 'PENDING';           // Pendiente de aprobación
    case ACTIVE = 'ACTIVE';             // Activo (disponible para requisiciones)
    case INACTIVE = 'INACTIVE';         // Inactivo (desactivado)
    case REJECTED = 'REJECTED';         // Rechazado

    /**
     * Devuelve array asociativo [valor => etiqueta] para selects.
     */
    public static function options(): array
    {
        return [
            self::PENDING->value => 'Pendiente de Aprobación',
            self::ACTIVE->value => 'Activo',
            self::INACTIVE->value => 'Inactivo',
            self::REJECTED->value => 'Rechazado',
        ];
    }

    /**
     * Clase CSS de Bootstrap para badge según el estado.
     */
    public static function badgeClass(self $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::REJECTED => 'danger',
        };
    }

    /**
     * Etiqueta legible del estado.
     */
    public function label(): string
    {
        return self::options()[$this->value];
    }
}
