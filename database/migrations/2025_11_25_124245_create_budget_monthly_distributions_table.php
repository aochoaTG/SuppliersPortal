<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea tabla de distribuciones mensuales de presupuestos.
     */
    public function up(): void
    {
        Schema::create('budget_monthly_distributions', function (Blueprint $table) {
            $table->id();

            // ===== RELACIONES =====
            // Presupuesto anual padre
            $table->foreignId('annual_budget_id')
                ->constrained('annual_budgets')
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->comment('Presupuesto anual al que pertenece esta distribución');

            // Categoría de gasto
            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->onDelete('no action')
                ->onUpdate('no action')
                ->comment('Categoría de gasto (Materiales, Servicios, etc.)');

            // ===== PERÍODO =====
            // Mes (1-12)
            $table->integer('month')
                ->comment('Mes (1=Enero, 2=Febrero, ..., 12=Diciembre)');

            // ===== MONTOS ASIGNADOS =====
            // Monto asignado para este mes/categoría
            $table->decimal('assigned_amount', 15, 2)
                ->comment('Monto asignado para este mes y categoría');

            // ===== TRACKING (calculado dinámicamente) =====
            // Monto consumido ejecutado
            $table->decimal('consumed_amount', 15, 2)
                ->default(0)
                ->comment('Monto consumido (compras ejecutadas)');

            // Monto comprometido (requisiciones autorizadas)
            $table->decimal('committed_amount', 15, 2)
                ->default(0)
                ->comment('Monto comprometido (requisiciones autorizadas)');

            // ===== AUDITORÍA =====
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action');

            // Soft delete
            $table->softDeletes();

            // Timestamps
            $table->timestamps();

            // ===== ÍNDICES =====
            // Único: un registro por presupuesto anual + mes + categoría
            $table->unique(
                ['annual_budget_id', 'month', 'expense_category_id'],
                'UX_monthly_dist_budget_month_cat'
            );

            // Índices de búsqueda
            $table->index('month');
            $table->index('expense_category_id');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_monthly_distributions');
    }
};
