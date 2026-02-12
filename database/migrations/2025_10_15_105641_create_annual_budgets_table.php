<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea tabla de presupuestos anuales.
     *
     * IMPORTANTE: Solo para centros de costo con budget_type = 'ANNUAL'
     * Período: Año Fiscal (enero a diciembre)
     * Estados: PLANIFICACION → APROBADO → CERRADO
     */
    public function up(): void
    {
        Schema::create('annual_budgets', function (Blueprint $table) {
            $table->id();

            // ===== RELACIÓN CON CENTRO DE COSTO =====
            // Centro de costo ANUAL
            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->onDelete('no action')
                ->onUpdate('no action')
                ->comment('Centro de costo ANUAL al que pertenece el presupuesto');

            // ===== PERÍODO FISCAL =====
            // Año fiscal (ej: 2025)
            $table->integer('fiscal_year')
                ->comment('Año fiscal (YYYY)');

            // ===== MONTO TOTAL ANUAL =====
            // Presupuesto total anual asignado
            $table->decimal('total_annual_amount', 15, 2)
                ->comment('Monto total anual asignado para este centro');

            // ===== ESTADO =====
            // Estados: PLANIFICACION, APROBADO, CERRADO
            $table->enum('status', ['PLANIFICACION', 'APROBADO', 'CERRADO'])
                ->default('PLANIFICACION')
                ->comment('PLANIFICACION: en edición | APROBADO: vigente | CERRADO: sin movimientos');

            // ===== APROBACIÓN =====
            // Director General que aprobó
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action')
                ->comment('Director General que aprobó el presupuesto');

            // Fecha de aprobación
            $table->timestamp('approved_at')
                ->nullable()
                ->comment('Fecha y hora de aprobación');

            // ===== AUDITORÍA COMPLETA =====
            // Usuario que creó
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action')
                ->comment('Usuario que creó el presupuesto');

            // Usuario que modificó
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action')
                ->comment('Usuario que modificó el presupuesto');

            // Usuario que eliminó (soft delete)
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action')
                ->comment('Usuario que eliminó el presupuesto');

            // Soft delete
            $table->softDeletes();

            // Timestamps
            $table->timestamps();

            // ===== ÍNDICES =====
            // Único: un presupuesto por centro de costo y año fiscal
            $table->unique(['cost_center_id', 'fiscal_year'], 'UX_annual_budgets_center_year');

            // Índices de búsqueda
            $table->index('fiscal_year');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_budgets');
    }
};
