<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();

            // ===== DATOS BASE DEL CENTRO =====
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();

            // ===== RELACIONES ORGANIZACIONALES =====
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('no action')
                ->onUpdate('no action')
                ->comment('Categoría de clasificación del centro de costo');

            // ===== RESPONSABLE (AUDITORÍA) =====
            $table->foreignId('responsible_user_id')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action')
                ->comment('Jefe de Área responsable del centro de costo');

            // ===== TIPO Y MONTO DE PRESUPUESTO =====
            $table->enum('budget_type', ['ANNUAL', 'FREE_CONSUMPTION'])
                ->default('ANNUAL')
                ->comment('ANNUAL: presupuesto anual con límite mensual | FREE_CONSUMPTION: monto global sin límite temporal');

            $table->decimal('global_amount', 15, 2)
                ->nullable()
                ->comment('Monto global autorizado para centros de consumo libre');

            $table->text('free_consumption_justification')
                ->nullable()
                ->comment('Justificación para centros de consumo libre (obra, proyecto, uso continuo)');

            // ===== AUTORIZACIÓN DE CONSUMO LIBRE =====
            // Nullable: puede no haber autorización aún
            $table->foreignId('authorized_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action')
                ->comment('Director General que autorizó el centro de consumo libre');

            $table->timestamp('authorized_at')
                ->nullable()
                ->comment('Fecha de autorización del centro de consumo libre');

            // 🆕 VIGENCIA DE CONSUMO LIBRE
            $table->date('validity_date')
                ->nullable()
                ->comment('Fecha de vigencia del centro de consumo libre (OBLIGATORIO para FREE_CONSUMPTION, solo editable por superadmin)');

            // ===== ESTADO DEL CENTRO =====
            $table->enum('status', ['ACTIVO', 'INACTIVO'])
                ->default('ACTIVO')
                ->comment('Estado operacional del centro de costo');

            // ===== AUDITORÍA Y CONTROL =====
            // NO NULLABLE: auditoría completa requiere que existan estos usuarios
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action')
                ->comment('Usuario que creó el registro');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action')
                ->comment('Usuario que modificó el registro');

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action') // 🔴 CAMBIO: NO ACTION en lugar de SET NULL
                ->onUpdate('no action')
                ->comment('Usuario que eliminó el registro (soft delete)');

            // Soft delete: timestamp
            $table->softDeletes();

            // Timestamps estándar (created_at, updated_at)
            $table->timestamps();

            // ===== ÍNDICES =====
            $table->index('code');
            $table->index('name');
            $table->index('company_id');
            $table->index('category_id');
            $table->index('responsible_user_id');
            $table->index('budget_type');
            $table->index('status');
            $table->index(['budget_type', 'status']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
