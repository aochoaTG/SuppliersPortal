<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_responses', function (Blueprint $table) {
            $table->id();

            // ===================================================
            // RELACIÓN: A qué RFQ y PROVEEDOR responde
            // ===================================================
            $table->foreignId('rfq_id')
                ->constrained('rfqs')
                ->noActionOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->noActionOnDelete();

            // ===================================================
            // PARTIDA COTIZADA
            // ===================================================
            $table->foreignId('requisition_item_id')
                ->constrained('requisition_items')
                ->noActionOnDelete();

            // ===================================================
            // DATOS GENERALES DE LA COTIZACIÓN
            // ===================================================
            $table->date('quotation_date')->nullable();
            $table->integer('validity_days')->nullable();
            $table->string('supplier_quotation_number', 100)->nullable();

            // ===================================================
            // DATOS DE PRECIO (SIN IVA)
            // ===================================================
            $table->decimal('unit_price', 12, 2)->comment('Precio unitario SIN IVA');
            $table->decimal('quantity', 10, 3)->comment('Cantidad (permite decimales para m, kg, L, etc)');
            $table->decimal('subtotal', 12, 2)->comment('Subtotal SIN IVA (unit_price * quantity)');

            // ===================================================
            // 🆕 IMPUESTOS (IVA)
            // ===================================================
            $table->decimal('iva_rate', 5, 2)->default(16.00)->comment('Tasa de IVA: 16.00, 8.00, 0.00');
            $table->decimal('iva_amount', 12, 2)->default(0.00)->comment('Monto del IVA calculado');
            $table->decimal('total', 12, 2)->comment('Total CON IVA (subtotal + iva_amount)');

            // ===================================================
            // 🆕 DESCUENTOS (OPCIONAL)
            // ===================================================
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Descuento en porcentaje');
            $table->decimal('discount_amount', 12, 2)->nullable()->comment('Monto del descuento');

            // ===================================================
            // 🆕 MONEDA
            // ===================================================
            $table->char('currency', 3)->default('MXN')->comment('Moneda: MXN, USD, EUR');

            // ===================================================
            // CONDICIONES COMERCIALES
            // ===================================================
            $table->integer('delivery_days')->nullable()->comment('Días de entrega');
            $table->string('payment_terms', 255)->nullable()->comment('Condiciones de pago');
            $table->text('warranty_terms')->nullable()->comment('Términos de garantía');

            // ===================================================
            // DETALLES TÉCNICOS
            // ===================================================
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->text('specifications')->nullable();
            $table->text('notes')->nullable();

            // ===================================================
            // ARCHIVOS ADJUNTOS
            // ===================================================
            $table->string('attachment_path')->nullable();

            // ===================================================
            // EVALUACIÓN
            // ===================================================
            $table->boolean('meets_specs')->default(true);
            $table->integer('score')->nullable()->comment('Puntuación de evaluación 0-100');
            $table->text('evaluation_notes')->nullable();

            // ===================================================
            // SELECCIÓN Y ESTADOS
            // ===================================================
            $table->enum('status', [
                'DRAFT',      // Borrador (proveedor aún no envía)
                'SUBMITTED',  // Enviada por el proveedor
                'SELECTED',   // Seleccionada (ganadora)
                'REJECTED'    // Rechazada
            ])->default('DRAFT');

            $table->text('selection_justification')->nullable();
            $table->timestamp('submitted_at')->nullable()->comment('Fecha en que el proveedor envió la cotización');

            // ===================================================
            // AUDITORÍA
            // ===================================================
            $table->foreignId('evaluated_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamp('evaluated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ===================================================
            // ÍNDICES
            // ===================================================
            $table->index('rfq_id');
            $table->index('supplier_id');
            $table->index('requisition_item_id');
            $table->index('status');
            $table->index('iva_rate');
            $table->index(['rfq_id', 'status']);
            $table->index(['supplier_id', 'status']);

            // ✅ CONSTRAINT: Un proveedor solo puede responder UNA VEZ por partida en una RFQ
            $table->unique(['rfq_id', 'supplier_id', 'requisition_item_id'], 'unique_response_per_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_responses');
    }
};
