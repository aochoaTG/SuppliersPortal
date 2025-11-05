<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ======= FKs "fuertes" (SI con constraint) =======
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate(); // sin onDelete

            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->cascadeOnUpdate();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnUpdate();

            // ======= Datos generales =======
            $table->integer('fiscal_year');
            $table->string('folio', 30)->unique();

            // Todos los campos que referencian "users" se dejan como BIGINT NULL con índice, sin constraint
            $table->unsignedBigInteger('requested_by')->nullable()->index();
            $table->date('required_date')->nullable();
            $table->string('description', 255)->nullable();
            $table->text('justification')->nullable();

            // ======= Montos =======
            $table->decimal('amount_requested', 18, 2)->default(0);
            $table->string('currency_code', 3)->default('MXN');

            // ======= Estado base =======
            $table->string('status', 40)->default('draft');

            // ======= Revisión =======
            $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            $table->dateTime('reviewed_at')->nullable();

            // ======= Aprobación =======
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->dateTime('approved_at')->nullable();

            // ======= En espera (On Hold) =======
            $table->text('on_hold_reason')->nullable();
            $table->unsignedBigInteger('on_hold_by')->nullable()->index();
            $table->dateTime('on_hold_at')->nullable();

            // ======= Rechazo =======
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable()->index();
            $table->dateTime('rejected_at')->nullable();

            // ======= Cancelación =======
            $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable()->index();
            $table->dateTime('cancelled_at')->nullable();

            // ======= Auditoría ligera =======
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Índices útiles para bandejas
            $table->index(['status', 'reviewed_by']);
            $table->index(['company_id', 'cost_center_id', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
