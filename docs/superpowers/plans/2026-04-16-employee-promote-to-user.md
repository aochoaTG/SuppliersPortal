# Promover Empleado a Usuario Staff — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir que el superadmin convierta un empleado del catálogo TRESS en usuario staff del portal directamente desde la tabla de empleados, mediante un modal con formulario, validación de dominio corporativo y email de bienvenida con credenciales.

**Architecture:** Se agrega una `ValidationRule` para dominios permitidos, una `Notification` de bienvenida, dos métodos al `EmployeeController` existente (`promoteForm` y `promote`), una vista parcial del modal, y se actualiza la vista `employees/index.blade.php` con la columna de acciones, el modal container y el JS de interacción AJAX.

**Tech Stack:** Laravel 12 · PHP 8.2 · Spatie Permission · Yajra DataTables · Bootstrap 5 Modal · Laravel Notifications (mail) · Fetch API

---

## File Map

| Acción | Archivo | Responsabilidad |
|---|---|---|
| Crear | `app/Rules/AllowedEmailDomain.php` | Validar que el dominio del email sea corporativo |
| Crear | `app/Notifications/StaffWelcomeNotification.php` | Email de bienvenida con credenciales |
| Modificar | `app/Http/Controllers/EmployeeController.php` | Agregar `promoteForm()` y `promote()` |
| Modificar | `routes/web.php` | Agregar rutas promote dentro de `role:superadmin` |
| Crear | `resources/views/employees/partials/promote-form.blade.php` | Formulario modal con drag & drop |
| Modificar | `resources/views/employees/index.blade.php` | Columna actions + modal container + JS |
| Crear | `tests/Feature/EmployeePromoteTest.php` | Feature tests de promoción |

---

## Task 1: AllowedEmailDomain Rule

**Files:**
- Create: `app/Rules/AllowedEmailDomain.php`
- Create: `tests/Feature/AllowedEmailDomainTest.php`

- [ ] **Step 1: Escribir el test que falla**

Crear `tests/Feature/AllowedEmailDomainTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Rules\AllowedEmailDomain;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AllowedEmailDomainTest extends TestCase
{
    private function validate(string $email): bool
    {
        return Validator::make(
            ['email' => $email],
            ['email' => [new AllowedEmailDomain]]
        )->passes();
    }

    public function test_allowed_domain_passes(): void
    {
        $this->assertTrue($this->validate('usuario@petrotal.com.mx'));
        $this->assertTrue($this->validate('nombre@totalgasolineras.com'));
        $this->assertTrue($this->validate('x@totalgasonline-ags.com'));
    }

    public function test_disallowed_domain_fails(): void
    {
        $this->assertFalse($this->validate('usuario@gmail.com'));
        $this->assertFalse($this->validate('nombre@hotmail.com'));
        $this->assertFalse($this->validate('test@empresa.com'));
    }

    public function test_validation_is_case_insensitive(): void
    {
        $this->assertTrue($this->validate('usuario@PETROTAL.COM.MX'));
        $this->assertTrue($this->validate('nombre@TotalGasolineras.COM'));
    }
}
```

- [ ] **Step 2: Ejecutar el test para confirmar que falla**

```bash
php artisan test tests/Feature/AllowedEmailDomainTest.php
```

Salida esperada: FAIL — "Class AllowedEmailDomain not found"

- [ ] **Step 3: Crear la regla**

