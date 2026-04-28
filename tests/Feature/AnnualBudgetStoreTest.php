<?php

namespace Tests\Feature;

use App\Models\AnnualBudget;
use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnualBudgetStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_rejects_duplicate_active_budget_for_same_cost_center_and_year(): void
    {
        $context = $this->createContext();

        AnnualBudget::create([
            'cost_center_id' => $context['costCenter']->id,
            'fiscal_year' => 2026,
            'total_annual_amount' => 500000,
            'status' => 'PLANIFICACION',
            'created_by' => $context['user']->id,
        ]);

        $response = $this->actingAs($context['user'])
            ->from(route('annual_budgets.create'))
            ->post(route('annual_budgets.store'), [
                'cost_center_id' => $context['costCenter']->id,
                'fiscal_year' => 2026,
                'total_annual_amount' => 1000000,
            ]);

        $response->assertRedirect(route('annual_budgets.create'));
        $response->assertSessionHasErrors([
            'cost_center_id' => 'Ya existe un presupuesto para este centro de costo en el año 2026.',
        ]);

        $this->assertSame(1, AnnualBudget::withTrashed()->count());
    }

    public function test_store_rejects_duplicate_soft_deleted_budget_for_same_cost_center_and_year(): void
    {
        $context = $this->createContext();

        $budget = AnnualBudget::create([
            'cost_center_id' => $context['costCenter']->id,
            'fiscal_year' => 2026,
            'total_annual_amount' => 500000,
            'status' => 'PLANIFICACION',
            'created_by' => $context['user']->id,
        ]);

        $budget->delete();

        $response = $this->actingAs($context['user'])
            ->from(route('annual_budgets.create'))
            ->post(route('annual_budgets.store'), [
                'cost_center_id' => $context['costCenter']->id,
                'fiscal_year' => 2026,
                'total_annual_amount' => 1000000,
            ]);

        $response->assertRedirect(route('annual_budgets.create'));
        $response->assertSessionHasErrors([
            'cost_center_id' => 'Ya existe un presupuesto eliminado para este centro de costo en el año 2026. Restaura o reutiliza ese registro en lugar de crear otro.',
        ]);

        $this->assertSame(1, AnnualBudget::withTrashed()->count());
        $this->assertTrue(AnnualBudget::withTrashed()->first()->trashed());
    }

    private function createContext(): array
    {
        $user = User::factory()->create();

        $company = Company::create([
            'code' => 'DGA',
            'name' => 'Diaz Gas',
            'legal_name' => 'Diaz Gas SA',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'OPERACIONES',
            'description' => 'Operaciones',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $costCenter = CostCenter::create([
            'code' => '00046',
            'name' => 'CORPORATIVO',
            'purchase_type' => 'Gasto Operativo',
            'category_id' => $category->id,
            'company_id' => $company->id,
            'responsible_user_id' => $user->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
            'created_by' => $user->id,
        ]);

        return [
            'user' => $user,
            'costCenter' => $costCenter,
        ];
    }
}
