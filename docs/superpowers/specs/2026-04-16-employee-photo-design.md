# Employee Photo — Diseño

**Fecha:** 2026-04-16
**Proyecto:** Portal de Proveedores TotalGas
**Stack:** Laravel 12 · PHP 8.2 · SQL Server · Bootstrap 5 · Laravel Storage (public disk)

---

## Contexto

La tabla `employees` no tiene columna de fotografía. Se requiere agregarla para:
1. Que el superadmin pueda cargar/reemplazar la foto de cualquier empleado directamente desde la tabla.
2. Que la tabla muestre un thumbnail circular con la foto de cada empleado (clickeable para ver la foto completa).
3. Que al promover un empleado a usuario staff, la foto del empleado se copie automáticamente como avatar del nuevo usuario y se muestre pre-cargada en el modal de promoción.

La fotografía del empleado **nunca proviene de TRESS** (el sync API externo). El campo `photo` se gestionará exclusivamente desde el portal. Por la misma razón, `recibir()` — el endpoint de sincronización TRESS — no debe tocar ni `photo` ni `user_id` en ningún caso (create ni update).

---

## Alcance

| Componente | Descripción |
|---|---|
| `database/migrations/2025_09_22_100028_create_employees_table.php` | Agregar columna `photo` nullable |
| `app/Models/Employee.php` | Agregar `photo` a `$fillable` |
| `app/Http/Controllers/EmployeeController.php` — `recibir()` | Sin cambios (ya excluye `photo` y `user_id` por diseño) |
| `app/Http/Controllers/EmployeeController.php` — `uploadPhoto()` | Nuevo método: valida, almacena y actualiza `$employee->photo` |
| `app/Http/Controllers/EmployeeController.php` — `photoForm()` | Nuevo método: retorna vista parcial del modal de carga |
| `app/Http/Controllers/EmployeeController.php` — `promote()` | Si no se sube avatar nuevo y `$employee->photo` existe, copiar archivo al directorio del usuario |
| `routes/web.php` | Agregar rutas `photo-form` y `photo` dentro del grupo `role:superadmin` |
| `resources/views/employees/partials/photo-form.blade.php` | Nuevo modal drag & drop para carga de foto del empleado |
| `resources/views/employees/partials/promote-form.blade.php` | Pre-cargar preview del avatar drag & drop si `$employee->photo` tiene valor |
| `resources/views/employees/index.blade.php` | Columna thumbnail + modal preview + modal upload + botón en acciones + JS |

---

## Rutas

Dentro del grupo `role:superadmin` existente en `routes/web.php`:

```php
Route::get('employees/{employee}/photo-form',  [EmployeeController::class, 'photoForm'])->name('employees.photo-form');
Route::post('employees/{employee}/photo',       [EmployeeController::class, 'uploadPhoto'])->name('employees.upload-photo');
```

---

## Migración

Agregar antes de `$table->timestamps()`:

```php
$table->string('photo', 500)->nullable();
```

El campo almacena el path relativo al disco `public` (igual que `avatar` en `users`), por ejemplo: `employees/42/photo/filename.jpg`.

---

## Modelo Employee

Agregar `'photo'` al array `$fillable`.

`recibir()` usa `fill([...])` con lista explícita que nunca incluye `photo` ni `user_id`. La protección es implícita — no se requiere ningún cambio en ese método.

---

## Controlador — `photoForm(Employee $employee)`

```php
public function photoForm(Employee $employee): View
{
    return view('employees.partials.photo-form', compact('employee'));
}
```

---

## Controlador — `uploadPhoto(Employee $employee)`

Validaciones:
- `photo`: required, image, max:2048, mimes:jpg,jpeg,png,webp

Lógica:
1. Si el empleado ya tiene foto, eliminar el archivo anterior: `Storage::disk('public')->delete($employee->photo)`
2. Almacenar el nuevo archivo: `$path = $request->file('photo')->store("employees/{$employee->id}/photo", 'public')`
3. `$employee->update(['photo' => $path])`
4. Retornar `response()->json(['success' => true, 'photo_url' => Storage::url($path)])`

---

## Controlador — `promote()`

Dentro del `DB::transaction`, después de crear el usuario:

```php
if (request()->hasFile('avatar')) {
    $path = request()->file('avatar')->store("users/{$user->id}/avatar", 'public');
    $user->update(['avatar' => $path]);
} elseif ($employee->photo) {
    $filename = basename($employee->photo);
    $newPath  = "users/{$user->id}/avatar/{$filename}";
    Storage::disk('public')->copy($employee->photo, $newPath);
    $user->update(['avatar' => $newPath]);
}
```

Se copia el archivo (no solo el path) para que el avatar del usuario tenga ciclo de vida independiente a la foto del empleado. Se debe importar `Illuminate\Support\Facades\Storage` en el controlador.

---

## Vista Parcial — `photo-form.blade.php`

Modal de carga de foto del empleado. Mismo patrón drag & drop que `promote-form.blade.php`:

