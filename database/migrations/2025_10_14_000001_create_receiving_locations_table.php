<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración crea la tabla 'receiving_locations' para almacenar
     * las ubicaciones físicas donde se reciben bienes y servicios:
     * estaciones de servicio, corporativo, almacenes, etc.
     */
    public function up(): void
    {
        Schema::create('receiving_locations', function (Blueprint $table) {
            // Identificador único de la ubicación
            $table->id();

            // Código único para identificar la ubicación (ej. EST-001, CORP-01, ALM-05)
            // Útil para búsquedas rápidas y referencia en otros sistemas
            $table->string('code', 20)->unique()->comment('Código único de la ubicación (ej. EST-001, CORP-01)');

            // Nombre descriptivo de la ubicación
            $table->string('name', 100)->comment('Nombre de la ubicación (ej. Estación Gemela Grande, Oficinas Corporativas)');

            // Tipo de ubicación según el catálogo definido en el FRU
            $table->enum('type', [
                'service_station',  // Estación de servicio (gasolinera)
                'corporate',        // Oficinas corporativas
                'warehouse',        // Almacén / bodega
                'other'             // Otros tipos de ubicaciones
            ])->default('service_station')->comment('Tipo de ubicación: service_station, corporate, warehouse, other');

            // Información de contacto y ubicación geográfica
            $table->string('address', 255)->nullable()->comment('Dirección completa de la ubicación');
            $table->string('city', 100)->nullable()->comment('Ciudad');
            $table->string('state', 50)->nullable()->comment('Estado o provincia');
            $table->string('country', 50)->default('México')->comment('País');
            $table->string('postal_code', 10)->nullable()->comment('Código postal');
            $table->string('phone', 20)->nullable()->comment('Teléfono de contacto');
            $table->string('email', 100)->nullable()->comment('Correo electrónico de contacto');
            
            // Responsable de la ubicación (gerente de estación, encargado de almacén, etc.)
            $table->string('manager_name', 100)->nullable()->comment('Nombre del responsable de la ubicación (Jefe de zona, gerente de estació, personal de compras, etc)');

            // Control de estado y bloqueos según FRU punto 4.5
            $table->boolean('is_active')->default(true)->comment('Indica si la ubicación está activa (para desactivaciones sin eliminar)');
            $table->boolean('portal_blocked')->default(false)->comment('Indica si el portal de proveedores está bloqueado para esta ubicación');

            // Notas u observaciones adicionales
            $table->text('notes')->nullable()->comment('Observaciones generales sobre la ubicación');

            // Campos de auditoría de Laravel
            $table->timestamps();  // created_at y updated_at

            // Índices para mejorar el rendimiento de búsquedas frecuentes
            $table->index('type', 'idx_receiving_locations_type');
            $table->index('is_active', 'idx_receiving_locations_is_active');
            $table->index('portal_blocked', 'idx_receiving_locations_portal_blocked');
            $table->index(['city', 'state'], 'idx_receiving_locations_city_state');
            
            // Índice compuesto para búsquedas por tipo y estado activo (común en consultas)
            $table->index(['type', 'is_active'], 'idx_receiving_locations_type_active');
        });
    }

    /**
     * Reverse the migrations.
     * Elimina la tabla 'receiving_locations' de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_locations');
    }
};