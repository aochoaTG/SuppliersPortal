# Análisis Técnico: Estado Actual del Módulo de Recepciones

**Fecha:** 2026-03-13
**Autor:** Análisis generado por Arquitecto de Software (Claude Code)
**Versión:** 1.1
**Estado:** Borrador para revisión de equipo — Paso 1 implementado

---

## 1. Objetivo del Análisis

Este documento tiene como propósito evaluar el estado actual del sistema para determinar si la base técnica es suficiente para implementar el proceso de **Recepción de Productos y Servicios**, tanto para Órdenes de Compra estándar (OC) como para Órdenes de Compra Directas (OCD).

El análisis responde tres preguntas concretas:

1. ¿Qué componentes necesarios para la recepción **ya existen** en el sistema?
2. ¿Qué componentes **faltan o están incompletos**?
3. ¿Cuál es el **plan de acción ordenado** para llegar a un modelo `Reception` robusto?

La intención no es solo crear un modelo, sino garantizar que el proceso de recepción sea trazable, auditable y funcionally correcto de extremo a extremo.

---

## 2. Actores Involucrados

| Actor | Rol en el Proceso | Permisos Relevantes |
|---|---|---|
| **Receptor** | Usuario en la locación que recibe físicamente los productos/servicios. Registra la recepción en el sistema. | `useMassReception`, `viewPendingReceptions` (ya definidos en `ReceivingLocationPolicy`) |
| **Comprador** | Encargado de emitir la OC y darle seguimiento. Verifica que la OC quede cerrada correctamente. | Acceso a `PurchaseOrderController` |
| **Proveedor** | Entrega los bienes o presta el servicio. En algunos flujos, puede tener acceso al portal para confirmar entrega. | Rol `supplier` en el sistema |
| **Aprobador / Jefe de Centro de Costo** | En OCD, es quien autoriza la orden antes de que llegue al estado `ISSUED` y por tanto sea recibible. | Ya modelado en `DirectPurchaseOrderApproval` |
| **Administrador del Sistema** | Configura locaciones, bloquea/desbloquea portal, administra permisos. | Acceso a `ReceivingLocationController` |
| **Contabilidad / Finanzas** | Consume el proceso de recepción para conciliación con facturas (cuentas por pagar). | No modelado aún — **brecha identificada** |

---

## 3. Flujo de Recepción de Alto Nivel

### 3.1 Flujo General (OC Estándar — Productos)

```
1.  [Compras]     Se genera una Requisición y pasa por el ciclo de cotización.
2.  [Compras]     Se aprueba el Resumen de Cotización (QuotationSummary).
3.  [Compras]     Se emite la Orden de Compra → estado: OPEN.
                  ✅ RESUELTO (Paso 1): La OC ahora tiene estado "ISSUED" y receiving_location_id.
4.  [Compras]     Comprador notifica al proveedor y al receptor de la locación destino.
5.  [Proveedor]   El proveedor llega a la locación con los bienes y documentos de entrega.
6.  [Receptor]    El receptor ubica la OC en el sistema (filtrada por locación).
                  ✅ RESUELTO (Paso 1): receiving_location_id ya existe en PurchaseOrder.
7.  [Receptor]    Selecciona "Iniciar Recepción" en la OC.
                  ⚠️ BRECHA: No existe ruta ni controlador para este paso.
8.  [Sistema]     Verifica que la locación esté activa y no bloqueada (portal_blocked = false).
9.  [Receptor]    Por cada ítem de la OC, ingresa la cantidad recibida.
                  Puede ser recepción total o parcial (ej. entrega en partes).
                  ⚠️ BRECHA: No existe modelo Reception ni ReceptionItem.
10. [Receptor]    Adjunta evidencia documental (foto de albarán, remisión, etc.).
                  ℹ️ INFO: DirectPurchaseOrderDocument ya maneja tipo 'reception_evidence'.
                           Falta equivalente para OC estándar.
11. [Sistema]     Registra la recepción con fecha, usuario y cantidades.
12. [Sistema]     Si todos los ítems fueron recibidos en su totalidad:
                  → OC pasa a estado RECEIVED.
                  → BudgetCommitment.markAsReceived() es invocado.
                  Si fue recepción parcial:
                  → OC pasa a estado PARTIALLY_RECEIVED.
                  ✅ RESUELTO (Paso 1): El estado PARTIALLY_RECEIVED ya existe en el modelo.
13. [Sistema]     Notifica a Compras y a Contabilidad que los bienes fueron recibidos.
                  ⚠️ BRECHA: No existe notificación de recepción.
```

