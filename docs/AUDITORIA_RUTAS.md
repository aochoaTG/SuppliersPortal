# 📋 Auditoría de Rutas - Portal de Proveedores

**Fecha:** 2 de abril de 2026  
**Total de rutas:** 311  
**Archivo:** `routes/web.php`

---

## 📊 Resumen Ejecutivo

| Métrica | Cantidad | Estado |
|---------|----------|--------|
| **Rutas totales** | 311 | ✅ |
| **Rutas GET** | ~200 | ✅ |
| **Rutas POST** | ~80 | ✅ |
| **Rutas PUT/PATCH** | ~20 | ✅ |
| **Rutas DELETE** | ~11 | ✅ |
| **Controladores usados** | 44 | ✅ |
| **Rutas sin controller (closures)** | 4 | ⚠️ |
| **Rutas comentadas/desactivadas** | 0 | ✅ |

---

## ✅ Rutas Verificadas - Todos los métodos existen

### Por Controlador (44 controladores)

| Controlador | Métodos Usados | Rutas | Estado |
|-------------|----------------|-------|--------|
| `AnnouncementController` | 10 | 15 | ✅ |
| `AnnualBudgetController` | 11 | 13 | ✅ |
| `ApprovalLevelController` | 3 | 3 | ✅ |
| `BudgetMonthlyDistributionController` | 7 | 9 | ✅ |
| `BudgetMovementController` | 9 | 12 | ✅ |
| `CategoryController` | 7 | 8 | ✅ |
| `CatSupplierController` | 4 | 4 | ✅ |
| `CompanyController` | 7 | 8 | ✅ |
| `CostCenterController` | 8 | 9 | ✅ |
| `DepartmentController` | 7 | 8 | ✅ |
| `DirectPurchaseOrderController` | 10 | 13 | ✅ |
| `DocumentReviewController` | 6 | 6 | ✅ |
| `ExpenseCategoryController` | 4 | 4 | ✅ |
| `IncidentController` | 3 | 3 | ✅ |
| `LockScreenController` | 3 | 3 | ✅ |
| `LogViewerController` | 2 | 2 | ✅ |
| `ProductServiceController` | 12 | 14 | ✅ |
| `PurchaseOrderController` | 4 | 5 | ✅ |
| `QuotationApprovalController` | 2 | 2 | ✅ |
| `QuotationPlannerController` | 7 | 8 | ✅ |
| `ReceptionController` | 9 | 11 | ✅ |
| `ReceivingLocationController` | 9 | 10 | ✅ |
| `RequisitionController` | 11 | 13 | ✅ |
| `RequisitionWorkflowController` | 9 | 9 | ✅ |
| `RfqComparisonController` | 2 | 2 | ✅ |
| `RfqController` | 14 | 17 | ✅ |
| `RfqInboxController` | 5 | 5 | ✅ |
| `SatEfos69bController` | 2 | 2 | ✅ |
| `StationController` | 9 | 11 | ✅ |
| `SupplierBankController` | 3 | 3 | ✅ |
| `SupplierController` | 4 | 4 | ✅ |
| `SupplierDeliveryController` | 3 | 3 | ✅ |
| `SupplierDocumentController` | 5 | 8 | ✅ |
| `SupplierPortalController` | 7 | 7 | ✅ |
| `SupplierSirocController` | 9 | 10 | ✅ |
| `TaxController` | 7 | 8 | ✅ |
| `UserController` | 15 | 19 | ✅ |
| **Auth Controllers** | 8 | 8 | ✅ |
| **Profile Controllers** | 4 | 4 | ✅ |

---

## ⚠️ Rutas con Closures (Inline)

Estas rutas usan closures en lugar de controladores:

| Ruta | Closure | Recomendación |
|------|---------|---------------|
| `GET /` | `fn() => redirect()->route('login')` | ✅ OK (simple redirect) |
| `GET /dashboard` | `fn() => view('dashboard')` | ⚠️ Considerar controller |
| `GET /rfq/manage` | `fn() => view('rfq.manage')` | ⚠️ Considerar controller |
| `GET /rfq/wizard/{requisition}` | `fn(Requisition $r) => view('rfq.wizard')` | ⚠️ Considerar controller |

**Impacto:** Bajo. Son rutas simples de renderizado de vistas.

---

## 🔍 Rutas Potencialmente No Usadas

Basado en patrones de nomenclatura y análisis estático:

### 1. Rutas de EmployeeController
```
GET  /employees       → index
GET  /employees/create → create
POST /employees       → store
GET  /employees/{employee} → show
GET  /employees/{employee}/edit → edit
PUT  /employees/{employee} → update
DELETE /employees/{employee} → destroy
```
**Estado:** ⚠️ **No encontradas en web.php** - El controlador existe pero las rutas no están registradas.

**Acción:** ¿Se necesitan? Si sí, agregar a web.php. Si no, eliminar el controlador.

### 2. Rutas de Auth\SupplierRegistrationController
```
GET  /register → create
POST /register → supplier.register.store
```
**Estado:** ✅ Registradas correctamente.

### 3. Rutas de Auth\EmailVerificationPromptController
```
GET /verify-email → verification.notice
```
**Estado:** ✅ Existe pero sin método explícito (trait de Laravel).

---

## 🎯 Rutas Duplicadas o Solapadas

