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
        Schema::create('quotation_group_items', function (Blueprint $table) {
            // ===================================================
            // LLAVES FORÁNEAS (Relación muchos a muchos)
            // ===================================================
            $table->foreignId('quotation_group_id')
                ->constrained('quotation_groups')
                ->onDelete('cascade');

            $table->foreignId('requisition_item_id')
                ->constrained('requisition_items')
                ->onDelete('no action');

            // ===================================================
            // METADATA ADICIONAL
            // ===================================================
            $table->text('notes')->nullable(); // Notas específicas por partida
            $table->integer('sort_order')->default(0); // Orden de visualización

            $table->timestamps();

            // ===================================================
            // LLAVE PRIMARIA COMPUESTA
            // ===================================================
            $table->primary(
                ['quotation_group_id', 'requisition_item_id'],
                'qgi_primary'
            );

            // ===================================================
            // ÍNDICES
            // ===================================================
            $table->index('quotation_group_id');
            $table->index('requisition_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_group_items');
    }
};
