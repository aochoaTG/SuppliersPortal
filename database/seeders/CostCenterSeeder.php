<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CostCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * CostCenterSeeder
 *
 * Carga masiva de Centros de Costo con categorías provistas por el usuario.
 * - Resuelve category_id por NOMBRE de categoría (no asume IDs fijos).
 * - Genera 'code' único y estable: <ABBR_CAT>_<SLUG_ASCII_MAYUS> (p.ej. EST_HERMANOS_ESCOBAR).
 * - Si ya existe 'code', no duplica (firstOrCreate).
 */
class CostCenterSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1) Asegurarnos de que las categorías existan.
         *    Si ya corriste CategorySeeder (Fase 1), esto solo valida que estén.
         */
        $categoryNames = [
            'ADMINISTRACION',
            'ENPROYECTO',
            'STAFF',
            'CORPORATIVO',
            'OPERACIONES',
            'ESTACIONES',
        ];

        foreach ($categoryNames as $catName) {
            Category::firstOrCreate(['name' => $catName], [
                'description' => $catName . ' (autocreada por seeder de centros)',
                'is_active' => true,
            ]);
        }

        /**
         * 2) Mapa nombre_de_categoría => id (para guardar FK sin asumir numeración).
         */
        $catId = Category::query()
            ->whereIn('name', $categoryNames)
            ->pluck('id', 'name')
            ->toArray();

        /**
         * 3) Abreviaturas por categoría (para prefijar el 'code' y evitar colisiones entre categorías).
         */
        $abbr = [
            'ADMINISTRACION' => 'ADM',
            'ENPROYECTO' => 'ENP',
            'STAFF' => 'STF',
            'CORPORATIVO' => 'COR',
            'OPERACIONES' => 'OPE',
            'ESTACIONES' => 'EST',
        ];

        /**
         * 4) Lista completa (categoría, nombre).
         *    Nota: el 'code' se genera automáticamente a partir del nombre.
         */
        $rows = [
            // ADMINISTRACION
            ['category' => 'ADMINISTRACION', 'name' => 'CC BALANCE'],

            // CORPORATIVO
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO'],
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO EMPRESA'],
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO J'],
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO L'],

            // ENPROYECTO
            ['category' => 'ENPROYECTO', 'name' => 'ADUANA'],
            ['category' => 'ENPROYECTO', 'name' => 'ALUMBRADO PÚBLICO'],
            ['category' => 'ENPROYECTO', 'name' => 'ASSESMENT'],
            ['category' => 'ENPROYECTO', 'name' => 'AUTOLAVADO'],
            ['category' => 'ENPROYECTO', 'name' => 'BAJIO'],
            ['category' => 'ENPROYECTO', 'name' => 'BODEGA DE ADITIVO'],
            ['category' => 'ENPROYECTO', 'name' => 'CASA DE CAMBIO'],
            ['category' => 'ENPROYECTO', 'name' => 'CC ZARAGOZA'],
            ['category' => 'ENPROYECTO', 'name' => 'COMPRA ES EN EDO CHIHUAHUA'],
            ['category' => 'ENPROYECTO', 'name' => 'ESTACIÓN TIJUANA'],
            ['category' => 'ENPROYECTO', 'name' => 'ESTANDARIZACIÓN DE PROCESOS'],
            ['category' => 'ENPROYECTO', 'name' => 'EXPANSION 22-26'],
            ['category' => 'ENPROYECTO', 'name' => 'FACTURACION 4.0'],
            ['category' => 'ENPROYECTO', 'name' => 'GASTO PETROTAL'],
            ['category' => 'ENPROYECTO', 'name' => 'GASOMEX'],
            ['category' => 'ENPROYECTO', 'name' => 'HERMANOS-ESCOBAR'],
            ['category' => 'ENPROYECTO', 'name' => 'IMPLEMENTACION ERPS'],
            ['category' => 'ENPROYECTO', 'name' => 'LABORATORIO ANALISIS COMBUSTIBLE'],
            ['category' => 'ENPROYECTO', 'name' => 'MAS QUE GAS'],
            ['category' => 'ENPROYECTO', 'name' => 'NFC'],
            ['category' => 'ENPROYECTO', 'name' => 'PANELES SOLARES'],
            ['category' => 'ENPROYECTO', 'name' => 'PRAXEDIS'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO 1G'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO 6947'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO CAMINO REAL'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO EJE VIAL'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO EL CASTAÑO'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO MIGUEL DE LA MADRIR'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO OFICINAS'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO SAN ISIDRO'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTO VILLA AHUMADA'],
            ['category' => 'ENPROYECTO', 'name' => 'PROYECTOS, PLANEACION ESTRATEGICA Y MEJORAS A OPERACIONES'],
            ['category' => 'ENPROYECTO', 'name' => 'SAN MIGUEL DE ALLENDE'],
            ['category' => 'ENPROYECTO', 'name' => 'SANTIAGO TRONCOSO'],
            ['category' => 'ENPROYECTO', 'name' => 'SISTEMA DE DOCUMENTACION Y PROCESOS WEB'],
            ['category' => 'ENPROYECTO', 'name' => 'TECNOLOGIA DE REDES Y COMUNICACION'],
            ['category' => 'ENPROYECTO', 'name' => 'TOP TIER'],
            ['category' => 'ENPROYECTO', 'name' => 'TRAVEL CENTER PREOPERACIONES'],
            ['category' => 'ENPROYECTO', 'name' => 'VALERAS PROYECTO'],
            ['category' => 'ENPROYECTO', 'name' => 'VALLAS PUBLICITARIAS'],

            // ESTACIONES
            ['category' => 'ESTACIONES', 'name' => 'AERONAUTICA'],
            ['category' => 'ESTACIONES', 'name' => 'ANAPRA'],
            ['category' => 'ESTACIONES', 'name' => 'AZTECAS'],
            ['category' => 'ESTACIONES', 'name' => 'CUSTODIA'],
            ['category' => 'ESTACIONES', 'name' => 'DELICIAS'],
            ['category' => 'ESTACIONES', 'name' => 'ELECTROLUX'],
            ['category' => 'ESTACIONES', 'name' => 'GEMELA CHICA'],
            ['category' => 'ESTACIONES', 'name' => 'GEMELA GRANDE'],
            ['category' => 'ESTACIONES', 'name' => 'HERMANOS ESCOBAR'],
            ['category' => 'ESTACIONES', 'name' => 'INDEPENDENCIA'],
            ['category' => 'ESTACIONES', 'name' => 'LERDO'],
            ['category' => 'ESTACIONES', 'name' => 'LOPEZ MATEOS'],
            ['category' => 'ESTACIONES', 'name' => 'MIGUEL DE LA MADRID'],
            ['category' => 'ESTACIONES', 'name' => 'MISIONES'],
            ['category' => 'ESTACIONES', 'name' => 'PARRAL'],
            ['category' => 'ESTACIONES', 'name' => 'PERMUTA'],
            ['category' => 'ESTACIONES', 'name' => 'PUERTO DE PALOS'],
            ['category' => 'ESTACIONES', 'name' => 'TECNOLOGICO'],
            ['category' => 'ESTACIONES', 'name' => 'TRAVEL CENTER'],
            ['category' => 'ESTACIONES', 'name' => 'VILLA AHUMADA'],

            // OPERACIONES
            ['category' => 'OPERACIONES', 'name' => 'EL CASTAÑO'],
            ['category' => 'OPERACIONES', 'name' => 'ES MALECON'],
            ['category' => 'OPERACIONES', 'name' => 'ES MUNICIPIO LIBRE'],
            ['category' => 'OPERACIONES', 'name' => 'PLUTARCO'],

            // STAFF
            ['category' => 'STAFF', 'name' => 'ADMINISTRATIVO'],
            ['category' => 'STAFF', 'name' => 'APLICACIONES Y SOFTWARE'],
            ['category' => 'STAFF', 'name' => 'AUDITORIA'],
            ['category' => 'STAFF', 'name' => 'COMERCIAL'],
            ['category' => 'STAFF', 'name' => 'CONTABILIDAD'],
            ['category' => 'STAFF', 'name' => 'DIRECCION COMERCIAL Y DE OPERACIONES'],
            ['category' => 'STAFF', 'name' => 'DIRECCIÓN'],
            ['category' => 'STAFF', 'name' => 'EXPANSIÓN'],
            ['category' => 'STAFF', 'name' => 'INGRESOS'],
            ['category' => 'STAFF', 'name' => 'JURIDICO'],
            ['category' => 'STAFF', 'name' => 'LOGISTICA'],
            ['category' => 'STAFF', 'name' => 'MANTENIMIENTO'],
            ['category' => 'STAFF', 'name' => 'NOMINAS'],
            ['category' => 'STAFF', 'name' => 'OPERATIVO'],
            ['category' => 'STAFF', 'name' => 'PROYECTOS'],
            ['category' => 'STAFF', 'name' => 'PSICOLOGIA'],
            ['category' => 'STAFF', 'name' => 'RECURSOS HUMANOS'],
            ['category' => 'STAFF', 'name' => 'SISTEMAS'],
            ['category' => 'STAFF', 'name' => 'VENTAS'],
        ];

        /**
         * 5) Insertar/asegurar registros.
         *    - Genera 'code' como <ABBR_CAT>_<SLUG_ASCII_MAYUS>.
         *    - Usa firstOrCreate para idempotencia.
         */
        foreach ($rows as $row) {
            $catName = $row['category'];
            $name = $row['name'];

            // Resuelve category_id por nombre (si no está, continúa para no romper el seed).
            $categoryId = $catId[$catName] ?? null;
            if (!$categoryId) {
                continue;
            }

            // Prefijo de categoría (para evitar colisiones entre categorías).
            $prefix = $abbr[$catName] ?? 'GEN';

            // Slug ASCII en MAYÚSCULAS con guiones bajos (sin acentos ni caracteres especiales).
            // Str::slug genera minúsculas y guiones: lo adaptamos a MAYÚSCULAS y underscores.
            $slug = Str::slug($name, '_');      // ej.: "HERMANOS-ESCOBAR" => "hermanos_escobar"
            $slug = strtoupper($slug);          // => "HERMANOS_ESCOBAR"

            // Code base
            $baseCode = $prefix . '_' . $slug;  // => "ENP_HERMANOS_ESCOBAR"

            // Asegurar unicidad del 'code' (por si existiera previamente o colisionara).
            $code = $this->uniqueCode($baseCode);

            CostCenter::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'category_id' => $categoryId,
                    'company_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Genera un code único en la tabla cost_centers basado en un baseCode.
     * Si ya existe, agrega sufijos _2, _3, ... hasta encontrar uno libre.
     */
    protected function uniqueCode(string $baseCode): string
    {
        $code = $baseCode;
        $i = 2;

        while (CostCenter::where('code', $code)->exists()) {
            $code = $baseCode . '_' . $i;
            $i++;
        }

        // Recorta a 50 chars en caso extremo (límite de la migración)
        return substr($code, 0, 50);
    }
}
