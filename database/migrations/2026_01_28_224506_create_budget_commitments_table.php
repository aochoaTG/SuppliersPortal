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
        Schema::create('budget_commitments', function (Blueprint $table) {
            $table->id();

            // Puede venir de OCD o de OC normal (para reuso futuro)
            $table->foreignId('direct_purchase_order_id')
                ->nullable()
                ->constrained('odc_direct_purchase_orders')
                ->noActionOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_orders')
                ->noActionOnDelete();

            // Datos presupuestales (desnormalizados para consultas rápidas)
            $table->foreignId('cost_center_id')->constrained()->noActionOnDelete();
            $table->string('application_month', 7); // YYYY-MM
            $table->foreignId('expense_category_id')->constrained()->noActionOnDelete();

            // Monto comprometido
            $table->decimal('committed_amount', 12, 2);

            // Estados del compromiso
            $table->enum('status', [
                'COMMITTED',  // Presupuesto bloqueado (OCD enviada a aprobación)
                'RECEIVED',   // Bien/servicio recibido (listo para facturar)
                'RELEASED'    // Compromiso liberado (OCD rechazada/cancelada)
            ])->default('COMMITTED');

            // Fechas de control
            $table->timestamp('committed_at');   // Cuándo se comprometió
            $table->timestamp('released_at')->nullable(); // Cuándo se liberó

            $table->timestamps();

            // Índices para consultas de disponibilidad presupuestal
            $table->index(
                ['cost_center_id', 'application_month', 'expense_category_id', 'status'],
                'odc_budget_lookup_idx'
            );
            $table->index('status');

            // Constraint: debe tener relación con OCD o OC, pero no ambas
            // (esto se validará en el modelo, SQL Server no soporta CHECK constraints complejos bien)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_commitments');
    }
};
