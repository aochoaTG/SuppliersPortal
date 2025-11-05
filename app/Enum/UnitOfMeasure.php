<?php

namespace App\Enum;

/**
 * UnitOfMeasure (Unidad de Medida)
 * Catálogo de unidades de medida más utilizadas en requisiciones y compras.
 * Basado en estándares comunes en México y comercio internacional.
 */
enum UnitOfMeasure: string
{
    // Unidades de cantidad
    case PIEZA = 'PZA';
    case UNIDAD = 'UNI';
    case JUEGO = 'JGO';
    case PAR = 'PAR';
    case DOCENA = 'DOC';
    case CIENTO = 'CTO';
    case MILLAR = 'MLR';

    // Unidades de longitud
    case METRO = 'M';
    case CENTIMETRO = 'CM';
    case MILIMETRO = 'MM';
    case KILOMETRO = 'KM';
    case PULGADA = 'IN';
    case PIE = 'FT';
    case YARDA = 'YD';

    // Unidades de área
    case METRO_CUADRADO = 'M2';
    case CENTIMETRO_CUADRADO = 'CM2';
    case HECTAREA = 'HA';
    case PIE_CUADRADO = 'FT2';

    // Unidades de volumen
    case METRO_CUBICO = 'M3';
    case CENTIMETRO_CUBICO = 'CM3';
    case LITRO = 'L';
    case MILILITRO = 'ML';
    case GALON = 'GAL';
    case BARRIL = 'BBL';

    // Unidades de peso/masa
    case KILOGRAMO = 'KG';
    case GRAMO = 'G';
    case MILIGRAMO = 'MG';
    case TONELADA = 'TON';
    case LIBRA = 'LB';
    case ONZA = 'OZ';
    case QUINTAL = 'QTL';

    // Unidades de tiempo
    case HORA = 'HR';
    case DIA = 'DIA';
    case SEMANA = 'SEM';
    case MES = 'MES';
    case AÑO = 'AÑO';

    // Unidades de empaque
    case CAJA = 'CJA';
    case PAQUETE = 'PQT';
    case BOLSA = 'BLS';
    case COSTAL = 'CST';
    case SACO = 'SCO';
    case TARIMA = 'TAR';
    case PALLET = 'PLT';
    case CONTENEDOR = 'CNT';
    case TAMBOR = 'TMB';
    case ROLLO = 'RLL';
    case BOBINA = 'BOB';

    // Unidades eléctricas
    case KILOWATT = 'KW';
    case KILOWATT_HORA = 'KWH';
    case AMPERE = 'AMP';
    case VOLT = 'V';

    // Otras unidades comunes
    case SERVICIO = 'SRV';
    case VIAJE = 'VJE';
    case LOTE = 'LTE';
    case EVENTO = 'EVT';
    case LICENCIA = 'LIC';
    case ACTIVIDAD = 'ACT';

