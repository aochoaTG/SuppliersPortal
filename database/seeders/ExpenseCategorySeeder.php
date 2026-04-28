<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $createdBy = 6; // Usuario administrador que crea las categorías

        $categories = [
            [
                'code' => 'A',
                'name' => 'Ingresos',
                'description' => 'Ingresos generados por la operación',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'B',
                'name' => 'Costo de Venta',
                'description' => 'Costos directamente asociados a la venta de productos o servicios',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'C',
                'name' => 'Nómina',
                'description' => 'Sueldos, salarios y pagos al personal',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'D',
                'name' => 'Costo Social',
                'description' => 'Prestaciones sociales, IMSS, Infonavit y cargas patronales',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'E',
                'name' => 'Gastos de Operación',
                'description' => 'Gastos necesarios para el funcionamiento diario de la operación',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'F',
                'name' => 'Mantenimiento',
                'description' => 'Mantenimiento preventivo y correctivo de equipos e instalaciones',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'H',
                'name' => 'Gastos Fijos',
                'description' => 'Gastos recurrentes fijos independientes del volumen de operación',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'I',
                'name' => 'Ingresos No Operativos',
                'description' => 'Ingresos no derivados de la operación principal (utilidad cambiaria, rentas, otros)',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'J',
                'name' => 'Depreciaciones y Amortizaciones',
                'description' => 'Depreciación de activos fijos y amortización de intangibles',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'K',
                'name' => 'CIF',
                'description' => 'Costos indirectos de fabricación',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'L',
                'name' => 'Partidas Extraordinarias Total 2.0',
                'description' => 'Partidas extraordinarias y conceptos especiales fuera de la operación ordinaria',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'M',
                'name' => 'Otros Proyectos',
                'description' => 'Gastos e inversiones relacionados con otros proyectos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insertar todas las categorías
        DB::table('expense_categories')->insert($categories);

        $this->command->info('✅ Se han insertado ' . count($categories) . ' categorías de gasto correctamente.');
    }
}