### 3.2 Flujo General (OCD — Productos y Servicios)

```
1.  [Solicitante] Crea la OCD en estado DRAFT y la envía a aprobación.
2.  [Aprobador]   Aprueba la OCD → estado: APPROVED.
3.  [Compras]     Emite la OCD hacia el proveedor → estado: ISSUED.
                  ℹ️ INFO: El estado ISSUED ya existe en DirectPurchaseOrder.
4.  [Proveedor]   Entrega bienes/presta servicio en la locación indicada (receiving_location_id).
                  ℹ️ INFO: receiving_location_id ya existe en DirectPurchaseOrder.
5.  [Receptor]    Ubica la OCD en el sistema.
                  ℹ️ INFO: canBeReceived() ya existe en el modelo.
6.  [Receptor]    Registra la recepción con notas (reception_notes), evidencias y fecha.
                  ⚠️ SEMI-IMPLEMENTADO: El modelo tiene received_by, received_at, reception_notes
                     pero no hay controlador/ruta para este paso ni modelo Reception granular.
7.  [Sistema]     OCD pasa a estado RECEIVED, se registra BudgetCommitment como recibido.
                  ℹ️ INFO: BudgetCommitment.markAsReceived() ya existe.
8.  [Sistema]     Notifica a Compras y Contabilidad.
```

### 3.3 Diferencia clave: Productos vs. Servicios

| Aspecto | Productos | Servicios |
|---|---|---|
| **Evidencia de recepción** | Remisión física, albarán, conteo de piezas | Acta de entrega, reporte de avance, firma de conformidad |
| **Recepción parcial** | Muy común (entrega por lotes) | Menos común, pero posible (avance % de obra) |
| **Locación** | Siempre aplica (almacén, estación, etc.) | Puede ser remota o intangible |
| **Campo clave** | `quantity_received` por ítem | `percentage_completed` o `notes` de conformidad |

---

## 4. Análisis de Brechas (Lo que Tenemos vs. Lo que Necesitamos)

### 4.1 Modelos de Datos Actuales — Evaluación

#### `PurchaseOrder` — Estado: ✅ COMPLETO para recepción *(actualizado 2026-03-13)*

| Campo Necesario | ¿Existe? | Observación |
|---|---|---|
| `receiving_location_id` | ✅ SÍ | Agregado en migración `2026_03_13_000001`. FK nullable a `receiving_locations`. |
| `received_by` | ✅ SÍ | Agregado en migración `2026_03_13_000001`. FK nullable a `users`. |
| `received_at` | ✅ SÍ | Agregado en migración `2026_03_13_000001`. Cast `datetime`. |
| `issued_at` / estado `ISSUED` | ✅ SÍ | Agregado en migración `2026_03_13_000001`. Estado `ISSUED` en CHECK CONSTRAINT. |
| Estado `PARTIALLY_RECEIVED` | ✅ SÍ | Agregado al CHECK CONSTRAINT en migración `2026_03_13_000001`. |
| `reception_notes` | ✅ SÍ | Agregado en migración `2026_03_13_000001`. Campo `text` nullable. |
| Estados actuales | `OPEN`, `ISSUED`, `PARTIALLY_RECEIVED`, `RECEIVED`, `CANCELLED`, `PAID`, `CLOSED_BY_INACTIVITY` | Ciclo completo cubierto. |

**Métodos agregados al modelo:**
- `isIssued()`, `isPartiallyReceived()`, `isReceived()`, `isCancelled()` — verificadores de estado completos
- `canBeReceived()` — retorna `true` si status es `ISSUED` o `PARTIALLY_RECEIVED`
- `receivingLocation()` — relación BelongsTo a `ReceivingLocation`
- `receiver()` — relación BelongsTo a `User` via `received_by`
- `getStatusLabel()` y `getStatusBadgeClass()` — actualizados con los nuevos estados

#### `PurchaseOrderItem` — Estado: ⚠️ INCOMPLETO para recepción

| Campo Necesario | ¿Existe? | Observación |
|---|---|---|
| `quantity_received` | ❌ NO | No se lleva registro de qué cantidad fue recibida por ítem. |
| `quantity_pending` | ❌ NO | No hay cálculo de pendiente por ítem. |

