<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_cedulas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->string('name', 200);

            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

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

            $table->softDeletes();
            $table->timestamps();

            $table->index('expense_category_id');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_cedulas');
    }
};
