<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('annual_budgets', function (Blueprint $table) {
            $table->id();

            // 🔗 Relaciones
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('cost_center_id');

            // 📅 Año fiscal
            $table->integer('fiscal_year')->comment('Año fiscal del presupuesto');

            // 💰 Montos principales
            $table->decimal('amount_assigned', 14, 2)->default(0)->comment('Monto asignado');
            $table->decimal('amount_committed', 14, 2)->default(0)->comment('Monto comprometido (cache)');
            $table->decimal('amount_consumed', 14, 2)->default(0)->comment('Monto consumido (cache)');
            $table->decimal('amount_released', 14, 2)->default(0)->comment('Monto liberado (cache)');
            $table->decimal('amount_adjusted', 14, 2)->default(0)->comment('Ajustes netos (subidas - bajadas)');
            $table->decimal('amount_available', 14, 2)->default(0)->comment('Monto disponible (cache)');

            // 📌 Estado y metadatos
            $table->boolean('is_closed')->default(false)->comment('Indica si el presupuesto está cerrado');
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            // 🔐 Relaciones FK
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->cascadeOnUpdate(); // onDelete NO ACTION por omisión

            $table->foreign('cost_center_id')
                ->references('id')->on('cost_centers')
                ->cascadeOnUpdate();

            // 🔍 Índices
            $table->unique(['cost_center_id', 'fiscal_year'], 'UX_annual_budgets_center_year');
            $table->index(['company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_budgets');
    }
};
