<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();

            // Rango de nivel (1, 2, 3, 4...)
            $table->integer('level_number')->unique();

            // Quién es el responsable (Jefe de Área, Director, etc.)
            $table->string('label', 100);

            // Rango monetario
            // Usamos 12,2 para soportar hasta $9,999,999,999.99
            $table->decimal('min_amount', 12, 2)->default(0);

            // El máximo puede ser NULL para el último nivel (Superior a...)
            $table->decimal('max_amount', 12, 2)->nullable();

            // Metadata para UI (Zircos tags: 'info', 'warning', 'danger', etc.)
            $table->string('color_tag', 30)->default('primary');

            // Descripción interna de la regla
            $table->text('description')->nullable();

            // Auditoría básica (Sin softDeletes por orden superior)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_levels');
    }
};
