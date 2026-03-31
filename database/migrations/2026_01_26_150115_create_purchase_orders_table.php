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
            $table->unsignedBigInteger('receiving_location_id')->nullable();

            // Datos Financieros (Heredados del sumario)
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->char('currency', 3)->default('MXN');

            // Condiciones (Heredadas del parche que hicimos)
            $table->string('payment_terms')->nullable();
            $table->integer('estimated_delivery_days')->nullable();

            // Estado de la OC
            $table->enum('status', [
                'OPEN',
                'ISSUED',
                'PARTIALLY_RECEIVED',
                'RECEIVED',
                'CANCELLED',
                'PAID',
                'CLOSED_BY_INACTIVITY',
                'DELIVERED_PENDING_RECEPTION',
            ])->default('OPEN');

            // Timestamps de eventos importantes
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('supplier_delivered_at')->nullable()->comment('Fecha en que el proveedor reportó la entrega física');
            $table->timestamp('reception_deadline_at')->nullable()->comment('Fecha límite (3 días hábiles) para que la estación capture la recepción');
            $table->timestamp('inactivity_warning_sent_at')->nullable();

            // Notas de recepción
            $table->text('reception_notes')->nullable();

            // Campos de entrega física del proveedor
            $table->string('physical_receiver_name', 150)->nullable()->comment('Nombre de quien recibió físicamente en la estación');
            $table->text('delivery_observations')->nullable()->comment('Observaciones del proveedor al momento de la entrega');

            // Auditoría
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedBigInteger('received_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('receiving_location_id')
                ->references('id')
                ->on('receiving_locations')
                ->nullOnDelete();

            $table->foreign('received_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
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
