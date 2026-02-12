<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_center_user', function (Blueprint $table) {
            $table->id();

            // ===== RELACIONES PRINCIPALES =====
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->comment('Usuario con acceso al centro de costo');

            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->comment('Centro de costo al que el usuario tiene acceso');

            // ===== CAMPOS ÚTILES =====
            $table->boolean('is_default')
                ->default(false)
                ->comment('TRUE si es el centro de costo predeterminado del usuario');

            $table->boolean('is_active')
                ->default(true)
                ->comment('TRUE si la asignación está activa');

            // ===== AUDITORÍA BÁSICA =====
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->comment('Usuario que creó la asignación');

            $table->unsignedBigInteger('updated_by')
                ->nullable()
                ->comment('Usuario que modificó la asignación');

            // Soft delete y timestamps
            $table->softDeletes();
            $table->timestamps();

            // ===== ÍNDICES =====
            $table->unique(['user_id', 'cost_center_id'], 'ux_user_cost_center');
            $table->index('is_default');
            $table->index('is_active');
            $table->index(['user_id', 'is_active'], 'idx_user_active');
            $table->index('deleted_at');
        });

        // ===== CONSTRAINTS MANUALES PARA SQL SERVER =====
        Schema::table('cost_center_user', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_center_user');
    }
};
