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

- [x] **Step 1: Escribir el test que falla**
- [x] **Step 2: Ejecutar el test para confirmar que falla**
- [x] **Step 3: Crear la regla**
- [x] **Step 4: Ejecutar el test para confirmar que pasa**
- [x] **Step 5: Commit**

---

## Task 2: StaffWelcomeNotification

**Files:**
- Create: `app/Notifications/StaffWelcomeNotification.php`
- Create: `tests/Feature/StaffWelcomeNotificationTest.php`

- [x] **Step 1: Escribir el test que falla**
- [x] **Step 2: Ejecutar el test para confirmar que falla**
- [x] **Step 3: Crear la notificación**
- [x] **Step 4: Ejecutar el test para confirmar que pasa**
- [x] **Step 5: Commit**

---

## Task 3: Rutas

**Files:**
- Modify: `routes/web.php` (~línea 227)

- [x] **Step 1: Agregar las dos rutas al grupo `role:superadmin` existente**
- [x] **Step 2: Verificar que las rutas se registran**
- [x] **Step 3: Commit**

---

## Task 4: Métodos del Controlador

**Files:**
- Modify: `app/Http/Controllers/EmployeeController.php`
- Create: `tests/Feature/EmployeePromoteTest.php`

- [x] **Step 1: Escribir los tests que fallan**
- [x] **Step 2: Ejecutar los tests para confirmar que fallan**
- [x] **Step 3: Crear el factory de Employee si no existe**
- [x] **Step 4: Agregar imports y métodos al EmployeeController**
- [x] **Step 5: Ejecutar los tests para confirmar que pasan**
- [x] **Step 6: Commit**

---

## Task 5: Vista Parcial del Modal

**Files:**
- Create: `resources/views/employees/partials/promote-form.blade.php`

- [x] **Step 1: Crear el directorio y la vista parcial**
- [x] **Step 2: Verificar que la vista existe**
- [x] **Step 3: Commit**

---

## Task 6: Actualizar la Vista Principal

**Files:**
- Modify: `resources/views/employees/index.blade.php`

- [x] **Step 1: Agregar columna Acciones al thead**
- [x] **Step 2: Agregar la columna actions al método `datatable()` del controlador**
- [x] **Step 3: Agregar la columna `actions` al JS de DataTables**
- [x] **Step 4: Agregar el modal container y el JS de interacción**
- [x] **Step 5: Verificar con artisan**
- [x] **Step 6: Commit**

---

## Task 7: Ejecutar Todos los Tests y Commit Final

- [x] **Step 1: Ejecutar la suite completa de employees**
- [x] **Step 2: Verificación manual en el navegador**
- [x] **Step 3: Commit final**

---

## Task 8: Filtros, Búsqueda y Ordenamiento

**Goal:** Mejorar la UX permitiendo filtrar por empresa y estado, y buscar/ordenar por nombre completo.

**Files:**
- Modify: `app/Http/Controllers/EmployeeController.php`
- Modify: `resources/views/employees/index.blade.php`
- Modify: `tests/Feature/EmployeeCatalogTest.php`

- [x] **Step 1: Actualizar `index()` para pasar lista de empresas**
- [x] **Step 2: Implementar filtros `is_active` y `company` en `datatable()`**
- [x] **Step 3: Implementar `filterColumn` y `orderColumn` para `full_name`**
- [x] **Step 4: Agregar selectores de filtro en el HTML de la vista**
- [x] **Step 5: Actualizar JS de DataTables (searchable: true, orderable: true)**
- [x] **Step 6: Agregar listeners de JS para recargar tabla al cambiar filtros**
- [x] **Step 7: Agregar tests de integración para filtros, búsqueda y ordenamiento**
- [x] **Step 8: Ejecutar tests y commit**
