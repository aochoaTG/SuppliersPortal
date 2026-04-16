<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'buyer', 'guard_name' => 'web']);
    }

    public function test_superadmin_can_access_employees_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $response = $this->actingAs($user)->get('/employees');

        $response->assertOk()
                 ->assertViewHas('companies');
    }

    public function test_buyer_is_forbidden_from_employees_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');

        $response = $this->actingAs($user)->get('/employees');

        $response->assertForbidden();
    }

    public function test_buyer_is_forbidden_from_employees_datatable(): void
    {
        $user = User::factory()->create();
        $user->assignRole('buyer');

        $response = $this->actingAs($user)->getJson('/employees/datatable');

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/employees');

        $response->assertRedirect('/login');
    }

    public function test_superadmin_can_access_employees_datatable(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $response = $this->actingAs($user)->getJson('/employees/datatable');

        $response->assertOk()
                 ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_datatable_filters_by_is_active(): void
    {
        Employee::factory()->create(['is_active' => 'SI']);
        Employee::factory()->create(['is_active' => 'NO']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $response = $this->actingAs($user)->getJson('/employees/datatable?is_active=SI');
        $response->assertJsonCount(1, 'data');
        $this->assertStringContainsString('SI', $response->json('data.0.is_active'));

        $response = $this->actingAs($user)->getJson('/employees/datatable?is_active=NO');
        $response->assertJsonCount(1, 'data');
        $this->assertStringContainsString('NO', $response->json('data.0.is_active'));
    }

    public function test_datatable_filters_by_company(): void
    {
        Employee::factory()->create(['company' => 'Empresa A']);
        Employee::factory()->create(['company' => 'Empresa B']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $response = $this->actingAs($user)->getJson('/employees/datatable?company=Empresa A');
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Empresa A', $response->json('data.0.company'));
    }

    public function test_datatable_searches_by_full_name(): void
    {
        Employee::factory()->create(['first_name' => 'Juan Alberto', 'last_name' => 'Perez Diaz']);
        Employee::factory()->create(['first_name' => 'Maria', 'last_name' => 'Gomez']);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        // Search by part of full name
        $response = $this->actingAs($user)->getJson('/employees/datatable?search[value]=Juan Alberto Perez');
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Juan Alberto Perez Diaz', $response->json('data.0.full_name'));

        // Search by part of first and last name
        $response = $this->actingAs($user)->getJson('/employees/datatable?search[value]=Alberto Perez');
        $response->assertJsonCount(1, 'data');
    }
}
