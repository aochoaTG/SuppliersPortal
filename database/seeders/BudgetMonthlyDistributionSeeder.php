<?php

namespace Database\Seeders;

use App\Models\AnnualBudget;
use App\Models\BudgetMonthlyDistribution;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * BudgetMonthlyDistributionSeeder
 *
 * Distribuye los presupuestos anuales 2026 en:
 * - 12 meses (enero a diciembre)
 * - Múltiples categorías de gasto por mes
 *
 * Distribución por defecto (uniforme):
 * Presupuesto Anual / 12 meses = Presupuesto Mensual
 *
 * Presupuesto Mensual distribuido entre categorías con porcentajes predefinidos:
 * - Materiales: 30%
 * - Servicios: 25%
 * - Mantenimiento: 20%
 * - Tecnología: 10%
 * - Viáticos: 5%
 * - Capacitación: 5%
 * - Otros Gastos: 5%
 */
class BudgetMonthlyDistributionSeeder extends Seeder
{
    public function run(): void
    {
        // ===== OBTENER USUARIO PARA AUDITORÍA =====
        $adminUser = User::where('email', 'admin@totalgas.local')->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        if (!$adminUser) {
            $this->command->error('❌ No hay usuarios en la tabla.');
            return;
        }

        // ===== OBTENER CATEGORÍAS DE GASTO =====
        $categories = ExpenseCategory::where('status', 'ACTIVO')
            ->get()
            ->keyBy('code');

        if ($categories->isEmpty()) {
            $this->command->error('❌ No hay categorías de gasto en la tabla.');
            return;
        }

        // ===== PORCENTAJE DE DISTRIBUCIÓN POR CATEGORÍA =====
        // Suma debe ser 100%
        $distributionPercentage = [
            'MAT' => 30.00,  // Materiales: 30%
            'SER' => 25.00,  // Servicios: 25%
            'MAN' => 20.00,  // Mantenimiento: 20%
            'TEC' => 10.00,  // Tecnología: 10%
            'VIA' => 5.00,   // Viáticos: 5%
            'CAP' => 5.00,   // Capacitación: 5%
            'OTR' => 5.00,   // Otros Gastos: 5%
        ];

        // ===== OBTENER PRESUPUESTOS ANUALES 2026 =====
        $annualBudgets = AnnualBudget::where('fiscal_year', 2026)
            ->with('costCenter')
            ->get();

        if ($annualBudgets->isEmpty()) {
            $this->command->warn('⚠️ No hay presupuestos anuales 2026 en la base de datos.');
            return;
        }

        $this->command->info("📌 Creando distribuciones mensuales para " . $annualBudgets->count() . " presupuestos...");

        // ===== PROCESAR CADA PRESUPUESTO ANUAL =====
        foreach ($annualBudgets as $annualBudget) {
            $annualAmount = $annualBudget->total_annual_amount;
            $monthlyAmount = $annualAmount / 12; // Distribución uniforme

            $this->command->line("  📊 " . $annualBudget->costCenter->code . " → \$" . number_format($annualAmount, 2));

            // ===== CREAR DISTRIBUCIONES PARA CADA MES Y CATEGORÍA =====
            for ($month = 1; $month <= 12; $month++) {
                foreach ($distributionPercentage as $categoryCode => $percentage) {
                    // Obtener categoría
                    $category = $categories->get($categoryCode);

                    if (!$category) {
                        $this->command->warn("    ⚠️ Categoría $categoryCode no encontrada");
                        continue;
                    }

                    // Calcular monto para esta categoría este mes
                    $categoryMonthlyAmount = $monthlyAmount * ($percentage / 100);

                    // Crear o actualizar distribución mensual
                    BudgetMonthlyDistribution::updateOrCreate(
                        [
                            'annual_budget_id' => $annualBudget->id,
                            'expense_category_id' => $category->id,
                            'month' => $month,
                        ],
                        [
                            'assigned_amount' => $categoryMonthlyAmount,
                            'consumed_amount' => 0,
                            'committed_amount' => 0,
                            'created_by' => $adminUser->id,
                            'updated_by' => null,
                        ]
                    );
                }
            }
        }

        // ===== RESUMEN FINAL =====
        $totalDistributions = BudgetMonthlyDistribution::count();
        $this->command->info("✅ " . $totalDistributions . " Distribuciones mensuales creadas correctamente.");
        $this->command->info("   • Período: Enero-Diciembre 2026");
        $this->command->info("   • Categorías: 7 (MAT, SER, MAN, TEC, VIA, CAP, OTR)");
        $this->command->info("   • Total: " . $annualBudgets->count() . " presupuestos × 12 meses × 7 categorías");
    }
}
