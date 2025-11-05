<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnnualBudget;
use App\Models\CostCenter;
use App\Models\Company;

/**
 * AnnualBudgetSeeder
 *
 * Crea presupuestos de ejemplo para el año fiscal 2025,
 * vinculados a centros de costo y compañías reales.
 */
class AnnualBudgetSeeder extends Seeder
{
    public function run(): void
    {
        $year = 2025;

        // === Ejemplos base ===
        $budgets = [
            // nombreCentro => monto
            'Estación 07 Gemela Grande' => 750000.00,
            'Dirección General' => 400000.00,
            'Mantenimiento Estaciones' => 600000.00,
            'Comercial' => 500000.00,
            'Tecnologia de Redes y Comunicacion' => 300000.00,
        ];

        foreach ($budgets as $ccName => $amount) {
            $costCenter = CostCenter::where('name', $ccName)->first();
            if (!$costCenter) {
                $this->command->warn("⚠️ Centro de costo no encontrado: {$ccName}");
                continue;
            }

            // Si tus cost centers tienen relación con compañía, la tomamos directamente
            $companyId = $costCenter->company_id ?? Company::first()->id ?? 1;

            AnnualBudget::updateOrCreate(
                [
                    'cost_center_id' => $costCenter->id,
                    'fiscal_year' => $year,
                ],
                [
                    'company_id' => $companyId,
                    'amount_assigned' => $amount,
                    'amount_committed' => 0,
                    'amount_consumed' => 0,
                    'amount_released' => 0,
                    'amount_adjusted' => 0,
                    'amount_available' => $amount,
                    'is_closed' => false,
                    'notes' => 'Presupuesto inicial generado por seeder.',
                ]
            );

            $this->command->info("✅ Presupuesto {$ccName} creado ({$amount})");
        }

        $this->command->info("🎯 Presupuestos anuales 2025 generados correctamente.");
    }
}
