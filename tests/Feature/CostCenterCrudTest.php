<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostCenterCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_requires_purchase_type(): void
    {
        $user = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $response = $this->actingAs($user)->post(route('cost-centers.store'), [
            'code' => 'CC-REQ',
            'name' => 'Centro sin tipo',
            'description' => 'Descripcion',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
        ]);

        $response->assertSessionHasErrors('purchase_type');
        $this->assertDatabaseMissing('cost_centers', [
            'code' => 'CC-REQ',
        ]);
    }

    public function test_store_persists_purchase_type(): void
    {
        $user = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $response = $this->actingAs($user)->post(route('cost-centers.store'), [
            'code' => 'CC-OK',
            'name' => 'Centro con tipo',
            'description' => 'Descripcion',
            'purchase_type' => 'Gasto Operativo',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
        ]);

        $response->assertRedirect(route('cost-centers.index'));
        $this->assertDatabaseHas('cost_centers', [
            'code' => 'CC-OK',
            'purchase_type' => 'Gasto Operativo',
            'created_by' => $user->id,
        ]);
    }

    public function test_update_allows_changing_purchase_type(): void
    {
        $user = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $costCenter = CostCenter::create([
            'code' => 'CC-UPD',
            'name' => 'Centro editable',
            'description' => 'Descripcion inicial',
            'purchase_type' => 'Gasto Operativo',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
            'budget_type' => 'ANNUAL',
            'global_amount' => null,
            'free_consumption_justification' => null,
            'authorized_by' => null,
            'authorized_at' => null,
            'validity_date' => null,
            'status' => 'ACTIVO',
            'created_by' => $user->id,
            'updated_by' => null,
            'deleted_by' => null,
        ]);

        $response = $this->actingAs($user)->put(route('cost-centers.update', $costCenter), [
            'code' => 'CC-UPD',
            'name' => 'Centro editable',
            'description' => 'Descripcion inicial',
            'purchase_type' => 'Gasto Corporativo',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
            'budget_type' => 'ANNUAL',
            'status' => 'ACTIVO',
        ]);

        $response->assertRedirect(route('cost-centers.index'));
        $this->assertDatabaseHas('cost_centers', [
            'id' => $costCenter->id,
            'purchase_type' => 'Gasto Corporativo',
            'updated_by' => $user->id,
        ]);
    }

    private function seedCatalogs(): array
    {
        $company = Company::create([
            'code' => 'TG01',
            'name' => 'TotalGas Test',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'OPERACIONES',
            'description' => 'Operaciones',
            'is_active' => true,
        ]);

        $responsible = User::factory()->create([
            'name' => 'Maria Responsable',
            'email' => 'maria.responsable@example.com',
            'is_active' => true,
        ]);

        return [$company, $category, $responsible];
    }
}