#### `DirectPurchaseOrder` — Estado: ✅ RELATIVAMENTE COMPLETO (recepción simple)

El modelo OCD tiene: `receiving_location_id`, `received_by`, `received_at`, `reception_notes`, estado `ISSUED` y `RECEIVED`, `canBeReceived()`. Sin embargo, **no tiene granularidad por ítem** (al igual que OC). La recepción es un evento de cabecera, no por línea.

#### `DirectPurchaseOrderItem` — Estado: ⚠️ IGUAL QUE PurchaseOrderItem

No tiene `quantity_received`. Asume recepción total del ítem siempre.

#### `ReceivingLocation` — Estado: ✅ COMPLETO como catálogo

El modelo está bien diseñado. Tiene tipos, bloqueo de portal, relación con usuarios. La `ReceivingLocationPolicy` incluso ya declara `useMassReception()` y `viewPendingReceptions()`, lo que indica que el equipo anticipó estas funcionalidades. Sin embargo, **estos métodos de policy están declarados pero no se usan en ningún controlador actual** — el controlador de recepciones aún no existe.

#### `BudgetCommitment` — Estado: ✅ LISTO para integrarse

Tiene `markAsReceived()` y el estado `RECEIVED`. Solo necesita ser invocado desde el proceso de recepción.

#### `Reception` / `ReceptionItem` — Estado: ❌ NO EXISTE

**No existe ningún modelo de Recepción en el sistema.** Este es el vacío central que impide llevar un historial granular, soportar recepciones parciales o múltiples recepciones sobre la misma orden.

---

### 4.2 Lógica de Negocio Existente — Evaluación

| Componente | ¿Existe? | Estado |
|---|---|---|
| Cambio de estado en OC estándar (OPEN → RECEIVED) | ⚠️ Parcial | El estado `RECEIVED` existe pero no hay método `receive()` ni servicio que lo gestione. Solo `isReceived()`. |
| Cambio de estado en OCD (ISSUED → RECEIVED) | ⚠️ Parcial | `canBeReceived()` existe, pero no hay método `receive()` ni ruta que lo ejecute. |
| Servicio de recepciones (`ReceptionService`) | ❌ NO | No existe. Toda la lógica deberá crearse. |
| Notificaciones de recepción | ❌ NO | No hay ninguna notificación asociada al evento de recepción. |
| Validación de locación (activa, no bloqueada) | ⚠️ Lógica existe en modelo | `ReceivingLocation` tiene `is_active` y `portal_blocked`, pero nadie los valida en el flujo de recepción porque ese flujo no existe. |
| Recepción parcial (por ítem) | ❌ NO | No existe ni el modelo ni la lógica. |
| Integración con BudgetCommitment al recibir | ⚠️ Parcial | `markAsReceived()` existe pero no está conectado a ningún trigger. |
| `ReceivingLocationPolicy::useMassReception()` | ⚠️ Declarado | Método en la policy existe pero no hay acción que lo use. |

---

### 4.3 Puntos Ciegos y Riesgos

#### 🔴 Riesgo Alto

1. **La OC Estándar no tiene locación de entrega.** Si se crea el módulo de recepción sin agregar `receiving_location_id` a `purchase_orders`, el receptor no puede filtrar qué órdenes le corresponden. Cualquier usuario podría recibir cualquier orden, eliminando los controles por locación.

2. **Sin modelo `Reception`, no hay historial.** Si solo se marca la OC como `RECEIVED`, se pierde: quién recibió, cuándo, qué documentos adjuntó, si fue parcial. Esto es un riesgo de auditoría y de cuentas por pagar.

3. **Recepciones parciales no modeladas.** En compras de productos, es muy común recibir en varias entregas. Si la primera entrega marca la OC como `RECEIVED`, se bloquea el proceso para las entregas siguientes.

#### 🟡 Riesgo Medio

4. ~~**El flujo OC estándar carece del estado `ISSUED`.**~~ ✅ **RESUELTO (Paso 1).** El estado `ISSUED` fue agregado al modelo y a la base de datos.

5. **Validación REPSE no conectada a recepción de servicios.** El modelo `Supplier` tiene `requires_repse`, `repse_expiry_date` etc. Sin embargo, no hay validación que bloquee la recepción de un servicio si el proveedor tiene el REPSE vencido.