Crear `app/Rules/AllowedEmailDomain.php`:

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class AllowedEmailDomain implements ValidationRule
{
    private const ALLOWED_DOMAINS = [
        'petrotal.com.mx',
        'totalgasolineras.com',
        'totalgasonline.mx',
        'rendilitrosjuarez.com',
        'prlsc.com',
        'rapigas.com',
        'aquacarwashclub.com',
        'aquacarclub.com',
        'energmedia.com',
        'petrodigitalmedia.com',
        'totaldigitalmedia.com',
        'fuelmedia.com.mx',
        'petrodigital.com.mx',
        'petromedia.com.mx',
        'totalmedia.mx',
        'masquegas.com',
        'gasolucion.com',
        'totalgasonline.com',
        'totalgasonline.net',
        'totalgasonline-ags.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(Str::after((string) $value, '@'));

        if (!in_array($domain, self::ALLOWED_DOMAINS, true)) {
            $fail('El dominio del correo no está permitido. Solo se aceptan correos corporativos del grupo TotalGas.');
        }
    }
}
```

- [ ] **Step 4: Ejecutar el test para confirmar que pasa**

```bash
php artisan test tests/Feature/AllowedEmailDomainTest.php
```

Salida esperada: 3 tests passing

- [ ] **Step 5: Commit**

```bash
git add app/Rules/AllowedEmailDomain.php tests/Feature/AllowedEmailDomainTest.php
git commit -m "feat: add AllowedEmailDomain validation rule for staff users"
```

---

## Task 2: StaffWelcomeNotification

**Files:**
- Create: `app/Notifications/StaffWelcomeNotification.php`
- Create: `tests/Feature/StaffWelcomeNotificationTest.php`

- [ ] **Step 1: Escribir el test que falla**

Crear `tests/Feature/StaffWelcomeNotificationTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StaffWelcomeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_via_mail_only(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@petrotal.com.mx']);
        $user->notify(new StaffWelcomeNotification('MiPassword123'));

        Notification::assertSentTo($user, StaffWelcomeNotification::class, function ($notification) {
            return in_array('mail', $notification->via($notification));
        });
    }

    public function test_notification_is_not_sent_via_database(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@petrotal.com.mx']);
        $user->notify(new StaffWelcomeNotification('MiPassword123'));

        Notification::assertSentTo($user, StaffWelcomeNotification::class, function ($notification) {
            return !in_array('database', $notification->via($notification));
        });
    }
}
```

- [ ] **Step 2: Ejecutar el test para confirmar que falla**

```bash
php artisan test tests/Feature/StaffWelcomeNotificationTest.php
```

Salida esperada: FAIL — "Class StaffWelcomeNotification not found"

- [ ] **Step 3: Crear la notificación**

Crear `app/Notifications/StaffWelcomeNotification.php`:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $plainPassword) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido al Portal de Proveedores TotalGas')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu cuenta de acceso al **Portal de Proveedores TotalGas** ha sido creada.')
            ->line('')
            ->line('**Tus credenciales de acceso:**')
            ->line('• **Usuario:** ' . $notifiable->email)
            ->line('• **Contraseña:** ' . $this->plainPassword)
            ->line('')
            ->action('Iniciar sesión', route('login'))
            ->line('Por seguridad, te recomendamos cambiar tu contraseña después de tu primer acceso.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
```

- [ ] **Step 4: Ejecutar el test para confirmar que pasa**

```bash
php artisan test tests/Feature/StaffWelcomeNotificationTest.php
```

Salida esperada: 2 tests passing

- [ ] **Step 5: Commit**

```bash
git add app/Notifications/StaffWelcomeNotification.php tests/Feature/StaffWelcomeNotificationTest.php
git commit -m "feat: add StaffWelcomeNotification for new staff users"
```

---

## Task 3: Rutas

**Files:**
- Modify: `routes/web.php` (~línea 227)

- [ ] **Step 1: Agregar las dos rutas al grupo `role:superadmin` existente**

Abrir `routes/web.php`. Localizar el grupo:

```php
Route::middleware('role:superadmin')->group(function () {
    Route::get('employees/datatable', [EmployeeController::class, 'datatable'])->name('employees.datatable');
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
});
```

Reemplazarlo por:

```php
Route::middleware('role:superadmin')->group(function () {
    Route::get('employees/datatable', [EmployeeController::class, 'datatable'])->name('employees.datatable');
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/{employee}/promote-form', [EmployeeController::class, 'promoteForm'])->name('employees.promote-form');
    Route::post('employees/{employee}/promote', [EmployeeController::class, 'promote'])->name('employees.promote');
});
```

