<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('financial_provisions')) {
            return;
        }

        Schema::create('financial_provisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->unique()->constrained('receptions')->noActionOnDelete();
            $table->nullableMorphs('receivable');
            $table->foreignId('supplier_id')->constrained('suppliers')->noActionOnDelete();
            $table->unsignedBigInteger('supplier_invoice_id')->nullable();
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->string('application_month', 7)->nullable();
            $table->decimal('provision_amount', 12, 2);
            $table->decimal('invoice_amount', 12, 2)->nullable();
            $table->decimal('difference_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            $table->string('status', 40)->default('PENDING_INVOICE');
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index('supplier_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_provisions');
    }
};
