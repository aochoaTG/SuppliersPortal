<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_summaries', function (Blueprint $table) {
            $table->id();
            // Sin unique en requisition_id: una requisición puede tener varios resúmenes (por rfq)
            $table->foreignId('requisition_id')->constrained()->onDelete('cascade');
            $table->foreignId('rfq_id')->nullable()->constrained('rfqs')->cascadeOnDelete();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->foreignId('approval_level_id')->nullable()->constrained('approval_levels');
            $table->foreignId('selected_supplier_id')->nullable()->constrained('suppliers');

            // Flujo de aprobación
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('selected_by_user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('current_approver_user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('authorizer_role_id')->nullable()->constrained('authorizer_roles')->nullOnDelete();
            $table->decimal('effective_authorization_limit', 14, 2)->nullable();
            $table->longText('approval_chain_snapshot')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('budget_reserved_at')->nullable();
            $table->timestamp('budget_released_at')->nullable();

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('justification')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Índice único filtrado en rfq_id (SQL Server no permite unique con múltiples NULLs)
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX quotation_summaries_rfq_id_unique ON quotation_summaries (rfq_id) WHERE rfq_id IS NOT NULL');
        } else {
            Schema::table('quotation_summaries', function (Blueprint $table) {
                $table->unique('rfq_id', 'quotation_summaries_rfq_id_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_summaries');
    }
};
