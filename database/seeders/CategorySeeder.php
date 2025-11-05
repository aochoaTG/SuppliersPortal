<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * CategorySeeder
 *
 * Crea las 6 categorías base para arrancar rápido.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'ADMINISTRACION', 'description' => 'Gastos y servicios administrativos', 'is_active' => true],
            ['name' => 'ENPROYECTO', 'description' => 'Agrupación de costos en fase de proyecto', 'is_active' => true],
            ['name' => 'STAFF', 'description' => 'Personal / Recursos Humanos / equipos internos', 'is_active' => true],
            ['name' => 'CORPORATIVO', 'description' => 'Oficina central y servicios compartidos', 'is_active' => true],
            ['name' => 'OPERACIONES', 'description' => 'Operaciones y soporte en campo', 'is_active' => true],
            ['name' => 'ESTACIONES', 'description' => 'Agrupación de estaciones de servicio', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            Category::firstOrCreate(
                ['name' => $row['name']],
                ['description' => $row['description'], 'is_active' => $row['is_active']]
            );
        }
    }
}
