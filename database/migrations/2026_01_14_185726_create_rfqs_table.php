<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 50)->unique();

            // Relaciones - ✅ TODAS con noActionOnDelete() para SQL Server
            $table->foreignId('requisition_id')->constrained()->noActionOnDelete();
            $table->foreignId('quotation_group_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('requisition_item_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('supersedes_rfq_id')->nullable()->constrained('rfqs')->noActionOnDelete();

            // Origen
            $table->enum('source', ['portal', 'external'])->default('portal');
            $table->string('external_contact_method')->nullable();
            $table->text('external_notes')->nullable();

            // Estado y fechas (string en lugar de enum para soportar valores extendidos)
            $table->string('status', 40)->default('DRAFT');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('response_deadline')->nullable();

            // Cancelación
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('cancellation_reason')->nullable();

            // Rechazo
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->text('rejection_reason')->nullable();

            // Contenido
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->text('requirements')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('status');
            $table->index('sent_at');
            $table->index('response_deadline');
        });

        // SQL Server: CHECK constraint explícito para el campo status (string no genera uno automático)
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement("ALTER TABLE rfqs ADD CONSTRAINT CK_rfqs_status_allowed CHECK ([status] IN (N'DRAFT', N'SENT', N'RECEIVED', N'EVALUATED', N'COMPLETED', N'CANCELLED', N'REJECTED'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
