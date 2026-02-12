<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->unique()->constrained()->onDelete('cascade');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // 🚩 CAMBIO CLAVE: Quitamos el 'enum' y ponemos el ID del nivel
            $table->foreignId('approval_level_id')->nullable()->constrained('approval_levels');

            // 🚩 PARA EL NOMBRE DEL PROVEEDOR:
            $table->foreignId('selected_supplier_id')->nullable()->constrained('suppliers');

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('justification')->nullable(); // Para la demo

            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_summaries');
    }
};