- [ ] **Step 2: Verificar que las rutas se registran**

```bash
php artisan route:list --name=employees
```

Salida esperada: 4 rutas — `employees.index`, `employees.datatable`, `employees.promote-form`, `employees.promote`

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat: add promote routes for employee-to-user conversion"
```

---

## Task 4: Métodos del Controlador

**Files:**
- Modify: `app/Http/Controllers/EmployeeController.php`
- Create: `tests/Feature/EmployeePromoteTest.php`

- [ ] **Step 1: Escribir los tests que fallan**

Crear `tests/Feature/EmployeePromoteTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePromoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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
}
```

- [ ] **Step 2: Ejecutar los tests para confirmar que fallan**

```bash
php artisan test tests/Feature/EmployeePromoteTest.php
```

Salida esperada: FAIL — necesita `Employee::factory()` y los métodos del controlador. Nota: si `Employee` no tiene factory, el test fallará con "factory not found" — ver Step 3.

- [ ] **Step 3: Crear el factory de Employee si no existe**

Verificar si existe:
```bash
ls database/factories/EmployeeFactory.php 2>/dev/null || echo "MISSING"
```

Si retorna `MISSING`, crear `database/factories/EmployeeFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_number' => $this->faker->unique()->numerify('####'),
            'first_name'      => $this->faker->firstName(),
            'last_name'       => $this->faker->lastName(),
            'email'           => $this->faker->unique()->safeEmail(),
            'phone'           => $this->faker->phoneNumber(),
            'job_title'       => $this->faker->jobTitle(),
            'department'      => $this->faker->word(),
            'company'         => $this->faker->company(),
            'is_active'       => 'SI',
            'user_id'         => null,
        ];
    }
}
```

Agregar `use HasFactory;` al modelo `app/Models/Employee.php` si no lo tiene:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    // ...resto del modelo
}
```

- [ ] **Step 4: Agregar imports y métodos al EmployeeController**

Abrir `app/Http/Controllers/EmployeeController.php`. Agregar estos `use` al bloque de imports (si no están):

```php
use App\Models\User;
use App\Notifications\StaffWelcomeNotification;
use App\Rules\AllowedEmailDomain;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
```

Agregar los dos métodos **antes** del método `index()`:

```php
public function promoteForm(Employee $employee): JsonResponse|View
{
    if ($employee->user_id !== null) {
        return response()->json(['error' => 'Este empleado ya tiene un usuario asignado.'], 409);
    }

    $roles = Role::orderBy('name')->get(['id', 'name']);

    return view('employees.partials.promote-form', compact('employee', 'roles'));
}

public function promote(Employee $employee): JsonResponse
{
    if ($employee->user_id !== null) {
        return response()->json(['error' => 'Este empleado ya tiene un usuario asignado.'], 409);
    }

    $validated = request()->validate([
        'name'      => ['required', 'string', 'max:180'],
        'email'     => ['required', 'email', 'max:180', 'unique:users,email', new AllowedEmailDomain],
        'password'  => ['required', 'string', 'min:8'],
        'phone'     => ['nullable', 'string', 'max:30'],
        'job_title' => ['nullable', 'string', 'max:120'],
        'roles'     => ['nullable', 'array'],
        'roles.*'   => ['string', 'exists:roles,name'],
        'avatar'    => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
    ]);

    $plainPassword = $validated['password'];

    DB::transaction(function () use ($validated, $employee, $plainPassword) {
        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'phone'     => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'is_active' => true,
        ]);

        if (request()->hasFile('avatar')) {
            $path = request()->file('avatar')->store("users/{$user->id}/avatar", 'public');
            $user->update(['avatar' => $path]);
        }

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        $employee->update(['user_id' => $user->id]);

        $user->notify(new StaffWelcomeNotification($plainPassword));
    });

    return response()->json(['success' => true, 'message' => 'Usuario creado y notificado correctamente.']);
}
```