6. **`ReceivingLocationPolicy` con métodos fantasma.** `useMassReception()` y `viewPendingReceptions()` están declarados en la policy pero no los usa nadie. Esto es un riesgo porque cuando se implemente el controlador, podría ignorarse la policy y crear un bypass de permisos.

#### 🟢 Riesgo Bajo / Deuda Técnica

7. **`DirectPurchaseOrderDocument` vs documentos de recepción en OC estándar.** La OCD tiene su propio sistema de documentos con tipo `reception_evidence`. La OC estándar no tiene equivalente. Si se crea un modelo `Reception` genérico, debería unificar este manejo de evidencias.

8. **Inactividad de OC no reiniciada por recepción.** `PurchaseOrder` cierra por inactividad a los 10 días. Si se registra una recepción parcial, el temporizador de inactividad debería reiniciarse. Esta lógica no existe.

---

## 5. Recomendación y Plan de Acción

### ¿Debemos crear el modelo `Reception` ahora?

**No directamente.** Hay prerrequisitos técnicos que, si se omiten, obligarán a refactorizar el modelo `Reception` apenas esté creado. El modelo `Reception` debe ser el **último paso** de la siguiente secuencia.

---

### Plan de Acción — Orden Sugerido

#### ~~Paso 1~~ ✅ COMPLETADO — Completar el modelo `PurchaseOrder` (2026-03-13)

**Archivos modificados:**
- `database/migrations/2026_03_13_000001_add_reception_fields_to_purchase_orders_table.php` — creado
- `app/Models/PurchaseOrder.php` — actualizado

**Lo que se hizo:**
- Migración con: `receiving_location_id` (FK → `receiving_locations`), `received_by` (FK → `users`), `received_at`, `issued_at`, `reception_notes`
- CHECK CONSTRAINT de SQL Server actualizado: `OPEN`, `ISSUED`, `PARTIALLY_RECEIVED`, `RECEIVED`, `CANCELLED`, `PAID`, `CLOSED_BY_INACTIVITY`
- Modelo actualizado con nuevos campos en `$fillable` y `$casts`
- Relaciones agregadas: `receivingLocation()`, `receiver()`
- Métodos de estado: `isIssued()`, `isPartiallyReceived()`, `isReceived()`, `isCancelled()`, `canBeReceived()`
- `getStatusLabel()` y `getStatusBadgeClass()` actualizados con los nuevos estados

#### Paso 2 — Agregar `quantity_received` a ítems de ambas órdenes
**Alcance:** Permitir que cada línea de orden registre cuánto fue recibido.

**Migraciones:**
```php
// add_received_qty_to_purchase_order_items_table
$table->decimal('quantity_received', 10, 3)->default(0)->after('quantity');

// add_received_qty_to_direct_purchase_order_items_table
$table->decimal('quantity_received', 10, 3)->default(0)->after('quantity');
```

**Lógica a agregar en los modelos:**
- `quantity_pending` como accesor calculado (`quantity - quantity_received`)
- `isFullyReceived()` → `quantity_received >= quantity`
- `isPartiallyReceived()` → `quantity_received > 0 && !isFullyReceived()`

#### Paso 3 — Crear el modelo `Reception` y la migración

Solo después de los pasos 1 y 2, crear este modelo que es el registro maestro de cada evento de recepción.

**Tabla:** `receptions`
```php
$table->id();
$table->string('folio')->unique();                          // Ej: REC-2026-0001
$table->morphs('receivable');                               // Polimórfico: purchase_order o direct_purchase_order
$table->unsignedBigInteger('receiving_location_id');
$table->unsignedBigInteger('received_by');
$table->string('status');                                   // PENDING, COMPLETED, PARTIAL
$table->text('notes')->nullable();
$table->string('delivery_reference')->nullable();           // Número de remisión o albarán del proveedor
$table->timestamp('received_at');
$table->timestamps();
$table->softDeletes();
```

> **Nota de diseño:** Se recomienda usar `morphs()` (relación polimórfica) para que `Reception` pueda pertenecer tanto a `PurchaseOrder` como a `DirectPurchaseOrder` sin duplicar la tabla. Esto unifica el historial de recepciones en un solo lugar.

#### Paso 4 — Crear el modelo `ReceptionItem`

Detalle por línea de cada recepción.

