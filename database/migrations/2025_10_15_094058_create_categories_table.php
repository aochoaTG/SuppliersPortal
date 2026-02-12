<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la tabla categories.
     * Diseño minimalista: nombre único, descripción opcional y flag de activo.
     * Mantiene timestamps para auditoría básica.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Nombre visible de la categoría de centro de costo.
            // Único para evitar duplicados (ADMINISTRACION, ENPROYECTO, STAFF, etc.).
            $table->string('name', 80)->unique();

            // Descripción opcional para dar contexto al usuario/contabilidad.
            $table->string('description', 255)->nullable();

            // Estatus de uso en la operación diaria (true = visible/seleccionable).
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users');

            // Auditoría básica
            $table->timestamps();
        });
    }

    /**
     * Reversión simple para entornos de desarrollo.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