- [ ] **Step 5: Ejecutar los tests para confirmar que pasan**

```bash
php artisan test tests/Feature/EmployeePromoteTest.php
```

Salida esperada: 6 tests passing

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/EmployeeController.php \
        app/Models/Employee.php \
        database/factories/EmployeeFactory.php \
        tests/Feature/EmployeePromoteTest.php
git commit -m "feat: add promoteForm and promote methods to EmployeeController"
```

---

## Task 5: Vista Parcial del Modal

**Files:**
- Create: `resources/views/employees/partials/promote-form.blade.php`

- [ ] **Step 1: Crear el directorio y la vista parcial**

Crear `resources/views/employees/partials/promote-form.blade.php`:

```blade
<div class="modal-header border-bottom-0 pb-1">
    <div>
        <h5 class="modal-title fw-semibold">
            <i class="ti ti-user-plus me-2 text-muted fs-18"></i>
            Crear usuario staff
        </h5>
        <p class="text-muted mb-0 mt-1" style="font-size:12px;">
            Completa los datos para crear el acceso al portal para <strong>{{ $employee->full_name }}</strong>.
        </p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="promoteForm"
      action="{{ route('employees.promote', $employee->id) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf

    <div class="modal-body pt-2">

        {{-- ── Avatar drag & drop ──────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-photo me-1"></i>Foto de perfil
        </p>

        <div class="mb-3">
            <div id="avatarDropZone"
                 class="border border-dashed rounded d-flex flex-column align-items-center justify-content-center p-3"
                 style="cursor:pointer;min-height:120px;border-style:dashed!important;">
                <img id="avatarPreviewPromote"
                     src="{{ asset('images/users/avatar-1.jpg') }}"
                     class="rounded-circle mb-2 d-none"
                     style="width:64px;height:64px;object-fit:cover;"
                     alt="Preview">
                <i id="avatarDropIcon" class="ti ti-photo fs-28 text-muted"></i>
                <span class="text-muted mt-1" style="font-size:12px;">Arrastra tu foto aquí o haz clic</span>
                <span class="text-muted" style="font-size:11px;">JPG, PNG o WEBP · Máx. 2 MB</span>
                <input type="file" name="avatar" id="avatarInputPromote"
                       accept="image/png,image/jpeg,image/webp"
                       class="d-none">
            </div>
        </div>

        {{-- ── Datos personales ─────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-user me-1"></i>Datos personales
        </p>

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Nombre(s)</label>
                <input type="text" name="first_name" class="form-control form-control-sm"
                       value="{{ $employee->first_name }}">
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Apellido(s)</label>
                <input type="text" name="last_name" class="form-control form-control-sm"
                       value="{{ $employee->last_name }}">
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Usuario (nombre completo) <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-sm"
                       value="{{ trim($employee->first_name . ' ' . $employee->last_name) }}"
                       required>
                <div class="invalid-feedback" id="err-name"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Teléfono</label>
                <input type="text" name="phone" class="form-control form-control-sm"
                       value="{{ $employee->phone }}">
                <div class="invalid-feedback" id="err-phone"></div>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-12">
                <label class="form-label form-label-sm mb-1">Puesto</label>
                <input type="text" name="job_title" class="form-control form-control-sm"
                       value="{{ $employee->job_title }}">
                <div class="invalid-feedback" id="err-job_title"></div>
            </div>
        </div>

        {{-- ── Credenciales ──────────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2 mt-3" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-lock me-1"></i>Credenciales de acceso
        </p>

        <div class="row g-2 mb-2">
            <div class="col-md-7">
                <label class="form-label form-label-sm mb-1">Correo electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control form-control-sm"
                       value="{{ $employee->email }}"
                       required>
                <div class="invalid-feedback" id="err-email"></div>
                <div class="form-text">Solo se permiten dominios corporativos del grupo TotalGas.</div>
            </div>
            <div class="col-md-5">
                <label class="form-label form-label-sm mb-1">Contraseña <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control form-control-sm"
                       required minlength="8" autocomplete="new-password">
                <div class="invalid-feedback" id="err-password"></div>
            </div>
        </div>

        {{-- ── Roles ─────────────────────────────────────────────────── --}}
        <p class="text-uppercase text-muted fw-semibold mb-2 mt-3" style="font-size:10px;letter-spacing:.7px;">
            <i class="ti ti-shield me-1"></i>Roles
        </p>

        <div class="row g-2">
            @foreach($roles as $role)
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="roles[]" value="{{ $role->name }}"
                           id="role_{{ $role->id }}">
                    <label class="form-check-label form-label-sm" for="role_{{ $role->id }}">
                        {{ $role->name }}
                    </label>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Errores generales --}}
        <div id="promoteGeneralError" class="alert alert-danger mt-3 d-none"></div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>Cancelar
        </button>
        <button type="submit" class="btn btn-primary btn-sm" id="promoteSubmitBtn">
            <i class="ti ti-user-plus me-1"></i>Crear usuario
        </button>
    </div>
</form>

<script>
(function () {
    // Drag & drop avatar
    const dropZone = document.getElementById('avatarDropZone');
    const fileInput = document.getElementById('avatarInputPromote');
    const preview  = document.getElementById('avatarPreviewPromote');
    const icon     = document.getElementById('avatarDropIcon');

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            icon.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }

    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => showPreview(fileInput.files[0]));
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-primary'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-primary'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-primary');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showPreview(file);
        }
    });
})();
</script>
```

- [ ] **Step 2: Verificar que la vista existe**

```bash
php -r "echo file_exists('resources/views/employees/partials/promote-form.blade.php') ? 'OK' : 'MISSING';"
```

Salida esperada: `OK`

- [ ] **Step 3: Commit**

```bash
git add resources/views/employees/partials/promote-form.blade.php
git commit -m "feat: add promote-form modal partial for employee-to-user conversion"
```

---

## Task 6: Actualizar la Vista Principal

**Files:**
- Modify: `resources/views/employees/index.blade.php`

Esta tarea tiene 3 partes: (A) thead, (B) PHP datatable, (C) JS + modal container.

- [ ] **Step 1: Agregar columna Acciones al thead**

Abrir `resources/views/employees/index.blade.php`. Localizar el `<thead>`:

```html
<th class="text-center" style="width: 80px;">Activo</th>
```

Reemplazarlo por:

```html
<th class="text-center" style="width: 80px;">Activo</th>
<th class="text-end" style="width: 80px;">Acciones</th>
```

- [ ] **Step 2: Agregar la columna actions al método `datatable()` del controlador**

Abrir `app/Http/Controllers/EmployeeController.php`. En el método `datatable()`, reemplazar:

```php
        ->rawColumns(['is_active'])
        ->make(true);
