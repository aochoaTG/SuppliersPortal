# Análisis Técnico: Estado Actual del Módulo de Recepciones

**Fecha:** 2026-03-13
**Autor:** Análisis generado por Arquitecto de Software (Claude Code)
**Versión:** 1.8
**Estado:** Borrador para revisión de equipo — Pasos 1 a 8 implementados

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

#### `PurchaseOrderItem` — Estado: ✅ COMPLETO para recepción *(actualizado 2026-03-13)*

| Campo Necesario | ¿Existe? | Observación |
|---|---|---|
| `quantity_received` | ✅ SÍ | Agregado en migración `2026_03_13_000002`. `decimal(10,3)` con default 0. |
| `quantity_pending` | ✅ SÍ | Accesor `getQuantityPendingAttribute()`. Calculado: `max(0, quantity - quantity_received)`. |

**Métodos agregados:** `isFullyReceived()`, `isPartiallyReceived()`, `getQuantityPendingAttribute()`
**Casts agregados:** `quantity`, `quantity_received` (`decimal:3`), más todos los monetarios (`decimal:2`).

#### `DirectPurchaseOrder` — Estado: ✅ RELATIVAMENTE COMPLETO (recepción simple)

El modelo OCD tiene: `receiving_location_id`, `received_by`, `received_at`, `reception_notes`, estado `ISSUED` y `RECEIVED`, `canBeReceived()`. Sin embargo, **no tiene granularidad por ítem** (al igual que OC). La recepción es un evento de cabecera, no por línea.

#### `DirectPurchaseOrderItem` — Estado: ✅ COMPLETO para recepción *(actualizado 2026-03-13)*

Mismos cambios que `PurchaseOrderItem`. `quantity_received` agregado en la misma migración `2026_03_13_000002`.
`$casts` de `quantity` actualizado de `decimal:2` a `decimal:3` para consistencia con ambos modelos.

**Métodos agregados:** `isFullyReceived()`, `isPartiallyReceived()`, `getQuantityPendingAttribute()`

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

#### ~~Paso 2~~ ✅ COMPLETADO — Agregar `quantity_received` a ítems (2026-03-13)

**Archivos modificados:**
- `database/migrations/2026_03_13_000002_add_quantity_received_to_order_items_tables.php` — creado (maneja ambas tablas)
- `app/Models/PurchaseOrderItem.php` — actualizado
- `app/Models/DirectPurchaseOrderItem.php` — actualizado

**Lo que se hizo:**
- `quantity_received decimal(10,3) DEFAULT 0` en `purchase_order_items` y `odc_direct_purchase_order_items`
- Accesor `getQuantityPendingAttribute()` → `max(0, quantity - quantity_received)`
- `isFullyReceived()` → `quantity_received >= quantity`
- `isPartiallyReceived()` → `quantity_received > 0 && !isFullyReceived()`
- `$casts` completados en ambos modelos (`quantity` y `quantity_received` como `decimal:3`)

#### ~~Paso 3~~ ✅ COMPLETADO — Crear el modelo `Reception` y la migración (2026-03-13)

**Archivos creados/modificados:**
- `database/migrations/2026_03_13_000003_create_receptions_table.php` — creado
- `app/Models/Reception.php` — creado
- `app/Models/PurchaseOrder.php` — relación `receptions()` agregada
- `app/Models/DirectPurchaseOrder.php` — relación `receptions()` agregada
- `app/Models/ReceivingLocation.php` — relación `receptions()` agregada (corrige bug: `recepciones()` llamaba a un método inexistente)

**Tabla `receptions` — campos implementados:**

| Campo | Tipo | Notas |
|---|---|---|
| `folio` | `string(50) UNIQUE` | Formato `REC-YYYY-NNNN` |
| `receivable_type` / `receivable_id` | `morphs()` | Polimórfico: `PurchaseOrder` o `DirectPurchaseOrder` |
| `receiving_location_id` | FK → `receiving_locations` | Con `noActionOnDelete` |
| `received_by` | FK → `users` | Con `noActionOnDelete` |
| `status` | `enum` | `PENDING`, `PARTIAL`, `COMPLETED` |
| `delivery_reference` | `string(100) nullable` | Número de remisión o albarán del proveedor |
| `notes` | `text nullable` | Observaciones del receptor |
| `received_at` | `timestamp` | Momento físico de recepción |
| Índices | — | `folio`, `status`, `received_by`, `receiving_location_id`, `received_at` |

