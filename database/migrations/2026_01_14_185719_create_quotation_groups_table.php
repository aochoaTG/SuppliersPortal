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
        Schema::create('quotation_groups', function (Blueprint $table) {
            $table->id();

            // ===================================================
            // RELACIÓN: A qué requisición pertenece este grupo
            // ===================================================
            $table->foreignId('requisition_id')
                ->constrained('requisitions')
                ->onDelete('cascade');

            // ===================================================
            // DATOS DEL GRUPO
            // ===================================================
            $table->string('name', 100); // "Equipo de Oficina", "Papelería", etc.

            // ===================================================
            // METADATA
            // ===================================================
            $table->text('notes')->nullable(); // Notas del comprador sobre el grupo

            // ===================================================
            // ESTADO Y CANCELACIÓN / RECHAZO
            // ===================================================
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('rejection_reason')->nullable();

            // ===================================================
            // AUDITORÍA
            // ===================================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action');

            $table->timestamps();
            $table->softDeletes();

            // ===================================================
            // ÍNDICES
            // ===================================================
            $table->index('requisition_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_groups');
    }
};
