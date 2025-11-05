<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la tabla cost_centers.
     * Campos mínimos y claros:
     * - code: identificador único del centro (para integraciones/reportes).
     * - name: nombre visible.
     * - category_id: FK hacia categories (Fase 1).
     * - company_id: opcional (para multiempresa, si aplica en tu portal).
     * - is_active: habilita/deshabilita el uso en requisiciones.
     */
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();

            // Código único del centro de costo (ej.: E04188, CORP01, PROY-MIGUEL).
            $table->string('code', 50)->unique();

            // Nombre amigable en la UI (ej.: "Estación 07 Gemela Grande", "Dirección").
            $table->string('name', 150);

            // Relación con la categoría (ADMINISTRACION, ENPROYECTO, etc.).
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->onDelete('no action');

            // Multiempresa (opcional). Si no la usas aún, déjalo null.
            $table->unsignedBigInteger('company_id')->nullable();

            // Activo/inactivo en la operación diaria.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índices útiles para búsqueda
            $table->index(['name']);
            $table->index(['category_id']);
            $table->index(['company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
