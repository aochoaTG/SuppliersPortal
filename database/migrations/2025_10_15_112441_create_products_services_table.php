<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crea la tabla products_services (Catálogo de Productos y Servicios)
     * según ESPECIFICACIONES_TECNICAS_SISTEMA_CONTROL_PRESUPUESTAL.md
     * Sección 5.1: Entidad PRODUCTO/SERVICIO
     */
    public function up(): void
    {
        Schema::create('products_services', function (Blueprint $table) {
            $table->id();

            // ============================================================
            // IDENTIFICACIÓN
            // ============================================================

            // Código único autogenerado
            $table->string('code', 20)->unique()
                ->comment('Código autogenerado del producto/servicio (ej: PROD-000001)');

            // Descripción técnica (mínimo 20 caracteres)
            $table->text('technical_description')
                ->comment('Descripción detallada del producto/servicio (≥20 chars)');

            // Nombre corto (opcional, para listados)
            $table->string('short_name', 100)->nullable()
                ->comment('Nombre corto para listados y reportes');

            // ============================================================
            // CLASIFICACIÓN
            // ============================================================

            // Tipo: Producto físico o Servicio
            $table->enum('product_type', ['PRODUCTO', 'SERVICIO'])->default('PRODUCTO')
                ->comment('Tipo: PRODUCTO físico o SERVICIO');

            // Relación con categoría (Electrónica, Papelería, etc.)
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onUpdate('cascade')
                ->onDelete('no action')
                ->comment('Categoría del catálogo');

            $table->string('subcategory', 100)->nullable()
                ->comment('Subcategoría específica');

            // ============================================================
            // ORGANIZACIÓN
            // ============================================================

            // Relación con centro de costo
            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->onUpdate('cascade')
                ->onDelete('no action')
                ->comment('Centro de costo al que pertenece');

            // Empresa (multiempresa)
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onUpdate('cascade')
                ->onDelete('no action')
                ->comment('Compañía propietaria del producto');

            // ============================================================
            // ESPECIFICACIONES TÉCNICAS
            // ============================================================

            // Marca y modelo (si aplica)
            $table->string('brand', 100)->nullable()
                ->comment('Marca del producto');

            $table->string('model', 100)->nullable()
                ->comment('Modelo del producto');

            // Unidad de medida
            $table->string('unit_of_measure', 30)->default('PIEZA')
                ->comment('Unidad de medida: PIEZA, SERVICIO, KG, LITRO, METRO, CAJA, etc.');

            // Especificaciones adicionales en JSON
            $table->json('specifications')->nullable()
                ->comment('Especificaciones técnicas adicionales en formato JSON');

            // ============================================================
            // INFORMACIÓN COMERCIAL
            // ============================================================

            // Precio estimado (referencial)
            $table->decimal('estimated_price', 15, 2)->default(0)
                ->comment('Precio referencial/estimado para presupuestos');

            $table->string('currency_code', 3)->default('MXN')
                ->comment('Moneda del precio estimado');

            // Proveedor sugerido/predeterminado (CRÍTICO - lo estábamos usando)
            $table->foreignId('default_vendor_id')->nullable()
                ->constrained('suppliers')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Proveedor sugerido del catálogo');

            // Restricciones de cantidad
            $table->decimal('minimum_quantity', 10, 3)->nullable()
                ->comment('Cantidad mínima de compra');

            $table->decimal('maximum_quantity', 10, 3)->nullable()
                ->comment('Cantidad máxima permitida por requisición');

            // Tiempo de entrega estimado
            $table->unsignedInteger('lead_time_days')->nullable()
                ->comment('Días estimados de entrega del proveedor');

            // ============================================================
            // ESTRUCTURA CONTABLE
            // ============================================================

            $table->string('account_major', 50)->nullable()
                ->comment('Cuenta Mayor contable');

            $table->string('account_sub', 50)->nullable()
                ->comment('Subcuenta contable');

            $table->string('account_subsub', 50)->nullable()
                ->comment('Subsubcuenta contable');

            // ============================================================
            // ESTADO Y APROBACIÓN
            // ============================================================

            // Estado del producto en el catálogo
            $table->enum('status', ['PENDING', 'ACTIVE', 'INACTIVE', 'REJECTED'])
                ->default('PENDING')
                ->comment('Estado del producto en el catálogo');

            // Flag de activo/inactivo (más simple que status para filtros)
            $table->boolean('is_active')->default(true)
                ->comment('TRUE si está activo y disponible para requisiciones');

            // Motivo de rechazo o inactivación
            $table->text('rejection_reason')->nullable()
                ->comment('Motivo de rechazo o inactivación');

            // ============================================================
            // OBSERVACIONES Y NOTAS
            // ============================================================

            $table->text('observations')->nullable()
                ->comment('Observaciones generales del producto/servicio');

            $table->text('internal_notes')->nullable()
                ->comment('Notas internas (no visibles para requisitores)');

            // ============================================================
            // AUDITORÍA COMPLETA
            // ============================================================

            $table->unsignedBigInteger('created_by')->nullable()->index()
                ->comment('Usuario que creó el registro');

            $table->unsignedBigInteger('updated_by')->nullable()->index()
                ->comment('Último usuario que modificó');

            $table->unsignedBigInteger('approved_by')->nullable()->index()
                ->comment('Usuario que aprobó para el catálogo');

            $table->timestamp('approved_at')->nullable()
                ->comment('Fecha de aprobación');

            $table->unsignedBigInteger('deleted_by')->nullable()->index()
                ->comment('Usuario que eliminó (soft delete)');

            $table->softDeletes();
            $table->timestamps();

            // ============================================================
            // ÍNDICES PARA OPTIMIZACIÓN
            // ============================================================

            $table->index('code');
            $table->index('status');
            $table->index('is_active');
            $table->index('product_type');
            $table->index(['category_id', 'subcategory']);
            $table->index('cost_center_id');
            $table->index('company_id');
            $table->index('default_vendor_id');
            $table->index('created_at');

            // Índice compuesto para búsquedas frecuentes
            $table->index(['company_id', 'cost_center_id', 'is_active'], 'idx_company_cc_active');
        });

        // Agregar constraints foreign keys manualmente con NO ACTION para auditoría
        Schema::table('products_services', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('no action')->onUpdate('no action');

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onDelete('no action')->onUpdate('no action');

            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('no action')->onUpdate('no action');

            $table->foreign('deleted_by')
                ->references('id')->on('users')
                ->onDelete('no action')->onUpdate('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_services');
    }
};
