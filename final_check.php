<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cc = App\Models\CostCenter::where('name', 'APLICACIONES Y SOFTWARE')->first();
$budget = App\Models\AnnualBudget::where('cost_center_id', $cc->id)->where('fiscal_year', 2026)->first();
$val = App\Models\BudgetMonthlyDistribution::where('annual_budget_id', $budget->id)
    ->join('budget_cedulas', 'budget_monthly_distributions.budget_cedula_id', '=', 'budget_cedulas.id')
    ->where('budget_cedulas.name', 'like', '%Software%')
    ->where('month', 1)
    ->first();

echo "RESULTADO FINAL:\n";
echo "CC: " . $cc->name . "\n";
echo "Enero (Software): " . ($val->assigned_amount ?? '0') . "\n";
echo "Total Anual CC: " . $budget->total_annual_amount . "\n";
