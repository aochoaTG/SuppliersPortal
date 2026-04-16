<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePromoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'buyer', 'guard_name' => 'web']);
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        return $user;
    }

    private function employee(array $attrs = []): Employee
    {
        return Employee::factory()->create(array_merge([
            'first_name' => 'Juan',
            'last_name'  => 'Pérez',
            'email'      => 'juan.perez@petrotal.com.mx',
            'phone'      => '6561234567',
            'job_title'  => 'Operador',
            'user_id'    => null,
        ], $attrs));
    }

    public function test_promote_form_returns_view_for_eligible_employee(): void
    {
        $response = $this->actingAs($this->superadmin())
            ->get(route('employees.promote-form', $this->employee()));

        $response->assertOk()
                 ->assertViewIs('employees.partials.promote-form');
    }

    public function test_promote_form_returns_409_when_employee_already_has_user(): void
    {
        $existingUser = User::factory()->create();
        $employee = $this->employee(['user_id' => $existingUser->id]);

        $response = $this->actingAs($this->superadmin())
            ->getJson(route('employees.promote-form', $employee));

        $response->assertStatus(409)
                 ->assertJsonFragment(['error' => 'Este empleado ya tiene un usuario asignado.']);
    }

    public function test_promote_creates_user_and_links_employee(): void
    {
        Notification::fake();

        $employee = $this->employee();

        $response = $this->actingAs($this->superadmin())
            ->postJson(route('employees.promote', $employee), [
                'name'      => 'Juan Pérez',
                'email'     => 'juan.perez@petrotal.com.mx',
                'password'  => 'Password123!',
                'phone'     => '6561234567',
                'job_title' => 'Operador',
                'roles'     => ['buyer'],
            ]);

        $response->assertOk()->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'juan.perez@petrotal.com.mx']);
        $employee->refresh();
        $this->assertNotNull($employee->user_id);

        $user = User::where('email', 'juan.perez@petrotal.com.mx')->first();
        $this->assertTrue($user->hasRole('buyer'));
    }

    public function test_promote_sends_welcome_notification(): void
    {
        Notification::fake();

        $employee = $this->employee();

        $this->actingAs($this->superadmin())
            ->postJson(route('employees.promote', $employee), [
                'name'     => 'Juan Pérez',
                'email'    => 'juan.perez@petrotal.com.mx',
                'password' => 'Password123!',
            ]);

        $user = User::where('email', 'juan.perez@petrotal.com.mx')->first();
        Notification::assertSentTo($user, StaffWelcomeNotification::class);
    }

    public function test_promote_rejects_disallowed_email_domain(): void
    {
        $employee = $this->employee();

        $response = $this->actingAs($this->superadmin())
            ->postJson(route('employees.promote', $employee), [
                'name'     => 'Juan Pérez',
                'email'    => 'juan@gmail.com',
                'password' => 'Password123!',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_promote_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'juan.perez@petrotal.com.mx']);
        $employee = $this->employee();

        $response = $this->actingAs($this->superadmin())
            ->postJson(route('employees.promote', $employee), [
                'name'     => 'Juan Pérez',
                'email'    => 'juan.perez@petrotal.com.mx',
                'password' => 'Password123!',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_promote_is_forbidden_for_non_superadmin(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');

        $response = $this->actingAs($buyer)
            ->postJson(route('employees.promote', $this->employee()), [
                'name'     => 'Juan Pérez',
                'email'    => 'juan.perez@petrotal.com.mx',
                'password' => 'Password123!',
            ]);

        $response->assertForbidden();
    }

    public function test_photo_form_returns_view(): void
    {
        $response = $this->actingAs($this->superadmin())
            ->get(route('employees.photo-form', $this->employee()));

        $response->assertOk()
                 ->assertViewIs('employees.partials.photo-form');
    }

    public function test_upload_photo_stores_file_and_updates_employee(): void
    {
        Storage::fake('public');

        $employee = $this->employee();
        $file     = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($this->superadmin())
            ->post(route('employees.upload-photo', $employee), ['photo' => $file]);

        $response->assertOk()->assertJsonFragment(['success' => true]);

        $employee->refresh();
        $this->assertNotNull($employee->photo);
        Storage::disk('public')->assertExists($employee->photo);
    }

    public function test_upload_photo_deletes_old_file_when_replacing(): void
    {
        Storage::fake('public');

        $employee = $this->employee();
        $oldPath  = "employees/{$employee->id}/photo/old.jpg";
        Storage::disk('public')->put($oldPath, 'old-content');
        $employee->update(['photo' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg', 200, 200);

        $this->actingAs($this->superadmin())
            ->post(route('employees.upload-photo', $employee), ['photo' => $newFile]);

        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_upload_photo_rejects_non_image(): void
    {
        Storage::fake('public');

        $employee = $this->employee();
        $file     = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->superadmin())
            ->postJson(route('employees.upload-photo', $employee), ['photo' => $file]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['photo']);
    }

    public function test_upload_photo_is_forbidden_for_non_superadmin(): void
    {
        Storage::fake('public');

        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($buyer)
            ->post(route('employees.upload-photo', $this->employee()), ['photo' => $file]);

        $response->assertForbidden();
    }
}
