<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requisition_id')
                ->constrained('requisitions')
                ->onUpdate('no action')
                ->cascadeOnDelete();

            $table->unsignedInteger('line_number');

            $table->string('item_category', 120)->nullable();
            $table->string('product_code', 80)->nullable();

            // 👇 SIN cascadeOnUpdate
            $table->foreignId('suggested_vendor_id')->nullable();
            $table->foreign('suggested_vendor_id')
                ->references('id')->on('suppliers')
                ->nullOnDelete();              // ON DELETE SET NULL (OK en SQL Server)
            // ->onUpdate('no action');    // (opcional, es el default)

            $table->text('notes')->nullable();

            $table->string('description', 255);

            $table->decimal('quantity', 14, 3)->default(1);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 14, 4)->default(0);

            // 👇 SIN cascadeOnUpdate
            $table->foreignId('tax_id')->nullable();
            $table->foreign('tax_id')
                ->references('id')->on('taxes')
                ->nullOnDelete();
            // ->onUpdate('no action');

            $table->decimal('line_total', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_with_tax', 14, 2)->default(0);

            $table->timestamps();

            $table->unique(['requisition_id', 'line_number'], 'requisition_items_unique_line');
            $table->index('item_category');
            $table->index('product_code');
            $table->index('suggested_vendor_id');
            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
