<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 50)->unique();

            // Relaciones - ✅ TODAS con noActionOnDelete() para SQL Server
            $table->foreignId('requisition_id')->constrained()->noActionOnDelete();
            $table->foreignId('quotation_group_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('requisition_item_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->noActionOnDelete();

            // Origen
            $table->enum('source', ['portal', 'external'])->default('portal');
            $table->string('external_contact_method')->nullable();
            $table->text('external_notes')->nullable();

            // Estado y fechas
            $table->enum('status', [
                'DRAFT',               // Borrador (aún no enviado)
                'SENT',                // Enviado al proveedor
                'RECEIVED',            // Proveedor(es) respondieron
                'EVALUATED',           // Cotizaciones evaluadas
                'COMPLETED',           // Completada
                'CANCELLED'            // Cancelada
            ])->default('DRAFT');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('response_deadline')->nullable();

            // Cancelación
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('cancellation_reason')->nullable();

            // Contenido
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->text('requirements')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('status');
            $table->index('sent_at');
            $table->index('response_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