```

Por:

```php
        ->addColumn('actions', function (Employee $row) {
            if ($row->user_id !== null) {
                return '<span class="btn btn-sm btn-outline-secondary disabled"
                              data-bs-toggle="tooltip"
                              title="Ya tiene usuario asignado">
                            <i class="ti ti-user-check"></i>
                        </span>';
            }
            return '<button class="btn btn-sm btn-outline-primary js-promote-btn"
                            data-id="' . $row->id . '"
                            data-bs-toggle="tooltip"
                            title="Crear usuario staff">
                        <i class="ti ti-user-plus"></i>
                    </button>';
        })
        ->rawColumns(['is_active', 'actions'])
        ->make(true);
```

- [ ] **Step 3: Agregar la columna `actions` al JS de DataTables**

En `resources/views/employees/index.blade.php`, localizar el array `columns:` en el JS. Después de la columna `is_active`, agregar:

```js
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
```

- [ ] **Step 4: Agregar el modal container y el JS de interacción**

Al final del `@section('content')`, justo **antes** de `@endsection`, agregar:

```blade
{{-- Modal: Promover a usuario staff --}}
<div class="modal fade" id="promoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="promoteModalContent">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</div>
```

En `@push('scripts')`, agregar **después** del bloque DataTables existente:

```js
        // ── Promote Employee to User ──────────────────────────────────────
        const promoteModal = new bootstrap.Modal(document.getElementById('promoteModal'));

        $(document).on('click', '.js-promote-btn', function () {
            const employeeId = $(this).data('id');
            const url = `/employees/${employeeId}/promote-form`;

            $('#promoteModalContent').html(
                '<div class="modal-body text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );
            promoteModal.show();

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (res.status === 409) {
                    promoteModal.hide();
                    return res.json().then(data => {
                        Swal.fire({ icon: 'warning', title: 'Atención', text: data.error });
                    });
                }
                return res.text().then(html => {
                    $('#promoteModalContent').html(html);
                });
            })
            .catch(() => {
                promoteModal.hide();
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el formulario.' });
            });
        });

        $(document).on('submit', '#promoteForm', function (e) {
            e.preventDefault();

            const form    = this;
            const url     = form.action;
            const formData = new FormData(form);
            const submitBtn = document.getElementById('promoteSubmitBtn');

            // Limpiar errores previos
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            document.getElementById('promoteGeneralError')?.classList.add('d-none');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creando...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                if (status === 200 && data.success) {
                    promoteModal.hide();
                    employeesTable.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: '¡Listo!', text: data.message, timer: 3000, showConfirmButton: false });
                } else if (status === 422 && data.errors) {
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        const input = form.querySelector(`[name="${field}"]`);
                        const errEl = document.getElementById(`err-${field}`);
                        if (input) input.classList.add('is-invalid');
                        if (errEl) errEl.textContent = messages[0];
                    });
                } else {
                    const errEl = document.getElementById('promoteGeneralError');
                    if (errEl) { errEl.textContent = data.error || 'Ocurrió un error.'; errEl.classList.remove('d-none'); }
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo completar la operación.' });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-user-plus me-1"></i>Crear usuario';
            });
        });