**Modelo `Reception` — características:**
- Constantes: `STATUS_PENDING`, `STATUS_PARTIAL`, `STATUS_COMPLETED`
- Relaciones: `receivable()` (MorphTo), `receivingLocation()`, `receiver()`, `items()` (→ ReceptionItem, Paso 4)
- `generateNextFolio()` — genera `REC-YYYY-NNNN` automáticamente
- `isPending()`, `isPartial()`, `isCompleted()`
- `getStatusLabel()`, `getStatusBadgeClass()`
- Scopes: `completed()`, `forLocation()`, `receivedBy()`

**Relaciones polimórficas en modelos de órdenes:**
```php
// Uso desde cualquier orden:
$purchaseOrder->receptions;           // Todas las recepciones de esta OC
$directPurchaseOrder->receptions;     // Todas las recepciones de esta OCD
$receivingLocation->receptions;       // Recepciones hechas en esta locación
```

#### ~~Paso 4~~ ✅ COMPLETADO — Crear el modelo `ReceptionItem` (2026-03-13)

**Archivos creados/modificados:**
- `database/migrations/2026_03_13_000004_create_reception_items_table.php` — creado
- `app/Models/ReceptionItem.php` — creado
- `app/Models/PurchaseOrderItem.php` — relación `receptionItems()` agregada
- `app/Models/DirectPurchaseOrderItem.php` — relación `receptionItems()` agregada

**Tabla `reception_items` — campos implementados:**

| Campo | Tipo | Notas |
|---|---|---|
| `reception_id` | FK → `receptions` | `cascadeOnDelete` — si se borra la recepción, se borran sus líneas |
| `receivable_item_type` / `receivable_item_id` | `morphs()` | Polimórfico: `PurchaseOrderItem` o `DirectPurchaseOrderItem` |
| `quantity_received` | `decimal(10,3)` | Cantidad recibida en este evento |
| `quantity_rejected` | `decimal(10,3) DEFAULT 0` | Unidades rechazadas (dañadas, incorrectas) |
| `rejection_reason` | `string(255) nullable` | Motivo del rechazo |

**Modelo `ReceptionItem` — características:**
- `getQuantityAcceptedAttribute()` → `quantity_received - quantity_rejected`
- `hasRejections()` → `quantity_rejected > 0`
- `isFullyRejected()` → `quantity_rejected >= quantity_received`
- Relaciones: `reception()` (BelongsTo), `receivableItem()` (MorphTo)

**Relaciones en modelos de ítems:**
```php
// Historial de recepciones parciales/totales de un ítem específico:
$purchaseOrderItem->receptionItems;        // todas las líneas de recepción de este ítem
$directPurchaseOrderItem->receptionItems;  // ídem para OCD
```

#### ~~Paso 5~~ ✅ COMPLETADO — Crear `ReceptionService` (2026-03-13)

**Archivos creados/modificados:**
- `app/Services/ReceptionService.php` — creado
- `app/Models/PurchaseOrder.php` — relación `budgetCommitment()` agregada (necesaria para el servicio)

**Métodos implementados:**

| Método | Visibilidad | Propósito |
|---|---|---|
| `receive($order, $itemsData, $receiver, $data)` | `public` | Orquesta toda la recepción dentro de `DB::transaction`. Punto de entrada único para el controlador. |
| `validateCanReceive($order)` | `public` | Verifica estado de la orden, locación activa y portal no bloqueado. Lanza `RuntimeException` si falla. |
| `validateRepseIfService($order)` | `public` | Verifica REPSE del proveedor. **Devuelve `?string`** (advertencia), no lanza excepción. El controlador decide cómo mostrarlo. |
| `calculateOrderReceptionStatus($order)` | `public` | Recarga ítems desde BD y determina si la orden quedó `RECEIVED` o `PARTIALLY_RECEIVED`. |
| `updateOrderStatus($order, $status, $receiver)` | `public` | Persiste el nuevo estado y los timestamps de auditoría (`received_by`, `received_at`). |
| `resolveItemClass($order)` | `private` | Mapea el tipo de orden al modelo de ítem correcto (`PurchaseOrderItem` o `DirectPurchaseOrderItem`). |
| `markBudgetAsReceived($order)` | `private` | Llama a `BudgetCommitment::markAsReceived()` si la orden tiene un compromiso asociado. |

