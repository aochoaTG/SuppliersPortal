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

            // Cantidad recibida en este evento
            $table->decimal('quantity_received', 10, 3);

            // Conformidad de especificaciones (FRU Sección 4.3-B)
            // CONFORME = el producto cumple especificaciones
            // NO_CONFORME = el producto NO cumple especificaciones
            $table->string('conformity', 20)->default('CONFORME');

            // Categoría de no conformidad — solo aplica cuando conformity = NO_CONFORME
            $table->string('nonconformity_type', 50)->nullable();

            // Descripción detallada (mínimo 100 caracteres en UI cuando NO_CONFORME)
            $table->text('nonconformity_notes')->nullable();

            // Rutas de evidencia fotográfica (JSON array, máx 5 fotos por partida)
            // Obligatorio cuando conformity = NO_CONFORME, opcional cuando CONFORME
            $table->json('photos')->nullable();

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
