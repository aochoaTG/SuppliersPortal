<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('fiscal_year');

            // ✅ Esta puede tener CASCADE (es la relación principal)
            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->cascadeOnUpdate();

            // ❌ QUITAR cascadeOnUpdate() para evitar ciclos
            $table->foreignId('annual_budget_id')
                ->nullable()
                ->constrained('annual_budgets')
                ->nullOnDelete(); // Solo nullOnDelete, SIN cascadeOnUpdate

            // ❌ QUITAR cascadeOnUpdate() aquí también
            $table->foreignId('requisition_id')
                ->nullable()
                ->constrained('requisitions')
                ->nullOnDelete(); // Solo nullOnDelete, SIN cascadeOnUpdate

            // Tipo de movimiento
            $table->string('movement_type', 20); // ASSIGN|COMMIT|RELEASE|CONSUME|ADJUST_UP|ADJUST_DOWN

            $table->decimal('amount', 14, 2);
            $table->char('currency_code', 3)->default('MXN');

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('source', 30)->nullable();
            $table->string('note', 255)->nullable();

            $table->timestamps();

            $table->index(['fiscal_year', 'cost_center_id']);
            $table->index(['movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_movements');
    }
};
