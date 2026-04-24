# Catálogo SAT Retenciones — Diseño

**Fecha:** 2026-04-24  
**Proyecto:** SuppliersPortal (TotalGas)  
**Stack:** Laravel 12 · PHP 8.2 · SQL Server · Yajra DataTables · Bootstrap 5 · Tabler Icons

---

## Objetivo

Exponer el catálogo `sat_retenciones` (ya existente en BD y seeder) como una sección CRUD en el sidebar del portal, accesible únicamente para el rol `superadmin`. Permite crear, editar, eliminar (físico) y activar/desactivar retenciones del SAT.

---

## Acceso y permisos

- **Solo `superadmin`** puede ver y operar esta sección.
- Las rutas se registran dentro del grupo `auth + lock` ya existente en `web.php`.
- No se crea middleware adicional; el control de rol se aplica en el controlador con `$this->authorize()` o `abort_unless(auth()->user()->hasRole('superadmin'), 403)`.

---

## Archivos a crear / modificar

### Nuevos

| Archivo | Tipo |
|---|---|
| `app/Http/Controllers/SatRetencionController.php` | Controlador CRUD + datatable |
| `app/Http/Requests/SaveSatRetencionRequest.php` | Form Request validación |
| `resources/views/sat_retenciones/index.blade.php` | Vista listado |
| `resources/views/sat_retenciones/create.blade.php` | Vista crear |
| `resources/views/sat_retenciones/edit.blade.php` | Vista editar |
| `resources/views/sat_retenciones/partials/form.blade.php` | Partial campos |

### Modificados

| Archivo | Cambio |
|---|---|
| `routes/web.php` | 2 líneas: datatable GET + Route::resource |
| `resources/views/layouts/partials/sidebar-staff.blade.php` | 1 entrada en accordion CONFIGURACIÓN |

---

## Rutas

```php
Route::get('sat-retenciones/datatable', [SatRetencionController::class, 'datatable'])
    ->name('sat-retenciones.datatable');

Route::resource('sat-retenciones', SatRetencionController::class)
    ->except(['show'])
    ->parameters(['sat-retenciones' => 'sat_retencion']);
```

Rutas generadas:

| Verbo | URI | Nombre | Método |
|---|---|---|---|
| GET | `/sat-retenciones` | `sat-retenciones.index` | `index()` |
| GET | `/sat-retenciones/create` | `sat-retenciones.create` | `create()` |
| POST | `/sat-retenciones` | `sat-retenciones.store` | `store()` |
| GET | `/sat-retenciones/{sat_retencion}/edit` | `sat-retenciones.edit` | `edit()` |
| PUT | `/sat-retenciones/{sat_retencion}` | `sat-retenciones.update` | `update()` |
| DELETE | `/sat-retenciones/{sat_retencion}` | `sat-retenciones.destroy` | `destroy()` |
| GET | `/sat-retenciones/datatable` | `sat-retenciones.datatable` | `datatable()` |

---

## Controlador (`SatRetencionController`)

Métodos:

- `index()` → retorna vista `sat_retenciones.index`
- `create()` → instancia `new SatRetencion(['activo' => true, 'requiere_cfdi_retencion' => true])`, retorna vista `sat_retenciones.create`
- `store(SaveSatRetencionRequest $request)` → `SatRetencion::create(...)`, redirect index con `success`
- `edit(SatRetencion $sat_retencion)` → retorna vista `sat_retenciones.edit`
- `update(SaveSatRetencionRequest $request, SatRetencion $sat_retencion)` → `$sat_retencion->update(...)`, redirect index con `success`
- `destroy(SatRetencion $sat_retencion)` → `$sat_retencion->delete()`, redirect index con `success`
- `datatable()` → Yajra DataTables sobre `SatRetencion::query()`

Todos los métodos abren con `abort_unless(auth()->user()->hasRole('superadmin'), 403)`.

---

## DataTable — columnas

| Columna DB | Label | Ancho | Formato |
|---|---|---|---|
| `clave` | Clave | 90px | texto plano |
| `nombre` | Nombre | — | texto plano |
| `impuesto` | Impuesto | 80px | badge: ISR=`bg-primary` / IVA=`bg-warning text-dark` |
| `porcentaje_display` | Porcentaje | 160px | texto plano |
| `requiere_cfdi_retencion` | CFDI Ret. | 90px | badge: Sí=`bg-success` / No=`bg-secondary` |
| `activo` | Activo | 80px | badge: Activo=`bg-success` / Inactivo=`bg-secondary` |
| acciones | Acciones | 100px | botón editar (`outline-primary`) + eliminar (`outline-danger`) con SweetAlert |

Configuración DataTable: `processing: true`, `serverSide: true`, `pageLength: 50`, botones: Nueva retención + Excel + PDF + Copy.

---

## Form Request (`SaveSatRetencionRequest`)

```
clave               required | string | max:20  | unique:sat_retenciones (ignore en update)
nombre              required | string | max:100
impuesto            required | in:ISR,IVA
porcentaje          nullable | numeric | min:0 | max:100
porcentaje_display  required | string | max:100
base_calculo        required | string | max:255
aplica_cuando       required | string | max:255
base_legal          required | string | max:100
descripcion         required | string | max:255
requiere_cfdi_retencion  required | boolean
notas               nullable | string
activo              required | boolean
```

Mensajes en español para `clave.unique`, `impuesto.in`, campos `required`.

---

## Layout del formulario

```
[ Clave* ]              [ Nombre* ]
[ Impuesto* (select) ]  [ Base legal* ]
[ Porcentaje (dec) ]    [ Porcentaje display* ]
[ Base de cálculo* ─────────────────────────── ]
[ Aplica cuando* ────────────────────────────── ]
[ Descripción* ──────────────────────────────── ]
[ Notas (textarea) ──────────────────────────── ]
[ Requiere CFDI retención (switch) ] [ Activo (switch) ]
```

Patrón: `col-md-6` para filas de dos columnas, `col-12` para campos anchos. Switch con hidden input antes del checkbox (captura 0 si desmarcado). `old()` + `@error()` en todos los campos.

---

## Sidebar

Archivo: `resources/views/layouts/partials/sidebar-staff.blade.php`  
Sección: accordion **CONFIGURACIÓN** (ya existe, solo `superadmin`)  
Posición: después de "Niveles de Autorización"

```blade
<li class="side-nav-item">
    <a href="{{ route('sat-retenciones.index') }}"
       class="side-nav-link {{ request()->routeIs('sat-retenciones.*') ? 'active' : '' }}">
        <span class="menu-text">Retenciones SAT</span>
    </a>
</li>
```

La variable `$openConfiguracion` (o equivalente) del sidebar debe incluir `request()->routeIs('sat-retenciones.*')` para que el accordion quede abierto al navegar a esta sección.

---

## Mensajes flash

| Acción | Mensaje |
|---|---|
| store | `'Retención creada correctamente.'` |
| update | `'Retención actualizada correctamente.'` |
| destroy | `'Retención eliminada correctamente.'` |

---

## Eliminación

Eliminación física (`$model->delete()`). Confirmación via SweetAlert2 con el patrón `js-delete-form` / `js-delete-btn` ya establecido en el proyecto. El `data-entity` del botón muestra `clave - nombre` para identificar el registro en el diálogo.