- Título: "Fotografía de {{ $employee->full_name }}"
- Si el empleado ya tiene foto (`$employee->photo`), pre-cargar el preview circular con `Storage::url($employee->photo)`
- Input file `name="photo"`, accept jpg/jpeg/png/webp, máx. 2 MB
- Botón submit: "Guardar foto"
- `action`: `route('employees.upload-photo', $employee->id)`
- `enctype="multipart/form-data"`, submit via AJAX con `FormData`
- IDs del drag & drop: `#photoDropZone`, `#photoInput`, `#photoPreview`, `#photoDropIcon` (distintos a los del promote form para evitar conflictos)

---

## Vista Parcial — `promote-form.blade.php`

Si `$employee->photo` tiene valor, se pre-carga el preview circular del avatar drag & drop:

```blade
@php $employeePhotoUrl = $employee->photo ? Storage::url($employee->photo) : null; @endphp
```

En el `<img>` del preview (`#avatarPreviewPromote`):
- Si `$employeePhotoUrl` existe: `src="{{ $employeePhotoUrl }}"` y visible (sin clase `d-none`)
- Si no: oculto hasta que el usuario arrastra una foto

En el ícono `#avatarDropIcon`: oculto si hay foto, visible si no.

---

## Vista Principal — `employees/index.blade.php`

### Columna `photo` en `datatable()` del controlador

```php
->addColumn('photo', function (Employee $row) {
    if ($row->photo) {
        $url = Storage::url($row->photo);
        return '<img src="' . $url . '"
                     class="rounded-circle js-photo-preview"
                     data-url="' . $url . '"
                     style="width:36px;height:36px;object-fit:cover;cursor:pointer;"
                     alt="Foto">';
    }
    $initial = strtoupper(mb_substr($row->first_name ?? '?', 0, 1));
    return '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                 style="width:36px;height:36px;font-size:14px;font-weight:600;">' . $initial . '</div>';
})
```

Agregar `'photo'` al `select([...])` de la query.

Agregar `'photo'` a `->rawColumns([...])`.

### Columna `photo` en `thead`

```html
<th style="width:52px;"></th>
```

Primera columna visible, antes de `#`.

### Columna `photo` en JS DataTables

```js
{ data: 'photo', name: 'photo', orderable: false, searchable: false, className: 'text-center' }
```

Primera entrada en el array `columns`, antes de `id`.

### Botón de foto en `actions`

En `addColumn('actions', ...)`, agregar botón de cámara junto al de promover:

```php
$photoBtn = '<button class="btn btn-sm btn-outline-info js-photo-btn me-1"
                     data-id="' . $row->id . '"
                     data-bs-toggle="tooltip"
                     title="Cargar fotografía">
                 <i class="ti ti-camera"></i>
             </button>';
```

El botón de foto aparece siempre, independientemente de si el empleado ya tiene usuario.

### Modales en `@section('content')`

```html
{{-- Modal: Preview de foto grande --}}
<div class="modal fade" id="photoPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <img id="photoPreviewImg" src="" class="img-fluid rounded" style="max-height:80vh;" alt="Foto">
            </div>
        </div>
    </div>
</div>

{{-- Modal: Carga de foto del empleado --}}
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="photoUploadModalContent">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</div>
```

### JS en `@push('scripts')`

```js
// Preview de foto grande
const photoPreviewModal = new bootstrap.Modal(document.getElementById('photoPreviewModal'));
$(document).on('click', '.js-photo-preview', function () {
    document.getElementById('photoPreviewImg').src = $(this).data('url');
    photoPreviewModal.show();
});

// Carga de foto
const photoUploadModal = new bootstrap.Modal(document.getElementById('photoUploadModal'));
$(document).on('click', '.js-photo-btn', function () {
    const employeeId = $(this).data('id');
    $('#photoUploadModalContent').html(
        '<div class="modal-body text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
    );
    photoUploadModal.show();
    fetch(`/employees/${employeeId}/photo-form`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text().then(html => {
        $('#photoUploadModalContent').html(html);
    }))
    .catch(() => {
        photoUploadModal.hide();
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el formulario.' });
    });
});

$(document).on('submit', '#photoForm', function (e) {
    e.preventDefault();
    const form      = this;
    const url       = form.action;
    const formData  = new FormData(form);
    const submitBtn = document.getElementById('photoSubmitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

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
            photoUploadModal.hide();
            employeesTable.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: '¡Listo!', text: 'Fotografía actualizada.', timer: 2500, showConfirmButton: false });
        } else if (status === 422 && data.errors) {
            const errEl = document.getElementById('err-photo');
            const input = form.querySelector('[name="photo"]');
            if (input) input.classList.add('is-invalid');
            if (errEl) errEl.textContent = Object.values(data.errors).flat()[0];
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la fotografía.' });
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Guardar foto';
    });
});
```

---

## Restricciones

- `recibir()` nunca recibe ni escribe `photo` (ni en create ni en update)
- `recibir()` nunca escribe `user_id` (protección ya existente)
- El archivo de foto se copia, no se mueve, al promover (el empleado conserva su foto)
- Al reemplazar una foto existente en `uploadPhoto()`, el archivo anterior se elimina del storage
- Solo superadmin puede cargar/cambiar fotos (rutas dentro del grupo `role:superadmin`)
- Se usa `migrate:fresh --seed` para aplicar la migración (confirmado por el usuario)
