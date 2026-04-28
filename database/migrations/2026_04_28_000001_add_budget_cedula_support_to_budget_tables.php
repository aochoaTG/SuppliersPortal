<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_monthly_distributions', function (Blueprint $table) {
            $table->foreignId('budget_cedula_id')
                ->nullable()
                ->after('annual_budget_id')
                ->constrained('budget_cedulas')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->index('budget_cedula_id', 'idx_bmd_budget_cedula_id');
        });

        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX ux_bmd_budget_month_cedula ON budget_monthly_distributions (annual_budget_id, month, budget_cedula_id) WHERE budget_cedula_id IS NOT NULL');
        } else {
            Schema::table('budget_monthly_distributions', function (Blueprint $table) {
                $table->unique(['annual_budget_id', 'month', 'budget_cedula_id'], 'ux_bmd_budget_month_cedula');
            });
        }

        Schema::table('budget_commitments', function (Blueprint $table) {
            $table->foreignId('budget_cedula_id')
                ->nullable()
                ->after('expense_category_id')
                ->constrained('budget_cedulas')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->index(['budget_cedula_id', 'status'], 'idx_budget_commitments_cedula_status');
        });
    }

    public function down(): void
    {
        Schema::table('budget_commitments', function (Blueprint $table) {
            $table->dropIndex('idx_budget_commitments_cedula_status');
            $table->dropConstrainedForeignId('budget_cedula_id');
        });

        Schema::table('budget_monthly_distributions', function (Blueprint $table) {
            $table->dropUnique('ux_bmd_budget_month_cedula');
            $table->dropIndex('idx_bmd_budget_cedula_id');
            $table->dropConstrainedForeignId('budget_cedula_id');
        });
    }
};
