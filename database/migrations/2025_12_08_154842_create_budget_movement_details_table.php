<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_movement_details', function (Blueprint $table) {
            $table->id();

            // Relación con el encabezado del movimiento
            $table->foreignId('budget_movement_id')
                ->constrained('budget_movements')
                ->onDelete('cascade')
                ->comment('Movimiento presupuestal al que pertenece');

            // Tipo de detalle (ORIGEN, DESTINO para transferencias, AJUSTE para ampliaciones/reducciones)
            $table->enum('detail_type', ['ORIGEN', 'DESTINO', 'AJUSTE'])
                ->comment('Tipo de detalle: origen, destino o ajuste');

            // Centro de costo afectado
            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->onDelete('no action')
                ->comment('Centro de costo afectado');

            // Mes afectado (1-12)
            $table->tinyInteger('month')
                ->comment('Mes afectado (1-12)');

            // Categoría de gasto afectada
            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->onDelete('no action')
                ->comment('Categoría de gasto afectada');

            // Monto del detalle (positivo para aumentos, negativo para disminuciones)
            $table->decimal('amount', 15, 2)
                ->comment('Monto del movimiento (+ aumenta, - disminuye)');

            // Timestamps
            $table->timestamps();

            // Índices para mejorar consultas
            $table->index('budget_movement_id');
            $table->index('cost_center_id');
            $table->index(['cost_center_id', 'month', 'expense_category_id'], 'idx_cost_center_month_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_movement_details');
    }
};
