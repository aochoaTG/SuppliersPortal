# Catálogo de Empleados — Diseño

**Fecha:** 2026-04-16  
**Proyecto:** Portal de Proveedores TotalGas  
**Stack:** Laravel 12 · PHP 8.2 · Yajra DataTables · Spatie Permission

---

## Contexto

El modelo `Employee` ya existe y se llena diariamente desde el sistema externo **TRESS** vía el endpoint API `POST /api/empleados/recibir`. El `EmployeeController` ya contiene la lógica de importación (`recibir`, `resolverLideresPendientes`).

Se requiere una vista de solo lectura para que los usuarios con rol `superadmin` o `buyer` puedan consultar el catálogo completo de empleados. **No se permite crear, editar ni eliminar** registros desde la interfaz web.

---

## Alcance

| Componente | Descripción |
|---|---|
| Ruta `GET /employees` | Carga la vista del catálogo |
| Ruta `GET /employees/datatable` | Endpoint JSON para Yajra DataTables |
| `EmployeeController::index()` | Retorna la vista |
| `EmployeeController::datatable()` | Retorna JSON paginado con columnas acordadas |
| Vista `employees/index.blade.php` | Tabla DataTables de solo lectura |
| Sidebar | Entrada "Empleados" bajo sección CATÁLOGOS |

---

## Rutas

Dentro del grupo web protegido con middleware `auth` y `lock`, junto a las demás rutas de catálogos:

```php
Route::get('employees/datatable', [EmployeeController::class, 'datatable'])->name('employees.datatable');
Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
```

No se registra `Route::resource` — solo las dos rutas GET necesarias.

---

## Controlador

### `index()`

```php
public function index(): View
{
    return view('employees.index');
}
```

### `datatable()`

Consulta `Employee::query()` y devuelve las siguientes columnas:

| Columna DataTables | Campo/Cálculo |
|---|---|
| `id` | `employees.id` |
| `employee_number` | `employees.employee_number` |
| `full_name` | Concatenación `first_name + ' ' + last_name` (columna calculada) |
| `company` | `employees.company` |
| `department` | `employees.department` |
| `job_title` | `employees.job_title` |
| `leader` | `employees.leader` |
| `is_active` | Badge `<span class="badge bg-success">SI</span>` o `<span class="badge bg-danger">NO</span>` |

- `full_name` se genera con `addColumn` y se escapa con `e()`.
- `is_active` se genera con `editColumn` y se marca como `rawColumns`.
- Sin columna de acciones (solo lectura).
- Ordenación por defecto: `employee_number ASC`.

---

## Vista

**Archivo:** `resources/views/employees/index.blade.php`

- `@extends('layouts.zircos')`
- Título: `Empleados`
- Breadcrumb: `Inicio > Empleados`
- Tabla `#employeesTable` con `table-bordered table-hover w-100`
- **Toolbar DataTables:** Excel, Copiar, PDF — sin botón "Nuevo"
- Idioma: `assets/vendor/datatables.net/es-MX.json`
- `pageLength: 50`

---

## Sidebar

**Archivo:** `resources/views/layouts/partials/sidebar.blade.php`

Dentro del bloque `@hasanyrole('superadmin|buyer')`, sección `CATÁLOGOS`, después del ítem "Usuarios Staff" y antes de "Usuarios Proveedor". El ítem se envuelve en `@hasrole('superadmin')` para restringirlo a ese rol:

```blade
@hasrole('superadmin')
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}"
        href="{{ route('employees.index') }}">
        <span class="menu-icon"><i class="ti ti-id-badge"></i></span>
        <span class="menu-text">Empleados</span>
    </a>
</li>
@endhasrole
```

---

## Restricciones

- **Solo el rol `superadmin`** puede ver y acceder al catálogo de empleados. El ítem del sidebar se envuelve en `@hasrole('superadmin')/@endhasrole`.
- No hay middleware adicional en las rutas — el grupo ya tiene `auth` + `lock`.
- No hay paginación server-side personalizada fuera de lo que provee Yajra DataTables.
- No se toca la lógica de importación existente en `EmployeeController`.
