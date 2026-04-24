<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_retenciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 20)->unique();          // ISR-HON, IVA-ARR, etc.
            $table->string('nombre', 100);
            $table->enum('impuesto', ['ISR', 'IVA']);
            $table->string('descripcion');
            $table->decimal('porcentaje', 8, 4)->nullable(); // null cuando es variable
            $table->string('porcentaje_display', 100);       // texto legible del %
            $table->string('base_calculo');                  // sobre qué se calcula
            $table->string('aplica_cuando');
            $table->string('base_legal', 100);
            $table->boolean('requiere_cfdi_retencion')->default(true);
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_retenciones');
    }
};