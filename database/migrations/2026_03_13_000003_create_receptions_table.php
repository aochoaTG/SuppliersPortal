<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receptions', function (Blueprint $table) {
            $table->id();

            // Folio único: REC-2026-0001
            $table->string('folio', 50)->unique();

            // Relación polimórfica: puede pertenecer a PurchaseOrder o DirectPurchaseOrder
            $table->morphs('receivable'); // receivable_type + receivable_id + índice compuesto

            // Dónde se recibió
            $table->foreignId('receiving_location_id')
                ->constrained('receiving_locations')
                ->noActionOnDelete();

            // Quién recibió
            $table->foreignId('received_by')
                ->constrained('users')
                ->noActionOnDelete();

            // Estado de este evento de recepción
            $table->enum('status', ['PENDING', 'PARTIAL', 'COMPLETED'])->default('PENDING');

            // Referencia del documento de entrega del proveedor (remisión, albarán, folio de factura)
            $table->string('delivery_reference', 100)->nullable();

            // Notas generales del receptor
            $table->text('notes')->nullable();

            // Momento en que se registró la recepción física
            $table->timestamp('received_at');

            $table->timestamps();
            $table->softDeletes();

            // Índices de búsqueda frecuente
            $table->index('folio');
            $table->index('status');
            $table->index('received_by');
            $table->index('receiving_location_id');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receptions');
    }
};
