<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->unique();

            // Origen del registro
            $table->string('archivo_origen', 255)->nullable();

            // Datos del empleado
            $table->string('employee_number', 50)->nullable()->index();
            $table->string('full_name', 255);                           // NOT nullable
            $table->string('department', 150)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->date('hire_date')->nullable();
            $table->string('is_active', 10)->nullable();
            $table->date('termination_date')->nullable();
            $table->string('rehire_eligible', 10)->nullable();
            $table->string('termination_reason', 255)->nullable();
            $table->string('team', 150)->nullable();
            $table->string('seniority', 50)->nullable();

            // Identificadores oficiales
            $table->string('rfc', 20)->nullable()->index();
            $table->string('imss', 20)->nullable();
            $table->string('curp', 20)->nullable()->index();

            // Datos personales
            $table->string('gender', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('education', 100)->nullable();

            // Datos laborales / empresa
            $table->string('company', 255)->nullable();
            $table->string('responsible', 150)->nullable();
            $table->string('leader', 150)->nullable();

            // Campos financieros
            $table->decimal('vacation_balance', 10, 4)->nullable();
            $table->decimal('savings_fund', 10, 4)->nullable();
            $table->decimal('daily_salary', 10, 4)->nullable();
            $table->decimal('severance_bonus', 10, 4)->nullable();
            $table->decimal('indemnization', 10, 4)->nullable();
            $table->decimal('seniority_premium', 10, 4)->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['company', 'employee_number']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('employees');
    }
};
