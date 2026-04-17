# Employee Photo — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Agregar fotografía a empleados: columna thumbnail en la tabla (clickeable para ver foto grande), modal drag & drop para cargar/reemplazar la foto, y al promover a usuario staff copiar la foto automáticamente como avatar.

**Architecture:** Se agrega `photo` a la migración y modelo. Dos nuevos métodos en `EmployeeController` (`photoForm` y `uploadPhoto`) con sus rutas. Nueva vista parcial `photo-form.blade.php`. La vista `employees/index.blade.php` recibe columna de thumbnail, dos modales de Bootstrap (preview + upload) y JS de interacción. El promote flow copia el archivo si no se sube avatar nuevo.

**Tech Stack:** Laravel 12 · PHP 8.2 · SQL Server · Laravel Storage (public disk) · Bootstrap 5 Modal · Fetch API · PHPUnit

---

## File Map

| Acción | Archivo | Responsabilidad |
|---|---|---|
| Modificar | `database/migrations/2025_09_22_100028_create_employees_table.php` | Agregar columna `photo` |
| Modificar | `app/Models/Employee.php` | Agregar `photo` a `$fillable` |
| Modificar | `database/factories/EmployeeFactory.php` | Agregar `photo => null` |
| Modificar | `routes/web.php` | Dos rutas nuevas: `photo-form` y `upload-photo` |
| Modificar | `app/Http/Controllers/EmployeeController.php` | Agregar `photoForm()`, `uploadPhoto()`, actualizar `promote()` y `datatable()` |
| Crear | `resources/views/employees/partials/photo-form.blade.php` | Modal drag & drop para cargar foto de empleado |
| Modificar | `resources/views/employees/partials/promote-form.blade.php` | Pre-cargar preview si `$employee->photo` tiene valor |
| Modificar | `resources/views/employees/index.blade.php` | Columna thumbnail + modal preview + modal upload + botón cámara + JS |
| Modificar | `tests/Feature/EmployeePromoteTest.php` | Tests de upload, promote con foto, preview en modales |

---

## Task 1: Migración, Modelo y Factory

**Files:**
- Modify: `database/migrations/2025_09_22_100028_create_employees_table.php`
- Modify: `app/Models/Employee.php`
- Modify: `database/factories/EmployeeFactory.php`

- [x] **Step 1: Agregar columna `photo` a la migración**
- [x] **Step 2: Agregar `photo` al modelo**
- [x] **Step 3: Agregar `photo` al factory**
- [x] **Step 4: Ejecutar migrate:fresh --seed**
- [x] **Step 5: Verificar que los tests existentes siguen pasando**
- [x] **Step 6: Commit**

---

## Task 2: Rutas

**Files:**
- Modify: `routes/web.php`

- [x] **Step 1: Agregar las dos rutas nuevas al grupo `role:superadmin`**
- [x] **Step 2: Verificar que las rutas se registran**
- [x] **Step 3: Commit**

---

## Task 3: Controlador — photoForm() y uploadPhoto()

**Files:**
- Modify: `app/Http/Controllers/EmployeeController.php`
- Modify: `tests/Feature/EmployeePromoteTest.php`

- [x] **Step 1: Escribir los tests que fallan**
- [x] **Step 2: Ejecutar los tests para confirmar que fallan**
- [x] **Step 3: Agregar import de Storage y los dos métodos al controlador**
- [x] **Step 4: Ejecutar los tests para confirmar que pasan**
- [x] **Step 5: Commit**

---

## Task 4: Vista Parcial — photo-form.blade.php

**Files:**
- Create: `resources/views/employees/partials/photo-form.blade.php`

- [x] **Step 1: Crear la vista parcial**
- [x] **Step 2: Verificar que el test de photoForm pasa**
- [x] **Step 3: Commit**

---

## Task 5: Vista Principal — Columna thumbnail, modales y JS

**Files:**
- Modify: `app/Http/Controllers/EmployeeController.php` (método `datatable()`)
- Modify: `resources/views/employees/index.blade.php`

- [x] **Step 1: Agregar `photo` al select y addColumn en `datatable()`**
- [x] **Step 2: Agregar botón de foto en la columna `actions`**
- [x] **Step 3: Actualizar el thead de la tabla en `index.blade.php`**
- [x] **Step 4: Agregar los dos modales nuevos en `@section('content')`**
- [x] **Step 5: Actualizar las columnas JS del DataTable y agregar JS de foto**
- [x] **Step 6: Verificar con artisan que no hay errores de sintaxis**
- [x] **Step 7: Commit**

---

## Task 6: promote() — Copiar foto del empleado al usuario

**Files:**
- Modify: `app/Http/Controllers/EmployeeController.php`
- Modify: `tests/Feature/EmployeePromoteTest.php`

- [x] **Step 1: Escribir los tests que fallan**
- [x] **Step 2: Ejecutar los tests para confirmar que fallan**
- [x] **Step 3: Actualizar promote() para copiar la foto**
- [x] **Step 4: Ejecutar los tests para confirmar que pasan**
- [x] **Step 5: Commit**

---

## Task 7: Vista Parcial promote-form — Preview de foto del empleado

**Files:**
- Modify: `resources/views/employees/partials/promote-form.blade.php`
- Modify: `tests/Feature/EmployeePromoteTest.php`

- [x] **Step 1: Escribir el test que falla**
- [x] **Step 2: Ejecutar los tests para confirmar que fallan**
- [x] **Step 3: Actualizar la vista parcial**
- [x] **Step 4: Ejecutar los tests para confirmar que pasan**
- [x] **Step 5: Commit**

---

## Task 8: Suite Completa y Verificación

- [x] **Step 1: Ejecutar todos los tests relacionados**
- [x] **Step 2: Verificar en navegador**
- [x] **Step 3: Commit final**
