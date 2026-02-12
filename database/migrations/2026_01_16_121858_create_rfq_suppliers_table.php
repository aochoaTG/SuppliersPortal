<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_suppliers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rfq_id')
                ->constrained('rfqs')
                ->onDelete('cascade');

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->onDelete('cascade');

            $table->timestamp('invited_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->string('quotation_pdf_path')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['rfq_id', 'supplier_id']); // Un proveedor solo una vez por RFQ
            $table->index('rfq_id');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_suppliers');
    }
};