**Flujo de `receive()` en detalle:**
```
1. validateCanReceive()     → lanza si hay impedimento
2. DB::transaction {
   3. Crear Reception (PENDING)
   4. Por cada ítem:
      a. Crear ReceptionItem
      b. $item->increment('quantity_received', $accepted)
         ↑ usa increment() directo para NO disparar eventos 'saved' de DirectPurchaseOrderItem
   5. calculateOrderReceptionStatus() → recarga ítems frescos de BD
   6. Reception.status = COMPLETED | PARTIAL
   7. updateOrderStatus() → orden queda RECEIVED | PARTIALLY_RECEIVED
   8. Si RECEIVED: markBudgetAsReceived()
}
9. Retorna Reception con items cargados
```

**Decisión de diseño — REPSE:**
`validateRepseIfService()` devuelve `?string` en lugar de lanzar excepción, porque un REPSE vencido es un **riesgo legal que debe visibilizarse** pero el negocio puede decidir aceptarlo. El controlador (Paso 6) mostrará el mensaje como alerta roja antes del formulario.

#### ~~Paso 6~~ ✅ COMPLETADO — Crear `ReceptionController`, rutas y vistas (2026-03-13)

**Archivos creados/modificados:**
- `app/Http/Controllers/ReceptionController.php` — creado
- `routes/web.php` — 6 rutas nuevas agregadas
- `resources/views/receptions/pending.blade.php` — creado
- `resources/views/receptions/create.blade.php` — creado (vista compartida OC y OCD)
- `resources/views/receptions/show.blade.php` — creado
- `app/Http/Controllers/PurchaseOrderController.php` — botón "Recibir" en DataTable y estados actualizados

**Rutas implementadas:**

```php
Route::get('/receptions/pending', [ReceptionController::class, 'pending'])
    ->name('receptions.pending');
Route::get('/purchase-orders/{purchaseOrder}/receive', [ReceptionController::class, 'create'])
    ->name('receptions.create');
Route::post('/purchase-orders/{purchaseOrder}/receive', [ReceptionController::class, 'store'])
    ->name('receptions.store');
Route::get('/direct-purchase-orders/{directPurchaseOrder}/receive', [ReceptionController::class, 'createDirect'])
    ->name('receptions.create-direct');
Route::post('/direct-purchase-orders/{directPurchaseOrder}/receive', [ReceptionController::class, 'storeDirect'])
    ->name('receptions.store-direct');
Route::get('/receptions/{reception}', [ReceptionController::class, 'show'])
    ->name('receptions.show');
```

> **Nota de orden de rutas:** `receptions/pending` se define **antes** de `receptions/{reception}` para que Laravel no interprete la cadena literal `"pending"` como un ID de recepción.

**Acciones del controlador:**

| Método | Ruta | Propósito |
|---|---|---|
| `pending()` | GET `/receptions/pending` | Bandeja con OC y OCD pendientes de recepción. Filtra por locación si el usuario es receptor. |
| `create(PurchaseOrder)` | GET `/purchase-orders/{id}/receive` | Formulario de recepción para OC estándar. Autoriza con `useMassReception` policy. |
| `store(Request, PurchaseOrder)` | POST `/purchase-orders/{id}/receive` | Persiste recepción de OC estándar. Delega a `ReceptionService::receive()`. |
| `createDirect(DirectPurchaseOrder)` | GET `/direct-purchase-orders/{id}/receive` | Formulario de recepción para OCD. |
| `storeDirect(Request, DirectPurchaseOrder)` | POST `/direct-purchase-orders/{id}/receive` | Persiste recepción de OCD. |
| `show(Reception)` | GET `/receptions/{id}` | Vista de solo lectura del comprobante de recepción. |

**Vistas implementadas:**