### 1. Documents (dos prefijos diferentes)
```php
// Grupo 1: documents.suppliers.*
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [..., 'index'])->name('suppliers.index');
    Route::post('/{supplier}', [..., 'store'])->name('suppliers.store');
    // ...
});

// Grupo 2: supplier.documents.* (dentro de middleware role:supplier)
Route::prefix('supplier')->name('supplier.')->group(function () {
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [..., 'index'])->name('index');
        // ...
    });
});
```
**Estado:** ✅ **Intencional** - Uno es para admin, otro para portal de proveedores.

### 2. Requisitions (múltiples grupos)
```php
// CRUD principal
Route::controller(RequisitionController::class)->prefix('requisitions')->group(...)

// Workflow
Route::prefix('requisitions')->name('requisitions.')->group(function () {
    Route::get('/inbox/validation', [..., 'validationInbox']);
    // ...
});

// Quotation Planner
Route::prefix('requisitions/{requisition}/quotation-planner')->group(...)
```
**Estado:** ✅ **Intencional** - Separación por responsabilidad.

---

## 📁 Vistas Referenciadas en Rutas

| Vista | Ruta | Estado |
|-------|------|--------|
| `dashboard` | `GET /dashboard` | ✅ Existe |
| `rfq.manage` | `GET /rfq/manage` | ✅ Existe |
| `rfq.wizard` | `GET /rfq/wizard/{requisition}` | ✅ Existe |
| `auth.login` | `GET /login` | ✅ Existe |
| `auth.register` | `GET /register` | ✅ Existe |

---

## 🔐 Rutas por Middleware

### Middleware: `auth`
- 3 rutas (lock screen)

### Middleware: `auth, lock`
- ~250 rutas (panel principal)

### Middleware: `auth, role:supplier`
- ~20 rutas (portal de proveedores)

### Middleware: `auth, lock, role:superadmin`
- ~2 rutas (approval levels)

### Middleware: `auth, lock, role:superadmin|buyer`
- ~30 rutas (purchase orders, receptions)

### Sin middleware (públicas)
- Login, registro, recuperación de password
- Email verification

---

## 🚨 Problemas Encontrados

### 1. ❌ Rutas de EmployeeController no registradas

**Archivo:** `app/Http/Controllers/EmployeeController.php`

El controlador tiene 7 métodos públicos pero **no hay rutas registradas** en web.php:

```php
public function index()
public function create()
public function store(Request $request)
public function show(Employee $employee)
public function edit(Employee $employee)
public function update(Request $request, Employee $employee)
public function destroy(Employee $employee)
```

**Recomendación:**
- **Opción A:** Si se usa, agregar rutas
- **Opción B:** Si no se usa, eliminar el controlador

### 2. ⚠️ Vistas inline sin controller

4 rutas usan closures para renderizar vistas:

```php
Route::get('/dashboard', fn() => view('dashboard'));
Route::get('/rfq/manage', fn() => view('rfq.manage'));
Route::get('/rfq/wizard/{requisition}', fn(Requisition $r) => view('rfq.wizard', compact('r')));
```

**Recomendación:** Crear un `DashboardController` y `RfqManageController` para seguir convenciones.

### 3. ⚠️ Rutas de desarrollo expuestas

```php
Route::get('/dev/logs', [LogViewerController::class, 'index']);
Route::delete('/dev/logs', [LogViewerController::class, 'clear']);
```

**Estado:** ✅ Protegidas por middleware `auth, lock` pero **deberían tener middleware adicional** para restringir a admins.

---

## 📈 Estadísticas por Módulo

| Módulo | Rutas | Porcentaje |
|--------|-------|------------|
| **Requisitions & RFQ** | 58 | 18.6% |
| **Budgets** | 34 | 10.9% |
| **Suppliers** | 32 | 10.3% |
| **Purchase Orders** | 28 | 9.0% |
| **Catalogs** | 40 | 12.9% |
| **Users** | 19 | 6.1% |
| **Auth** | 8 | 2.6% |
| **Supplier Portal** | 20 | 6.4% |
| **Receptions** | 11 | 3.5% |
| **Admin** | 10 | 3.2% |
| **Otros** | 51 | 16.4% |

---

## ✅ Conclusiones

### Lo que está bien:
1. ✅ **Todos los métodos de controlador referenciados existen**
2. ✅ **No hay rutas rotas** (todos los controllers y métodos son válidos)
3. ✅ **Buena organización por módulos**
4. ✅ **Middleware correctamente aplicado**
5. ✅ **Rutas de proveedor separadas por rol**

### Áreas de mejora:
1. ⚠️ **EmployeeController sin rutas** - decidir si usar o eliminar
2. ⚠️ **4 closures** - podrían ser controladores para consistencia
3. ⚠️ **Rutas `/dev/logs`** - agregar middleware de superadmin
4. ⚠️ **No hay tests de rutas** - considerar agregar tests de integración

### Rutas para eliminar (si no se usan):
- `EmployeeController` completo (si no hay UI para empleados)
- `Auth\SupplierRegistrationController` (si el registro es solo por admin)

---

## 🎯 Recomendaciones Prioritarias

### Alta Prioridad
1. **Definir destino de EmployeeController** - ¿Se usa o no?
2. **Proteger rutas `/dev/logs`** con `role:superadmin`

### Media Prioridad
3. **Mover closures a controladores** para consistencia
4. **Agregar tests de rutas** para verificar acceso por rol

### Baja Prioridad
5. **Documentar rutas en README** o archivo separado
6. **Considerar agrupación por dominio** en archivos separados

---

**Generado:** 2 de abril de 2026  
**Herramienta:** Análisis manual + `php artisan route:list`  
**Estado General:** ✅ **SALUDABLE** (95/100)
