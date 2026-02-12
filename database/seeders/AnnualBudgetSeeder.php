<?php

namespace Database\Seeders;

use App\Models\AnnualBudget;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * AnnualBudgetSeeder
 *
 * Crea presupuestos anuales para 2026 en todos los centros de costo ANNUAL.
 * Los montos varían según la categoría del centro.
 */
class AnnualBudgetSeeder extends Seeder
{
    public function run(): void
    {
        // ===== OBTENER USUARIOS PARA AUDITORÍA =====
        $adminUser = User::where('email', 'admin@totalgas.local')->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        if (!$adminUser) {
            $this->command->error('❌ No hay usuarios en la tabla.');
            return;
        }

        // Director General (para aprobación)
        $directorGeneral = $adminUser;

        // ===== MAPEO DE MONTOS POR CATEGORÍA =====
        // Presupuestos anuales sugeridos según tipo de centro
        $budgetByCategory = [
            'ADMINISTRACION' => 50000.00,
            'ENPROYECTO' => 150000.00,
            'STAFF' => 80000.00,
            'CORPORATIVO' => 120000.00,
            'OPERACIONES' => 100000.00,
            'ESTACIONES' => 75000.00,
        ];

        // ===== OBTENER CENTROS DE COSTO ANUALES =====
        $annualCostCenters = CostCenter::where('budget_type', 'ANNUAL')
            ->notDeleted()
            ->with('category')
            ->get();

        if ($annualCostCenters->isEmpty()) {
            $this->command->warn('⚠️ No hay centros de costo ANNUAL en la base de datos.');
            return;
        }

        $this->command->info("📌 Creando presupuestos para " . $annualCostCenters->count() . " centros ANNUAL...");

        // ===== CREAR PRESUPUESTOS ANUALES =====
        foreach ($annualCostCenters as $costCenter) {
            // Obtener monto según categoría
            $categoryName = $costCenter->category?->name ?? 'OTRAS';
            $totalAmount = $budgetByCategory[$categoryName] ?? 75000.00;

            // Crear o actualizar presupuesto para 2026
            $budget = AnnualBudget::firstOrCreate(
                [
                    'cost_center_id' => $costCenter->id,
                    'fiscal_year' => 2026,
                ],
                [
                    'total_annual_amount' => $totalAmount,
                    'status' => 'APROBADO', // Directamente aprobado para usar inmediatamente
                    'approved_by' => $directorGeneral->id,
                    'approved_at' => now(),
                    'created_by' => $adminUser->id,
                    'updated_by' => null,
                ]
            );

            $this->command->line(
                "  ✓ " . str_pad($costCenter->code, 20) .
                    " → \$" . number_format($totalAmount, 2) .
                    " (Status: " . $budget->status . ")"
            );
        }

        $this->command->info('✅ ' . AnnualBudget::where('fiscal_year', 2026)->count() . ' Presupuestos Anuales 2026 creados correctamente.');
    }
}