    /**
     * Devuelve una lista [valor => etiqueta] útil para selects.
     */
    public static function options(): array
    {
        return [
                // Cantidad
            self::PIEZA->value => 'PZA – Pieza',
            self::UNIDAD->value => 'UNI – Unidad',
            self::JUEGO->value => 'JGO – Juego',
            self::PAR->value => 'PAR – Par',
            self::DOCENA->value => 'DOC – Docena',
            self::CIENTO->value => 'CTO – Ciento',
            self::MILLAR->value => 'MLR – Millar',

                // Longitud
            self::METRO->value => 'M – Metro',
            self::CENTIMETRO->value => 'CM – Centímetro',
            self::MILIMETRO->value => 'MM – Milímetro',
            self::KILOMETRO->value => 'KM – Kilómetro',
            self::PULGADA->value => 'IN – Pulgada',
            self::PIE->value => 'FT – Pie',
            self::YARDA->value => 'YD – Yarda',

                // Área
            self::METRO_CUADRADO->value => 'M² – Metro cuadrado',
            self::CENTIMETRO_CUADRADO->value => 'CM² – Centímetro cuadrado',
            self::HECTAREA->value => 'HA – Hectárea',
            self::PIE_CUADRADO->value => 'FT² – Pie cuadrado',

                // Volumen
            self::METRO_CUBICO->value => 'M³ – Metro cúbico',
            self::CENTIMETRO_CUBICO->value => 'CM³ – Centímetro cúbico',
            self::LITRO->value => 'L – Litro',
            self::MILILITRO->value => 'ML – Mililitro',
            self::GALON->value => 'GAL – Galón',
            self::BARRIL->value => 'BBL – Barril',

                // Peso/Masa
            self::KILOGRAMO->value => 'KG – Kilogramo',
            self::GRAMO->value => 'G – Gramo',
            self::MILIGRAMO->value => 'MG – Miligramo',
            self::TONELADA->value => 'TON – Tonelada',
            self::LIBRA->value => 'LB – Libra',
            self::ONZA->value => 'OZ – Onza',
            self::QUINTAL->value => 'QTL – Quintal',

                // Tiempo
            self::HORA->value => 'HR – Hora',
            self::DIA->value => 'DIA – Día',
            self::SEMANA->value => 'SEM – Semana',
            self::MES->value => 'MES – Mes',
            self::AÑO->value => 'AÑO – Año',

                // Empaque
            self::CAJA->value => 'CJA – Caja',
            self::PAQUETE->value => 'PQT – Paquete',
            self::BOLSA->value => 'BLS – Bolsa',
            self::COSTAL->value => 'CST – Costal',
            self::SACO->value => 'SCO – Saco',
            self::TARIMA->value => 'TAR – Tarima',
            self::PALLET->value => 'PLT – Pallet',
            self::CONTENEDOR->value => 'CNT – Contenedor',
            self::TAMBOR->value => 'TMB – Tambor',
            self::ROLLO->value => 'RLL – Rollo',
            self::BOBINA->value => 'BOB – Bobina',

                // Eléctricas
            self::KILOWATT->value => 'KW – Kilowatt',
            self::KILOWATT_HORA->value => 'KWH – Kilowatt-hora',
            self::AMPERE->value => 'AMP – Ampere',
            self::VOLT->value => 'V – Volt',

                // Otras
            self::SERVICIO->value => 'SRV – Servicio',
            self::VIAJE->value => 'VJE – Viaje',
            self::LOTE->value => 'LTE – Lote',
            self::EVENTO->value => 'EVT – Evento',
            self::LICENCIA->value => 'LIC – Licencia',
            self::ACTIVIDAD->value => 'ACT – Actividad',
        ];
    }

    /**
     * Devuelve solo la descripción de la unidad sin el código.
     */
    public function label(): string
    {
        return match ($this) {
                // Cantidad
            self::PIEZA => 'Pieza',
            self::UNIDAD => 'Unidad',
            self::JUEGO => 'Juego',
            self::PAR => 'Par',
            self::DOCENA => 'Docena',
            self::CIENTO => 'Ciento',
            self::MILLAR => 'Millar',

                // Longitud
            self::METRO => 'Metro',
            self::CENTIMETRO => 'Centímetro',
            self::MILIMETRO => 'Milímetro',
            self::KILOMETRO => 'Kilómetro',
            self::PULGADA => 'Pulgada',
            self::PIE => 'Pie',
            self::YARDA => 'Yarda',

                // Área
            self::METRO_CUADRADO => 'Metro cuadrado',
            self::CENTIMETRO_CUADRADO => 'Centímetro cuadrado',
            self::HECTAREA => 'Hectárea',
            self::PIE_CUADRADO => 'Pie cuadrado',

                // Volumen
            self::METRO_CUBICO => 'Metro cúbico',
            self::CENTIMETRO_CUBICO => 'Centímetro cúbico',
            self::LITRO => 'Litro',
            self::MILILITRO => 'Mililitro',
            self::GALON => 'Galón',
            self::BARRIL => 'Barril',

                // Peso/Masa
            self::KILOGRAMO => 'Kilogramo',
            self::GRAMO => 'Gramo',
            self::MILIGRAMO => 'Miligramo',
            self::TONELADA => 'Tonelada',
            self::LIBRA => 'Libra',
            self::ONZA => 'Onza',
            self::QUINTAL => 'Quintal',

                // Tiempo
            self::HORA => 'Hora',
            self::DIA => 'Día',
            self::SEMANA => 'Semana',
            self::MES => 'Mes',
            self::AÑO => 'Año',

                // Empaque
            self::CAJA => 'Caja',
            self::PAQUETE => 'Paquete',
            self::BOLSA => 'Bolsa',
            self::COSTAL => 'Costal',
            self::SACO => 'Saco',
            self::TARIMA => 'Tarima',
            self::PALLET => 'Pallet',
            self::CONTENEDOR => 'Contenedor',
            self::TAMBOR => 'Tambor',
            self::ROLLO => 'Rollo',
            self::BOBINA => 'Bobina',

                // Eléctricas
            self::KILOWATT => 'Kilowatt',
            self::KILOWATT_HORA => 'Kilowatt-hora',
            self::AMPERE => 'Ampere',
            self::VOLT => 'Volt',

                // Otras
            self::SERVICIO => 'Servicio',
            self::VIAJE => 'Viaje',
            self::LOTE => 'Lote',
            self::EVENTO => 'Evento',
            self::LICENCIA => 'Licencia',
            self::ACTIVIDAD => 'Actividad',
        };
    }

