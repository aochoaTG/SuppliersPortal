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
        Schema::create('odc_direct_purchase_order_approvals', function (Blueprint $table) {
            $table->id();

            // Relación con la OCD
            $table->foreignId('direct_purchase_order_id')
                ->constrained('odc_direct_purchase_orders')
                ->onDelete('cascade');

            // Nivel de aprobación que se aplicó (1, 2, 3, 4)
            $table->integer('approval_level');

            // Quién aprobó/rechazó/devolvió
            $table->foreignId('approver_user_id')->constrained('users')->noActionOnDelete();

            // Acción tomada por el aprobador
            $table->enum('action', [
                'APPROVED',  // Aprobó la OCD
                'REJECTED',  // Rechazó la OCD
                'RETURNED'   // Devolvió para corrección
            ]);

            // Comentarios del aprobador (obligatorios para REJECTED y RETURNED)
            $table->text('comments')->nullable();

            // Fecha y hora de la acción
            $table->timestamp('approved_at'); // Usamos este campo para todas las acciones

            $table->timestamps();

            // Índices
            $table->index('direct_purchase_order_id');
            $table->index('approver_user_id');
            $table->index('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odc_direct_purchase_order_approvals');
    }
};
