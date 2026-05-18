<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('financial_provision_adjustments')) {
            return;
        }

        Schema::create('financial_provision_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_provision_id')->constrained('financial_provisions')->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->noActionOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255);
            $table->text('notes')->nullable();
            $table->timestamp('authorized_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_provision_adjustments');
    }
};