    /**
     * Agrupa las unidades por categoría para mejor organización en UI.
     */
    public static function groupedOptions(): array
    {
        return [
            'Cantidad' => [
                self::PIEZA->value => 'PZA – Pieza',
                self::UNIDAD->value => 'UNI – Unidad',
                self::JUEGO->value => 'JGO – Juego',
                self::PAR->value => 'PAR – Par',
                self::DOCENA->value => 'DOC – Docena',
                self::CIENTO->value => 'CTO – Ciento',
                self::MILLAR->value => 'MLR – Millar',
            ],
            'Longitud' => [
                self::METRO->value => 'M – Metro',
                self::CENTIMETRO->value => 'CM – Centímetro',
                self::MILIMETRO->value => 'MM – Milímetro',
                self::KILOMETRO->value => 'KM – Kilómetro',
                self::PULGADA->value => 'IN – Pulgada',
                self::PIE->value => 'FT – Pie',
                self::YARDA->value => 'YD – Yarda',
            ],
            'Área' => [
                self::METRO_CUADRADO->value => 'M² – Metro cuadrado',
                self::CENTIMETRO_CUADRADO->value => 'CM² – Centímetro cuadrado',
                self::HECTAREA->value => 'HA – Hectárea',
                self::PIE_CUADRADO->value => 'FT² – Pie cuadrado',
            ],
            'Volumen' => [
                self::METRO_CUBICO->value => 'M³ – Metro cúbico',
                self::CENTIMETRO_CUBICO->value => 'CM³ – Centímetro cúbico',
                self::LITRO->value => 'L – Litro',
                self::MILILITRO->value => 'ML – Mililitro',
                self::GALON->value => 'GAL – Galón',
                self::BARRIL->value => 'BBL – Barril',
            ],
            'Peso/Masa' => [
                self::KILOGRAMO->value => 'KG – Kilogramo',
                self::GRAMO->value => 'G – Gramo',
                self::MILIGRAMO->value => 'MG – Miligramo',
                self::TONELADA->value => 'TON – Tonelada',
                self::LIBRA->value => 'LB – Libra',
                self::ONZA->value => 'OZ – Onza',
                self::QUINTAL->value => 'QTL – Quintal',
            ],
            'Tiempo' => [
                self::HORA->value => 'HR – Hora',
                self::DIA->value => 'DIA – Día',
                self::SEMANA->value => 'SEM – Semana',
                self::MES->value => 'MES – Mes',
                self::AÑO->value => 'AÑO – Año',
            ],
            'Empaque' => [
                self::CAJA->value => 'CJA – Caja',
                self::PAQUETE->value => 'PQT – Paquete',
                self::BOLSA->value => 'BLS – Bolsa',
                self::COSTAL->value => 'CST – Costal',
                self::SACO->value => 'SCO – Saco',
                self::TARIMA->value => 'TAR – Tarima',
                self::PALLET->value => 'PLT – Pallet',
                self::CONTENEDOR->value => 'CNT – Contenedor',
                self::TAMBOR->value => 'TMB – Tambor',
                self::ROLLO->value => 'RLL – Rollo',
                self::BOBINA->value => 'BOB – Bobina',
            ],
            'Eléctricas' => [
                self::KILOWATT->value => 'KW – Kilowatt',
                self::KILOWATT_HORA->value => 'KWH – Kilowatt-hora',
                self::AMPERE->value => 'AMP – Ampere',
                self::VOLT->value => 'V – Volt',
            ],
            'Servicios' => [
                self::SERVICIO->value => 'SRV – Servicio',
                self::VIAJE->value => 'VJE – Viaje',
                self::LOTE->value => 'LTE – Lote',
                self::EVENTO->value => 'EVT – Evento',
                self::LICENCIA->value => 'LIC – Licencia',
                self::ACTIVIDAD->value => 'ACT – Actividad',
            ],
        ];
    }
}
