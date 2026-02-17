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
        Schema::create('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->id();

            // Folio único generado automáticamente
            $table->string('folio', 50)->unique()->nullable(); // OCD-2026-0001

            // Relaciones principales
            $table->foreignId('supplier_id')->constrained()->noActionOnDelete();
            $table->foreignId('cost_center_id')->constrained()->noActionOnDelete();
            // ❌ ELIMINADO: expense_category_id

            // Datos de la solicitud
            $table->string('application_month', 7); // YYYY-MM (ej: 2026-03)
            $table->text('justification'); // Obligatorio - razón de la compra directa

            // Montos totales
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->char('currency', 3)->default('MXN');

            // Condiciones heredadas del proveedor
            $table->string('payment_terms')->nullable(); // Ej: "30 días neto"
            $table->integer('estimated_delivery_days')->nullable();

            // Control de aprobación (UN SOLO NIVEL)
            $table->integer('required_approval_level')->nullable(); // 1, 2, 3, 4
            $table->foreignId('assigned_approver_id')->nullable()->constrained('users')->noActionOnDelete();

            // Estados del ciclo de vida
            $table->enum('status', [
                'DRAFT',              // Borrador (aún editando)
                'PENDING_APPROVAL',   // Enviada a aprobación
                'APPROVED',           // Aprobada (generando PDF)
                'REJECTED',           // Rechazada por aprobador
                'RETURNED',           // Devuelta para corrección
                'ISSUED',             // Emitida (PDF generado)
                'RECEIVED',           // Bienes/servicios recibidos
                'CANCELLED'           // Cancelada
            ])->default('DRAFT');

            // Ruta del PDF generado
            $table->string('pdf_path')->nullable();

            // Notas de recepción
            $table->text('reception_notes')->nullable();

            // Auditoría - Quién y Cuándo
            $table->foreignId('created_by')->constrained('users')->noActionOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->noActionOnDelete();

            // Timestamps de eventos importantes
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar performance
            $table->index('folio');
            $table->index('status');
            $table->index(['cost_center_id', 'application_month']);
            $table->index('created_by');
            $table->index('assigned_approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odc_direct_purchase_orders');
    }
};
