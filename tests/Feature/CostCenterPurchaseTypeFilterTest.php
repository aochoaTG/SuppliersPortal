<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostCenterPurchaseTypeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cost_centers_api_filters_by_user_company_and_purchase_type(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $company = Company::create([
            'code' => 'COMP-01',
            'name' => 'Compania Demo',
        ]);

        $otherCompany = Company::create([
            'code' => 'COMP-02',
            'name' => 'Compania Alterna',
        ]);

        $category = Category::create([
            'name' => 'STAFF',
            'created_by' => $user->id,
        ]);

        $matchingCenter = CostCenter::create([
            'code' => 'CC-OP-01',
            'name' => 'Centro Operativo Asignado',
            'purchase_type' => 'Gasto Operativo',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $user->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
            'created_by' => $user->id,
        ]);

        $sameCompanyDifferentType = CostCenter::create([
            'code' => 'CC-ST-01',
            'name' => 'Centro Staff Asignado',
            'purchase_type' => 'Gasto Staff',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $user->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
            'created_by' => $user->id,
        ]);

        $sameTypeUnassigned = CostCenter::create([
            'code' => 'CC-OP-02',
            'name' => 'Centro Operativo No Asignado',
            'purchase_type' => 'Gasto Operativo',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $otherUser->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
            'created_by' => $otherUser->id,
        ]);

        $otherCompanyCenter = CostCenter::create([
            'code' => 'CC-OP-03',
            'name' => 'Centro Otra Compania',
            'purchase_type' => 'Gasto Operativo',
            'company_id' => $otherCompany->id,
            'category_id' => $category->id,
            'responsible_user_id' => $user->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
            'created_by' => $user->id,
        ]);

        $user->companies()->attach([$company->id, $otherCompany->id]);
        $user->costCenters()->attach($matchingCenter->id, ['is_active' => true, 'is_default' => true]);
        $user->costCenters()->attach($sameCompanyDifferentType->id, ['is_active' => true, 'is_default' => false]);
        $user->costCenters()->attach($otherCompanyCenter->id, ['is_active' => true, 'is_default' => false]);

        $response = $this->actingAs($user)->getJson(route('cost-centers.api.by-company', $company) . '?purchase_type=Gasto%20Operativo');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $matchingCenter->id,
            'name' => 'Centro Operativo Asignado',
            'purchase_type' => 'Gasto Operativo',
        ]);
        $response->assertJsonMissing(['id' => $sameCompanyDifferentType->id]);
        $response->assertJsonMissing(['id' => $sameTypeUnassigned->id]);
        $response->assertJsonMissing(['id' => $otherCompanyCenter->id]);
    }
}
