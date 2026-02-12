<?php

namespace Database\Seeders;

use App\Models\ApprovalLevel;
use Illuminate\Database\Seeder;

class ApprovalLevelSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla antes de sembrar para evitar duplicados en SQL Server
        // Ya que no usamos softDeletes, esto es un borrado total.
        \App\Models\ApprovalLevel::query()->delete();

        $levels = [
            [
                'level_number' => 1,
                'label'        => 'Comprador',
                'min_amount'   => 0,
                'max_amount'   => 5000.00,
                'color_tag'    => 'secondary',
                'description'  => 'Autonomía total para compras menores de oficina y suministros básicos.'
            ],
            [
                'level_number' => 2,
                'label'        => 'Jefe de Área',
                'min_amount'   => 5000.01,
                'max_amount'   => 50000.00,
                'color_tag'    => 'info',
                'description'  => 'Aprobación de gastos operativos de área.'
            ],
            [
                'level_number' => 3,
                'label'        => 'Gerente de Departamento',
                'min_amount'   => 50000.01,
                'max_amount'   => 150000.00,
                'color_tag'    => 'primary',
                'description'  => 'Revisión gerencial para insumos de mediano costo.'
            ],
            [
                'level_number' => 4,
                'label'        => 'Director de Área',
                'min_amount'   => 150000.01,
                'max_amount'   => 500000.00,
                'color_tag'    => 'warning',
                'description'  => 'Autorización de proyectos y equipamiento de alto valor.'
            ],
            [
                'level_number' => 5,
                'label'        => 'Director General',
                'min_amount'   => 500000.01,
                'max_amount'   => null, // Sin límite superior para el Comandante Supremo
                'color_tag'    => 'danger',
                'description'  => 'Inversiones críticas y activos estratégicos de TotalGas.'
            ],
        ];

        foreach ($levels as $level) {
            ApprovalLevel::create($level);
        }

        $this->command->info("¡Rangos de combate actualizados! 5 niveles desplegados.");
    }
}
