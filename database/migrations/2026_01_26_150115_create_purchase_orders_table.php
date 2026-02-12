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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 50)->unique(); // Ej: OC-2026-0001

            // Relaciones estratégicas
            $table->foreignId('requisition_id')->constrained()->noActionOnDelete();
            $table->foreignId('supplier_id')->constrained()->noActionOnDelete();
            $table->foreignId('quotation_summary_id')->constrained()->noActionOnDelete();

            // Datos Financieros (Heredados del sumario)
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->char('currency', 3)->default('MXN');

            // Condiciones (Heredadas del parche que hicimos)
            $table->string('payment_terms')->nullable();
            $table->integer('estimated_delivery_days')->nullable();

            // Estado de la OC
            $table->enum('status', ['OPEN', 'RECEIVED', 'CANCELLED', 'PAID'])->default('OPEN');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
