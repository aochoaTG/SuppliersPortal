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
        Schema::create('odc_direct_purchase_order_documents', function (Blueprint $table) {
            $table->id();

            // Relación con la OCD
            $table->foreignId('direct_purchase_order_id')
                ->constrained('odc_direct_purchase_orders')
                ->onDelete('cascade');

            // Tipo de documento
            $table->enum('document_type', [
                'quotation',           // Cotización del proveedor (obligatoria)
                'support_document',    // Documento de soporte adicional
                'reception_evidence'   // Evidencia de recepción
            ]);

            // Ruta del archivo almacenado
            $table->string('file_path');

            // Nombre original del archivo (para descarga)
            $table->string('original_filename');

            // Quién subió el archivo
            $table->foreignId('uploaded_by')->constrained('users')->noActionOnDelete();

            $table->timestamps();

            // Índices
            $table->index('direct_purchase_order_id');
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odc_direct_purchase_order_documents');
    }
};
