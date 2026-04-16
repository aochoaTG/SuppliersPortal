# Promover Empleado a Usuario Staff — Diseño

**Fecha:** 2026-04-16  
**Proyecto:** Portal de Proveedores TotalGas  
**Stack:** Laravel 12 · PHP 8.2 · Spatie Permission · Yajra DataTables · Bootstrap 5 · Tabler Icons

---

## Contexto

El catálogo de empleados (`/employees`) ya existe como vista de solo lectura sincronizada desde TRESS. Se requiere que el superadmin pueda convertir un empleado en usuario staff del portal directamente desde esa tabla, sin navegar a otra pantalla.

---

## Alcance

| Componente | Descripción |
|---|---|
| Ruta `GET /employees/{employee}/promote-form` | Retorna HTML del modal precargado |
| Ruta `POST /employees/{employee}/promote` | Crea usuario, asigna roles, vincula empleado, envía email |
| `EmployeeController::promoteForm()` | Carga datos del empleado en la vista parcial |
| `EmployeeController::promote()` | Lógica de creación y notificación |
| `StaffWelcomeNotification` | Email de bienvenida con credenciales |
| Vista parcial `employees/partials/promote-form.blade.php` | Formulario modal con drag & drop de avatar |
| Vista `employees/index.blade.php` | Columna `actions` + modal container + JS |

---

## Rutas

Dentro del grupo `role:superadmin` existente en `routes/web.php`:

```php
Route::get('employees/{employee}/promote-form', [EmployeeController::class, 'promoteForm'])->name('employees.promote-form');
Route::post('employees/{employee}/promote', [EmployeeController::class, 'promote'])->name('employees.promote');
```

---

## Controlador

### `promoteForm(Employee $employee)`

```php
public function promoteForm(Employee $employee): JsonResponse|View
{
    if ($employee->user_id !== null) {
        return response()->json(['error' => 'Este empleado ya tiene un usuario asignado.'], 409);
    }

    $roles = Role::orderBy('name')->get(['id', 'name']);

    return view('employees.partials.promote-form', compact('employee', 'roles'));
}
```

### `promote(Employee $employee)`

Validaciones:
- `name`: required, string, max:180
- `email`: required, email, max:180, unique:users,email, dominio en lista permitida (Rule personalizada `AllowedEmailDomain`)
- `password`: required, string, min:8
- `phone`: nullable, string, max:30
- `job_title`: nullable, string, max:120
- `roles`: nullable, array
- `roles.*`: string, exists:roles,name
- `avatar`: nullable, image, max:2048, mimes:jpg,jpeg,png,webp

Lógica (dentro de `DB::transaction`):
1. `$plainPassword = $validated['password']`
2. `User::create(...)` — password se hashea via cast del modelo
3. Si hay avatar: `$request->file('avatar')->store("users/{$user->id}/avatar", 'public')` → `$user->update(['avatar' => $path])`
4. Si hay roles: `$user->syncRoles($validated['roles'])`
5. `$employee->update(['user_id' => $user->id])`
6. `$user->notify(new StaffWelcomeNotification($plainPassword))`

Respuesta éxito: `response()->json(['success' => true, 'message' => 'Usuario creado y notificado correctamente.'])`

Respuesta error validación: Laravel retorna 422 automáticamente con `errors`.

---

## Regla de Validación — Dominios Permitidos

**Archivo:** `app/Rules/AllowedEmailDomain.php`

Implementa `ValidationRule`. Extrae el dominio del email con `Str::after($value, '@')` y verifica que esté en la lista de dominios corporativos permitidos. Si no coincide, el mensaje de error es:

> "El dominio del correo no está permitido. Solo se aceptan correos corporativos del grupo TotalGas."

**Dominios permitidos:**
```php
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
```

---

## Notificación

**Archivo:** `app/Notifications/StaffWelcomeNotification.php`

- Canal: `mail` únicamente (no `database`)
- Constructor recibe `string $plainPassword`
- Asunto: `Bienvenido al Portal de Proveedores TotalGas`
- Contenido del email:
  - Saludo con nombre del usuario
  - Línea: tus credenciales de acceso
  - `Usuario:` email del usuario
  - `Contraseña:` `$plainPassword`
  - Botón de acción: `Iniciar sesión` → `route('login')`
  - Aviso: cambiar contraseña en primer acceso (recomendación, no forzado)

---

## Vista Parcial — Modal Form

**Archivo:** `resources/views/employees/partials/promote-form.blade.php`

Campos del formulario:

| Campo | Tipo | Pre-llenado |
|---|---|---|
| Nombre(s) `first_name` | text | `$employee->first_name` |
| Apellido(s) `last_name` | text | `$employee->last_name` |
| Usuario `name` | text | `$employee->first_name . ' ' . $employee->last_name` |
| Email `email` | email | `$employee->email` |
| Teléfono `phone` | text | `$employee->phone` |
| Puesto `job_title` | text | `$employee->job_title` |
| Contraseña `password` | password | vacío |
| Roles `roles[]` | checkboxes | ninguno pre-seleccionado |
| Avatar `avatar` | drag & drop | vacío (opcional) |

**Drag & drop de avatar:**
- Zona de drop con dashed border, ícono `ti ti-photo` y texto "Arrastra tu foto aquí o haz clic"
- Al soltar o seleccionar: muestra preview circular (igual al patrón de `users/staff/partials/form.blade.php`)
- Input `type="file"` oculto activado por click en la zona

El formulario hace POST via AJAX a `route('employees.promote', $employee->id)` con `FormData` para soportar el archivo.

---

## Vista Principal — Cambios en `employees/index.blade.php`

### Columna actions en DataTables PHP (`datatable()`)

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
```

### Columna en thead de la tabla

```html
<th class="text-end" style="width:80px;">Acciones</th>
```

### Columna en JS DataTables

```js
{
    data: 'actions',
    name: 'actions',
    orderable: false,
    searchable: false,
    className: 'text-end'
}
```

### Modal container y JS

Al final del `@section('content')`, antes de `@endsection`:

```html
<div class="modal fade" id="promoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="promoteModalContent">
            <!-- Se carga via AJAX -->
        </div>
    </div>
</div>
```

JS en `@push('scripts')`:
- Click en `.js-promote-btn` → `fetch(route + '/' + id + '/promote-form')` → inyecta HTML en `#promoteModalContent` → `Modal.show()`
- Si respuesta 409 → toast de advertencia "Este empleado ya tiene usuario"
- Submit del form dentro del modal → `FormData` → `fetch POST` → si 200: toast éxito, cierra modal, `employeesTable.ajax.reload()` → si 422: muestra errores inline bajo cada campo

---

## Restricciones

- Ambas rutas bajo `role:superadmin` (mismo grupo que las rutas existentes de empleados)
- La contraseña se envía en texto plano **únicamente en el email de bienvenida** — en BD siempre se guarda hasheada
- No se fuerza cambio de contraseña en primer login (el sistema no tiene ese flujo actualmente)
- El campo `user_id` en `employees` ya existe en el modelo (está en `$fillable`)
