<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();

            // ===== DATOS BASE =====
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();

            // ===== ESTADO =====
            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

            // ===== AUDITORÍA =====
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            // ===== TIMESTAMPS =====
            $table->softDeletes();
            $table->timestamps();

            // ===== ÍNDICES =====
            $table->index('code');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
