<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_commitments', function (Blueprint $table) {
            $table->foreignId('quotation_summary_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('quotation_summaries')
                ->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budget_commitments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_summary_id');
        });
    }
};
