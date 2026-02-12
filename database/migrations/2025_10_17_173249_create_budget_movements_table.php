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
        Schema::create('budget_movements', function (Blueprint $table) {
            $table->id();

            // Tipo de movimiento
            $table->enum('movement_type', ['TRANSFERENCIA', 'AMPLIACION', 'REDUCCION'])
                ->comment('Tipo de movimiento presupuestal');

            // Año fiscal al que pertenece
            $table->year('fiscal_year')
                ->comment('Año presupuestal afectado');

            // Fecha del movimiento
            $table->date('movement_date')
                ->comment('Fecha en que se realiza el movimiento');

            // Monto total del movimiento
            $table->decimal('total_amount', 15, 2)
                ->comment('Monto total del movimiento');

            // Justificación
            $table->text('justification')
                ->comment('Justificación del movimiento presupuestal');

            // Estado del movimiento
            $table->enum('status', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])
                ->default('PENDIENTE')
                ->comment('Estado del movimiento');

            // Usuario que crea el movimiento
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('no action')
                ->comment('Usuario que registra el movimiento');

            // Usuario que aprueba (nullable)
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action')
                ->comment('Usuario que aprueba/rechaza el movimiento');

            // Fecha de aprobación (nullable)
            $table->timestamp('approved_at')
                ->nullable()
                ->comment('Fecha de aprobación o rechazo');

            // Timestamps
            $table->timestamps();

            // Índices para mejorar consultas
            $table->index('fiscal_year');
            $table->index('movement_type');
            $table->index('status');
            $table->index('movement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_movements');
    }
};