- **`pending.blade.php`**: Dos tabs (OC Estándar / OC Directas). Muestra folio, proveedor, punto de entrega, total, estado y botón "Recibir". Contador total en la cabecera.
- **`create.blade.php`**: Vista compartida para OC y OCD. Incluye:
  - Alerta de advertencia REPSE (si aplica)
  - Tabla de ítems con columnas: Ordenado, Recibido Prev., Pendiente, A Recibir, Rechazado, Motivo Rechazo
  - El campo "Motivo Rechazo" se muestra/oculta con JavaScript al ingresar cantidad rechazada
  - Panel lateral con fecha de recepción, referencia del proveedor (remisión/albarán) y notas
  - Validación JS al enviar: rechazado no puede superar recibido
- **`show.blade.php`**: Comprobante de recepción. Muestra folio, receptor, locación, orden de origen, tipo de orden, proveedor, y tabla de ítems con cantidades recibidas/rechazadas/aceptadas. Botón de impresión.

**Policy conectada:**
- `ReceivingLocationPolicy::useMassReception()` → usado en `create()` y `createDirect()` con `$this->authorize()`
- `ReceivingLocationPolicy::viewPendingReceptions()` → usado en `pending()` para filtrar órdenes por locación del receptor

**Cambios en `PurchaseOrderController`:**
- `datatableRegular()`: columna `status` ahora usa `getStatusBadgeClass()` / `getStatusLabel()` del modelo (ya manejan los nuevos estados `ISSUED`, `PARTIALLY_RECEIVED`)
- Botón "Recibir" (ícono `ti-package-import`) aparece en el DataTable cuando `$po->canBeReceived()` es verdadero

#### ~~Paso 7~~ ✅ COMPLETADO — Conectar `BudgetCommitment` y completar soporte OCD (2026-03-13)

**Archivos creados/modificados:**
- `database/migrations/2026_03_13_000005_add_received_at_to_budget_commitments_table.php` — creado
- `database/migrations/2026_03_13_000006_add_partially_received_to_ocd_status.php` — creado
- `app/Models/BudgetCommitment.php` — actualizado
- `app/Models/DirectPurchaseOrder.php` — actualizado
- `app/Http/Controllers/PurchaseOrderController.php` — DataTable OCD actualizado

**Brecha 1 resuelta — `received_at` en `BudgetCommitment`:**

La tabla `budget_commitments` tenía `committed_at` y `released_at` pero no `received_at`. Se agrega para trazabilidad completa del ciclo de vida del compromiso presupuestal.

- Migración `000005`: agrega `received_at timestamp nullable` a `budget_commitments`
- `BudgetCommitment::markAsReceived()` ahora registra `received_at = now()` además de cambiar el status:
```php
$this->update(['status' => 'RECEIVED', 'received_at' => now()]);
```
- `$fillable` y `$casts` actualizados

**Brecha 2 resuelta — `PARTIALLY_RECEIVED` faltaba en OCD:**

`ReceptionService::calculateOrderReceptionStatus()` puede retornar `'PARTIALLY_RECEIVED'` para cualquier tipo de orden. Sin este fix, `updateOrderStatus()` intentaría escribir ese valor en una OCD y fallaría por el CHECK CONSTRAINT de SQL Server (valor no permitido).

- Migración `000006`: descubre dinámicamente el CHECK CONSTRAINT existente en `odc_direct_purchase_orders`, lo elimina y lo recrea incluyendo `PARTIALLY_RECEIVED` y `CLOSED_BY_INACTIVITY`
- `DirectPurchaseOrder::canBeReceived()` ahora acepta `ISSUED` y `PARTIALLY_RECEIVED`
- `DirectPurchaseOrder::isPartiallyReceived()` — nuevo método verificador
- `getStatusLabel()` y `getStatusBadgeClass()` actualizados con el nuevo estado

**Mejora adicional — DataTable OCD:**

`PurchaseOrderController::datatableDirect()` tenía los status hardcodeados como arrays locales (sin `PARTIALLY_RECEIVED` ni `CLOSED_BY_INACTIVITY`). Se actualizó para usar `$ocd->getStatusBadgeClass()` / `$ocd->getStatusLabel()` del modelo (igual que se hizo con el DataTable regular en el Paso 6). También se agregó el botón "Recibir" cuando `$ocd->canBeReceived()` es verdadero.

#### ~~Paso 8~~ ✅ COMPLETADO — Validación REPSE en recepción de servicios (2026-03-13)

