<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_monthly_distributions', function (Blueprint $table) {
            $table->dropUnique('UX_monthly_dist_budget_month_cat');
        });
    }

    public function down(): void
    {
        Schema::table('budget_monthly_distributions', function (Blueprint $table) {
            $table->unique(
                ['annual_budget_id', 'month', 'expense_category_id'],
                'UX_monthly_dist_budget_month_cat'
            );
        });
    }
};
