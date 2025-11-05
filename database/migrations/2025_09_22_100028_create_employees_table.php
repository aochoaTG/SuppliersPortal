<?php

// database/migrations/2025_09_22_000001_create_employees_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->unique(); // relación 1–1
            $table->string('company', 150)->nullable();                  // nombre de la empresa (archivo)
            $table->string('employee_number', 50)->nullable()->index();  // "Número de empleado"
            $table->string('full_name', 255)->nullable();                // "Nombre"
            $table->string('department', 150)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->date('hire_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('termination_date')->nullable();
            $table->boolean('rehire_eligible')->nullable();
            $table->string('termination_reason', 255)->nullable();
            $table->string('team', 150)->nullable();
            $table->string('seniority', 50)->nullable();                 // texto de antigüedad
            $table->string('rfc', 13)->nullable()->index();
            $table->string('imss', 20)->nullable();
            $table->string('curp', 18)->nullable()->index();
            $table->string('gender', 10)->nullable();
            $table->decimal('vacation_balance', 8, 2)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['company', 'employee_number']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('employees');
    }
};