**Archivos modificados:**
- `app/Models/Supplier.php` — `repseExpiresIn()` corregido
- `app/Models/ExpenseCategory.php` — nuevo método `isService()`
- `app/Services/ReceptionService.php` — `validateRepseIfService()` mejorado

**Bug corregido — `Supplier::repseExpiresIn()`:**

El método tenía los operandos de `diffInDays()` invertidos:
```php
// ANTES (bug): devuelve negativo cuando el REPSE aún es válido
return $this->repse_expiry_date->diffInDays(now(), false);
// expiry=2026-04-01, now=2026-03-13 → retorna -19 (¡negativo!)
// Consecuencia: -19 <= 30 siempre es true → warning para TODO REPSE válido

// DESPUÉS (correcto): días restantes, positivo = días que quedan
return (int) now()->diffInDays($this->repse_expiry_date, false);
// expiry=2026-04-01, now=2026-03-13 → retorna +19
// 19 <= 30 → warning correctamente activado solo cuando faltan ≤ 30 días
```

**Semántica de `repseExpiresIn()` después del fix:**
| Valor retornado | Significado |
|---|---|
| Positivo (ej: 45) | REPSE válido, vence en 45 días |
| ≤ 30 y positivo | REPSE válido pero próximo a vencer → warning |
| 0 | Vence hoy |
| Negativo | Ya venció — pero `hasValidRepseRegistration()` lo captura antes |
| `null` | No hay fecha de vencimiento registrada |

**Nuevo método `ExpenseCategory::isService()`:**

```php
public function isService(): bool
{
    return $this->code === 'SER';
}
```

Permite que el servicio consulte semánticamente si un ítem de OCD pertenece a la categoría Servicios, sin acoplar el código del servicio al valor `'SER'` hardcodeado.

**`validateRepseIfService()` — lógica completa:**

```
1. Si el proveedor no tiene provides_specialized_services → null (sin advertencia)
2. Si la orden es una OCD:
      Cargar items con expenseCategory
      Si ningún ítem tiene código SER → null (productos, no servicios → no aplica REPSE)
3. Si el REPSE está vencido o sin número → mensaje de ERROR (REPSE inválido)
4. Si vence en ≤ 30 días → mensaje de ADVERTENCIA (próximo a vencer)
5. En cualquier otro caso → null
```

**Diferencia de comportamiento OC vs OCD:**
- **OC estándar**: los ítems no tienen `expenseCategory`, así que el check se aplica a nivel del proveedor (`provides_specialized_services`). Si el proveedor presta servicios especializados, aplica.
- **OCD**: los ítems sí tienen `expenseCategory`. El check solo activa la advertencia si al menos un ítem tiene categoría `SER` (Servicios). Una OCD de materiales con un proveedor REPSE-registered no genera advertencia.

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
| `PurchaseOrderItem` / `DirectPurchaseOrderItem` | ✅ Completo | Paso 2 completado el 2026-03-13 |
| `Reception` | ✅ Completo | Paso 3 completado el 2026-03-13 |
| `ReceptionItem` | ✅ Completo | Paso 4 completado el 2026-03-13 |
| `ReceptionService` | ✅ Completo | Paso 5 completado el 2026-03-13 |
| `ReceptionController` + Rutas | ✅ Completo | Paso 6 completado el 2026-03-13 |
| Integración `BudgetCommitment` | ✅ Completo | Paso 7 completado el 2026-03-13. `received_at` agregado; `PARTIALLY_RECEIVED` en OCD corregido. |
| Validación REPSE en recepción | ✅ Completo | Paso 8 completado el 2026-03-13. Bug en `repseExpiresIn()` corregido. Check item-level para OCD. |
| Notificaciones de recepción | ❌ No existe | Implementar (Paso 9) |

**Respuesta directa:** Los pasos 1 a 8 están completados. La capa de modelos, lógica de negocio, controlador y vistas están listos. **Antes de usar el sistema, es necesario ejecutar `php artisan migrate` para aplicar las 6 migraciones creadas.** El único paso pendiente de implementar desde cero es el Paso 9: Notificaciones de recepción.

---

*Documento generado con base en análisis estático del código fuente. Pendiente de validación con el equipo.*
