<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();

            // Relación con empresa
            $table->foreignId('company_id')
                ->constrained('companies');

            // Información general
            $table->string('station_name', 100);          // Estación
            $table->string('country', 100)->nullable();   // País
            $table->string('state', 100)->nullable();     // Estado
            $table->string('municipality', 150)->nullable(); // Municipio
            $table->string('address', 255)->nullable();   // Dirección
            $table->string('expedition_place', 255)->nullable(); // Lugar de expedición
            $table->string('server_ip', 45)->nullable();  // IP del servidor

            // Campos administrativos
            $table->string('database_name', 100)->nullable();   // Base de datos
            $table->string('cre_permit', 50)->nullable();       // Permiso CRE
            $table->string('email', 150)->nullable();

            // Identificación externa
            $table->string('source_system', 50)->nullable();    // Sistema externo (ControlGas, SG12, etc.)
            $table->string('external_id', 100)->nullable();     // ID en sistema externo

            // Estado
            $table->boolean('is_active')->default(true);        // Activa/Inactiva

            $table->timestamps();

            // Índices y restricciones
            $table->unique(['source_system', 'external_id'], 'stations_unique_source_idx');
            $table->index(['company_id', 'is_active'], 'stations_company_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