```

- [ ] **Step 5: Verificar con artisan**

```bash
php artisan route:list --name=employees
php artisan view:clear
```

Salida esperada: 4 rutas de employees, sin errores de compilación de vistas.

- [ ] **Step 6: Commit**

```bash
git add resources/views/employees/index.blade.php \
        app/Http/Controllers/EmployeeController.php
git commit -m "feat: add actions column and promote modal to employees index"
```

---

## Task 7: Ejecutar Todos los Tests y Commit Final

- [ ] **Step 1: Ejecutar la suite completa de employees**

```bash
php artisan test tests/Feature/EmployeePromoteTest.php tests/Feature/AllowedEmailDomainTest.php tests/Feature/StaffWelcomeNotificationTest.php tests/Feature/EmployeeCatalogTest.php
```

Salida esperada: todos los tests pasando (mínimo 15 tests)

- [ ] **Step 2: Verificación manual en el navegador**

1. `php artisan serve`
2. Iniciar sesión como superadmin
3. Navegar a `/employees`
4. Verificar que aparece la columna "Acciones" con botones `ti-user-plus`
5. Hacer clic en un botón → verificar que abre el modal con datos precargados
6. Verificar drag & drop de imagen
7. Llenar formulario con email de dominio no permitido → verificar error
8. Llenar con datos válidos → verificar creación de usuario, toast de éxito y recarga de tabla
9. Verificar que el botón del empleado recién promovido aparece como `ti-user-check` deshabilitado

- [ ] **Step 3: Commit final**

```bash
git add .
git commit -m "feat: employee promote-to-staff-user complete (rule, notification, controller, views, tests)"
```
