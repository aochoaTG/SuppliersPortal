<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reception_items', function (Blueprint $table) {
            $table->id();

            // Recepción a la que pertenece esta línea
            $table->foreignId('reception_id')
                ->constrained('receptions')
                ->cascadeOnDelete();

            // Ítem de orden que se está recibiendo.
            // Puede ser PurchaseOrderItem o DirectPurchaseOrderItem.
            $table->morphs('receivable_item'); // receivable_item_type + receivable_item_id + índice compuesto

            // Cantidades de este evento
            $table->decimal('quantity_received', 10, 3);
            $table->decimal('quantity_rejected', 10, 3)->default(0);

            // Motivo si hubo rechazo de unidades (dañadas, incorrectas, etc.)
            $table->string('rejection_reason', 255)->nullable();

            $table->timestamps();

            // Índice para consultas frecuentes desde la recepción
            $table->index('reception_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reception_items');
    }
};
