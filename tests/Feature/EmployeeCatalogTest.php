<?php

namespace Tests\Feature;

use App\Models\User;
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

        $response->assertOk();
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
}