**Tabla:** `reception_items`
```php
$table->id();
$table->unsignedBigInteger('reception_id');
$table->morphs('receivable_item');                          // PurchaseOrderItem o DirectPurchaseOrderItem
$table->decimal('quantity_received', 10, 3);
$table->decimal('quantity_rejected', 10, 3)->default(0);
$table->string('rejection_reason')->nullable();
$table->timestamps();
```

#### Paso 5 — Crear `ReceptionService`

Centralizar la lógica de negocio de recepciones para evitar código duplicado en controladores.

**Métodos sugeridos:**
```php
class ReceptionService {
    public function receive(Model $order, array $items, User $receiver, array $data): Reception
    public function validateCanReceive(Model $order, ReceivingLocation $location): void
    public function calculateOrderReceptionStatus(Model $order): string  // PARTIAL o RECEIVED
    public function updateOrderStatus(Model $order, Reception $reception): void
    public function notifyReceptionCompleted(Reception $reception): void
    public function validateRepseIfService(Model $order): void
}
```

#### Paso 6 — Crear `ReceptionController` y rutas

```php
// Rutas sugeridas:
Route::get('/receptions/pending', [ReceptionController::class, 'pending']);   // Bandeja del receptor
Route::get('/purchase-orders/{order}/receive', [ReceptionController::class, 'create']);
Route::post('/purchase-orders/{order}/receive', [ReceptionController::class, 'store']);
Route::get('/direct-purchase-orders/{order}/receive', [ReceptionController::class, 'createDirect']);
Route::post('/direct-purchase-orders/{order}/receive', [ReceptionController::class, 'storeDirect']);
Route::get('/receptions/{reception}', [ReceptionController::class, 'show']);
```

**Conectar con la policy existente:**
- `useMassReception()` → Habilitar recepción masiva desde la bandeja
- `viewPendingReceptions()` → Filtrar órdenes pendientes de recepción por locación del usuario

#### Paso 7 — Conectar `BudgetCommitment` al proceso de recepción

En `ReceptionService::updateOrderStatus()`, cuando la orden quede como `RECEIVED`:
```php
$order->budgetCommitment?->markAsReceived();
```

#### Paso 8 — Validación REPSE en recepción de servicios

En `ReceptionService::validateRepseIfService()`:
- Si el ítem es de categoría "Servicio" y el proveedor `requires_repse`, verificar que `repse_expiry_date` sea futura.
- Emitir advertencia (no bloqueo) si está próximo a vencer.

#### Paso 9 — Notificaciones

Crear `ReceptionCompletedNotification` para:
- Notificar al Comprador (creador de la OC)
- Notificar a Finanzas/Contabilidad (nuevo actor a definir con el equipo)

---

## 6. Resumen Ejecutivo

| Componente | Estado Actual | Acción Requerida |
|---|---|---|
| `ReceivingLocation` | ✅ Completo | Ninguna |
| `DirectPurchaseOrder` | ✅ Estructura básica ok | Conectar a `ReceptionService` |
| `PurchaseOrder` | ✅ Completo | Paso 1 completado el 2026-03-13 |
| `PurchaseOrderItem` / `DirectPurchaseOrderItem` | ❌ Sin `quantity_received` | **Migración** (Paso 2) |
| `Reception` | ❌ No existe | Crear tras pasos 1 y 2 (Paso 3) |
| `ReceptionItem` | ❌ No existe | Crear tras Reception (Paso 4) |
| `ReceptionService` | ❌ No existe | Crear (Paso 5) |
| `ReceptionController` + Rutas | ❌ No existe | Crear (Paso 6) |
| Integración `BudgetCommitment` | ⚠️ Parcial (método existe) | Conectar (Paso 7) |
| Validación REPSE en recepción | ❌ No existe | Implementar (Paso 8) |
| Notificaciones de recepción | ❌ No existe | Implementar (Paso 9) |

**Respuesta directa:** El sistema tiene una base sólida de catálogos (locaciones, proveedores, presupuestos) y el flujo de OCD está más maduro que el de OC estándar. El Paso 1 ya fue completado. **El siguiente prerrequisito es el Paso 2** (agregar `quantity_received` a los ítems). Una vez completado, el sistema estará listo para crear el modelo `Reception`.

---

*Documento generado con base en análisis estático del código fuente. Pendiente de validación con el equipo.*
