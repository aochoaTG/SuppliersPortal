# Análisis Técnico: Estado Actual del Módulo de Recepciones

**Fecha:** 2026-03-13 (última actualización: 2026-03-24)
**Autor:** Análisis generado por Arquitecto de Software (Claude Code)
**Versión:** 2.0
**Estado:** Borrador para revisión de equipo — Pasos 1 a 9 implementados + correcciones y mejoras 2026-03-24

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

#### ~~Paso 9~~ ✅ COMPLETADO — Notificaciones de recepción (2026-03-19)

**Archivos creados/modificados:**
- `app/Notifications/ReceptionCompletedNotification.php` — creado
- `app/Services/ReceptionService.php` — método `receive()` actualizado + `notifyReception()` agregado

**`ReceptionCompletedNotification` — características:**

| Aspecto | Detalle |
|---|---|
| Canales | `['mail', 'database']` |
| Trait | `Queueable` (compatible con colas) |
| Parámetro | `Reception $reception` (con relación polimórfica `receivable`) |
| Asunto del email | `✅ Recepción REC-YYYY-NNNN (COMPLETADA) — OC-XXXX` o `🔶 ... (PARCIAL)` |
| Cuerpo del email | Folio, orden, proveedor, punto de entrega, receptor, fecha, estado de la orden, referencia del proveedor (si existe), aviso si la recepción es parcial |
| `toArray()` | `type`, `reception_id`, `reception_folio`, `reception_status`, `order_id`, `order_folio`, `url`, `message` |

**Destinatarios (método `notifyReception()` en `ReceptionService`):**
1. El **creador de la orden** (`$order->creator`) — siempre notificado, ya sea comprador (OC) o solicitante (OCD).
2. Todos los usuarios con rol **`buyer`** (`User::role('buyer')->get()`).
3. Se **deduplica por ID** para evitar notificación doble si el creador también tiene rol `buyer`.

> **Nota:** El actor de **Finanzas/Contabilidad** queda pendiente de definir con el equipo. Cuando se modele ese rol, bastará con agregar `User::role('finanzas')->get()` dentro de `notifyReception()`.

**Decisión de arquitectura — notificación fuera de la transacción:**

El método `notifyReception()` se invoca **después** del `DB::transaction`, no dentro de él. Esto garantiza que:
- La notificación solo se despacha si el commit fue exitoso (no hay rollback).
- Los trabajos en cola no leen datos en estado intermedio.

```php
// ReceptionService::receive() — flujo completo:
$reception = DB::transaction(fn() => /* ... persiste todo ... */);
$this->notifyReception($reception, $order);   // ← después del commit
return $reception;
```

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
| Notificaciones de recepción | ✅ Completo | Paso 9 completado el 2026-03-19. `ReceptionCompletedNotification` notifica al creador + buyers. |

**Respuesta directa:** Los pasos 1 a 9 están completados. La capa de modelos, lógica de negocio, controlador, vistas y notificaciones están listos. **Antes de usar el sistema, es necesario ejecutar `php artisan migrate` para aplicar las 6 migraciones creadas.** El módulo de recepciones está completamente implementado. El único ítem pendiente de definición es la incorporación del actor Finanzas/Contabilidad como destinatario adicional de notificaciones (ver detalle del Paso 9).

---

---

## 7. Cambios y Correcciones (2026-03-24)

Esta sección documenta los cambios implementados en la sesión del 2026-03-24, todos relacionados con el flujo de OCD, el módulo de recepciones y herramientas de soporte técnico.

---

### 7.1 Restauración del botón "Nueva Orden de Compra Directa"

**Archivos modificados:**
- `resources/views/purchase-orders/index.blade.php`

**Problema:** El botón para crear una OCD fue eliminado accidentalmente en el commit `72c3a59 Cambios estilos` junto con ajustes de estilos. La vista quedó sin forma de acceder a la creación de OCD directamente.

**Solución:** Se restauró el botón en el card-header de la vista, apuntando a `direct-purchase-orders.create`, con los estilos originales (`bg-white border-bottom`, título con `text-primary`).

---

### 7.2 Fix: Validación de presupuesto bloqueaba OCD con CC de consumo libre

**Archivos modificados:**
- `app/Http/Controllers/DirectPurchaseOrderController.php` — método `validateBudgetAvailability()`

**Problema:** Al aprobar una OCD cuyo centro de costo tiene `budget_type = FREE_CONSUMPTION`, el sistema lanzaba el error `"Presupuesto insuficiente: No existe un presupuesto aprobado para el centro de costo y año fiscal seleccionados."` Los CC de consumo libre no tienen presupuesto anual por diseño, por lo que la ausencia de presupuesto es correcta y no debe bloquear la aprobación.

**Solución:** Se agregó una verificación al inicio de `validateBudgetAvailability()`. Si el CC tiene `budget_type = 'FREE_CONSUMPTION'`, el método retorna `['available' => true]` inmediatamente sin buscar presupuesto:

```php
$costCenter = \App\Models\CostCenter::find($costCenterId);
if ($costCenter && $costCenter->budget_type === 'FREE_CONSUMPTION') {
    return ['available' => true, 'message' => 'Centro de costo de consumo libre.'];
}
```

---

### 7.3 Cambio de flujo OCD: aprobación pasa directamente a ISSUED

**Archivos modificados:**
- `app/Http/Controllers/DirectPurchaseOrderController.php` — método `approve()`

**Problema:** Al aprobar una OCD, el status quedaba en `APPROVED`. El módulo de recepciones filtra órdenes por status `ISSUED` y `PARTIALLY_RECEIVED`, por lo que una OCD aprobada nunca aparecía como pendiente de recepción. No existía ningún paso de "emisión" para OCD (a diferencia de las OC regulares).

**Solución:** El método `approve()` ahora cambia el status directamente a `ISSUED` en lugar de `APPROVED`. El flujo queda:

```
DRAFT → PENDING_APPROVAL → ISSUED → (PARTIALLY_RECEIVED) → RECEIVED
```

El status `APPROVED` deja de usarse en el ciclo de vida de una OCD.

> **Impacto en presupuesto:** El paso de comprometer presupuesto (paso 3 del método `approve()`) no se vio afectado, ya que opera sobre los ítems y el `AnnualBudget` independientemente del status final.

---

### 7.4 Nueva funcionalidad: Devolver OCD a revisión desde ISSUED (Opción A)

**Archivos modificados:**
- `app/Models/DirectPurchaseOrder.php` — nuevo método `canBeReturnedToRevision()`
- `app/Http/Controllers/DirectPurchaseOrderController.php` — método `return()` actualizado
- `resources/views/purchase-orders/show-direct.blade.php` — nuevo botón y form

**Contexto:** Una OCD ya aprobada (status `ISSUED`) puede tener errores detectados después de la aprobación (proveedor incorrecto, monto equivocado, etc.), siempre y cuando no se haya registrado ninguna recepción. Se implementó la opción de devolverla a revisión como `RETURNED`, lo que permite al solicitante corregirla y reenviarla a aprobación.

**Modelo — `canBeReturnedToRevision()`:**
```php
public function canBeReturnedToRevision(): bool
{
    return $this->status === 'ISSUED' && $this->receptions()->count() === 0;
}
```
Solo se puede devolver si está `ISSUED` y **no tiene recepciones** registradas.

**Controlador — `return()` actualizado:**

Cuando el método detecta que la OCD viene de `ISSUED`, libera el presupuesto comprometido antes de cambiar el status:

```
1. Detecta $comingFromIssued = ($status === 'ISSUED')
2. Valida canBeReturnedToRevision() — falla si ya tiene recepciones
3. Si viene de ISSUED: busca AnnualBudget y llama releaseCommitment() por cada categoría de ítem
4. Cambia status a RETURNED
5. Registra en historial de aprobaciones
6. Notifica al solicitante (DirectPurchaseOrderReturnedNotification)
```

**Vista — botón "Devolver a Revisión":**
- Botón amarillo (`btn-warning`) visible solo cuando `$ocd->canBeReturnedToRevision()` es true
- `form-return` también se renderiza para este estado
- El modal SweetAlert existente de instrucciones de devolución se reutiliza

**Flujo completo con devolución desde ISSUED:**
```
ISSUED → (sin recepciones) → RETURNED
  → Presupuesto liberado
  → Solicitante corrige
  → PENDING_APPROVAL
  → Aprobador vuelve a aprobar
  → ISSUED (presupuesto vuelve a comprometerse)
```

---

### 7.5 Fix: Botones de aprobación visibles cuando OCD está en RETURNED

**Archivos modificados:**
- `app/Models/DirectPurchaseOrder.php` — método `canBeApproved()` actualizado
- `resources/views/purchase-orders/show-direct.blade.php` — condiciones de visibilidad de botones y forms

**Problema:** Cuando una OCD quedaba en status `RETURNED`, la vista no mostraba los botones de Aprobar ni Rechazar, solo el botón "Enviar a Aprobación" (para el creador). El aprobador no tenía acciones disponibles.

**Solución:**

Modelo — `canBeApproved()` ahora acepta `RETURNED`:
```php
public function canBeApproved(): bool
{
    return in_array($this->status, ['PENDING_APPROVAL', 'RETURNED']);
}
```

Vista — los botones Aprobar y Rechazar se muestran con `PENDING_APPROVAL` **o** `RETURNED`. El botón "Devolver" (para enviar de vuelta) solo aparece en `PENDING_APPROVAL` (en `RETURNED` ya está devuelta). El `form-reject` también se renderiza para `RETURNED`.

---

### 7.6 Fix: Métodos faltantes en `ReceptionItem` causaban error en vista `show.blade.php`

**Archivos modificados:**
- `app/Models/ReceptionItem.php`

**Problema:** La vista `resources/views/receptions/show.blade.php` llamaba a `$item->hasRejections()`, `$item->quantity_rejected`, `$item->quantity_accepted` y `$item->rejection_reason`, pero ninguno existía en el modelo ni en la tabla (la migración usa `conformity` y `nonconformity_notes` en lugar de campos de rechazo por cantidad).

**Causa raíz:** El documento del Paso 4 (sección 4.3) describe la tabla con `quantity_rejected` y `rejection_reason`, pero la migración real implementó un enfoque diferente: conformidad binaria por ítem (`CONFORME`/`NO_CONFORME`) en lugar de cantidad rechazada numérica.

**Solución:** Se agregaron los métodos faltantes al modelo como adaptadores semánticos sobre los campos reales:

| Método / Accesor | Implementación | Notas |
|---|---|---|
| `hasRejections()` | `$this->conformity === 'NO_CONFORME'` | Alias de `isNonConforming()` |
| `getQuantityRejectedAttribute()` | `hasRejections() ? quantity_received : 0` | Si no conforme, toda la cantidad es rechazada |
| `getQuantityAcceptedAttribute()` | `hasRejections() ? 0 : quantity_received` | Inverso del anterior |
| `getRejectionReasonAttribute()` | `$this->nonconformity_notes` | Alias legible |

> **Nota de deuda técnica:** El documento del Paso 4 describe `quantity_rejected` como campo de la tabla, pero la migración implementó `conformity` (binario). Esta divergencia entre documentación y código fue la causa del error. El esquema actual no soporta "recibir 8 de 10 piezas y rechazar 2" — solo "conforme" o "no conforme" para toda la cantidad recibida. Si en el futuro se necesita rechazo parcial por cantidad, se requerirá migración.

---

### 7.7 Nueva herramienta: Log Viewer (solo usuario id=1)

**Archivos creados/modificados:**
- `app/Http/Controllers/LogViewerController.php` — creado
- `resources/views/dev/log-viewer.blade.php` — creado
- `resources/views/layouts/partials/navbar.blade.php` — ícono de insecto agregado
- `routes/web.php` — 2 rutas nuevas, import de `LogViewerController`

**Propósito:** Herramienta de desarrollo para visualizar el log de Laravel directamente desde el navegador, sin necesidad de acceso SSH o al sistema de archivos.

**Acceso:** Solo visible y accesible para el usuario con `id = 1`. Cualquier otro usuario recibe un `403`. El ícono de insecto rojo (`ti-bug`) en el navbar solo se renderiza cuando `Auth::id() === 1`.

**Rutas:**
```php
GET    /dev/logs   → LogViewerController::index()   // Ver log (últimas 500 líneas)
DELETE /dev/logs   → LogViewerController::clear()   // Vaciar el archivo
```

**Implementación — lectura eficiente:**

Se usa `SplFileObject` para leer solo el final del archivo sin cargarlo completo en memoria. Útil cuando el log es de varios MB:

```php
$file->seek(PHP_INT_MAX);       // mueve el cursor al EOF para obtener el total de líneas
$total = $file->key();          // número de líneas
$start = max(0, $total - 500);  // últimas 500 líneas
$file->seek($start);
while (!$file->eof()) { $content[] = $file->fgets(); }
```

**Características de la vista:**
- Fondo oscuro tipo terminal (`#1e1e1e`) con auto-scroll al final al cargar
- Tamaño actual del archivo en el header
- **Botón Copiar**: usa `navigator.clipboard.writeText()`. Feedback visual verde por 2 segundos. Swal de error si el clipboard no está disponible (sin HTTPS).
- **Botón Vaciar log**: Swal de confirmación con `icon: warning` antes de enviar el form DELETE.
- **Flash de éxito**: después de vaciar, se muestra Swal de éxito con auto-cierre a 3 segundos (en lugar de un `<div>` de alerta estático).

---

## 8. Resumen Ejecutivo Actualizado (2026-03-24)

| Componente | Estado | Observación |
|---|---|---|
| `ReceivingLocation` | ✅ Completo | Sin cambios |
| `PurchaseOrder` | ✅ Completo | Sin cambios |
| `PurchaseOrderItem` / `DirectPurchaseOrderItem` | ✅ Completo | Sin cambios |
| `Reception` / `ReceptionItem` | ✅ Completo | Fix 7.6: métodos `hasRejections()`, `quantity_rejected`, `quantity_accepted`, `rejection_reason` agregados |
| `ReceptionService` | ✅ Completo | Sin cambios |
| `ReceptionController` + Rutas | ✅ Completo | Sin cambios |
| Integración `BudgetCommitment` | ✅ Completo | Sin cambios |
| Validación REPSE | ✅ Completo | Sin cambios |
| Notificaciones de recepción | ✅ Completo | Sin cambios |
| Flujo de aprobación OCD | ✅ Actualizado | Fix 7.3: aprobación va directo a `ISSUED`. Fix 7.5: botones visibles en `RETURNED`. |
| Devolución a revisión desde ISSUED | ✅ Nuevo | 7.4: `canBeReturnedToRevision()`, liberación de presupuesto, botón en vista |
| CC de consumo libre en OCD | ✅ Corregido | Fix 7.2: `validateBudgetAvailability()` omite validación para `FREE_CONSUMPTION` |
| Log Viewer (dev) | ✅ Nuevo | 7.7: herramienta solo para usuario id=1 |

**Deuda técnica pendiente (vigente al cierre de la sesión 2026-03-24 mañana):**
- Actor Finanzas/Contabilidad no modelado como destinatario de notificaciones (pendiente desde Paso 9).

> La nota anterior sobre rechazo parcial por cantidad queda resuelta — ver Sección 9.

---

---

## 9. Alineación con FRU — Conformidad por Partida (2026-03-24)

**Versión:** 2.0
**Contexto:** Se realizó una revisión del formulario `resources/views/receptions/create.blade.php` contra el FRU (*Módulo de Recepción de Bienes y Servicios*, solicitante: Maribel García, Directora de Administración y Finanzas), específicamente contra la Sección 4.3 *Proceso de Recepción Estándar*. Se identificaron brechas críticas y se implementaron las correcciones correspondientes.

---

### 9.1 Brechas identificadas antes de los cambios

| Sección FRU | Brecha |
|---|---|
| 4.3-B Conformidad | El formulario usaba "Cant. Rechazada" + "Motivo de Rechazo" numérico. El FRU requiere un indicador explícito `CONFORME` / `NO CONFORME` por partida, con categoría y descripción detallada. |
| 4.3-B Categorías | No existía clasificación del tipo de no conformidad (defecto, especificaciones, empaque, etc.). |
| 4.3-B Longitud mínima | El campo de motivo no tenía restricción de longitud mínima (FRU exige ≥ 100 caracteres). |
| 4.3-C Evidencia fotográfica | No existía campo de fotografías por partida. El FRU requiere foto obligatoria cuando `NO_CONFORME`, opcional cuando `CONFORME` (1–5 fotos, JPG/PNG, máx. 5 MB c/u). |
| Lógica de negocio (bug) | Las cantidades `NO_CONFORME` se sumaban igual a `quantity_received` del ítem de la orden, marcando la OC como `RECEIVED` aunque el proveedor no hubiera repuesto las unidades rechazadas. |

---

### 9.2 Migración `2026_03_13_000004` — Rediseño de `reception_items`

**Acción:** Modificación directa de la migración existente (con `migrate:fresh`). No se creó migración adicional.

**Campos eliminados:**

| Campo | Razón |
|---|---|
| `quantity_rejected decimal(10,3)` | Reemplazado por conformidad binaria — la pregunta de negocio relevante es "¿cumple especificaciones?", no "¿cuántas unidades rechazas?". |
| `rejection_reason varchar(255)` | Reemplazado por `nonconformity_notes text` con semántica más precisa y sin límite de 255 caracteres. |

**Campos nuevos:**

| Campo | Tipo | Propósito |
|---|---|---|
| `conformity` | `string(20)` DEFAULT `'CONFORME'` | Indicador de cumplimiento de especificaciones por partida (FRU 4.3-B) |
| `nonconformity_type` | `string(50)` nullable | Categoría del problema: `defective`, `wrong_specs`, `wrong_product`, `damaged_packaging`, `other` |
| `nonconformity_notes` | `text` nullable | Descripción libre de la no conformidad (UI exige ≥ 100 chars cuando aplica) |
| `photos` | `json` nullable | Array de rutas de evidencia fotográfica. Obligatorio cuando `NO_CONFORME`, opcional cuando `CONFORME` (FRU 4.3-C) |

---

### 9.3 Modelo `ReceptionItem` — Rediseño completo

**Archivos modificados:**
- `app/Models/ReceptionItem.php`

**Constantes agregadas:**

```php
const CONFORMITY_OK   = 'CONFORME';
const CONFORMITY_FAIL = 'NO_CONFORME';

const NONCONFORMITY_TYPES = [
    'defective'         => 'Producto defectuoso',
    'wrong_specs'       => 'Especificaciones incorrectas',
    'wrong_product'     => 'Producto diferente al solicitado',
    'damaged_packaging' => 'Daño en empaque/producto',
    'other'             => 'Otro',
];
```

**Métodos nativos nuevos:**

| Método | Propósito |
|---|---|
| `isConforming(): bool` | Retorna `true` si `conformity === CONFORME` |
| `isNonConforming(): bool` | Retorna `true` si `conformity === NO_CONFORME` |
| `getNonconformityLabel(): string` | Traduce la clave del tipo a texto legible |
| `hasPhotos(): bool` | Verifica si hay fotos adjuntas |

**Adaptadores de compatibilidad (para `show.blade.php` y código existente):**

| Accesor / Método | Implementación | Semántica |
|---|---|---|
| `hasRejections()` | `conformity === NO_CONFORME` | Alias de `isNonConforming()` |
| `getQuantityRejectedAttribute()` | `isNonConforming() ? quantity_received : 0` | Si no conforme → toda la cantidad es rechazada |
| `getQuantityAcceptedAttribute()` | `isNonConforming() ? 0 : quantity_received` | Inverso del anterior |
| `getRejectionReasonAttribute()` | `$this->nonconformity_notes` | Alias legible para vistas heredadas |

> **Decisión de diseño:** Los adaptadores evitan modificar `show.blade.php` y otras vistas que ya usan la terminología anterior (`quantity_rejected`, `rejection_reason`). El contrato externo se mantiene; el almacenamiento interno es semánticamente correcto.

---

### 9.4 `ReceptionService` — Corrección de bug crítico en acumulación de cantidades

**Archivos modificados:**
- `app/Services/ReceptionService.php` — método `receive()`

**Bug anterior:**
```php
// ANTES: se acumulaba quantity_received sin importar la conformidad
if ($quantityReceived > 0) {
    $item->increment('quantity_received', $quantityReceived);
}
```

**Escenario que evidenciaba el bug:**
- OC: 5 unidades ordenadas
- Recepción 1: 2 unidades `NO_CONFORME` → `quantity_received` del ítem = 2, `pending` = 3
- Recepción 2: 3 unidades `CONFORME` → `quantity_received` del ítem = 5, `pending` = 0
- **Resultado incorrecto:** OC marcada como `RECEIVED`. Las 2 unidades rechazadas se daban por recibidas, el proveedor no tenía pendiente de reposición.

**Corrección:**
```php
// DESPUÉS: solo las cantidades CONFORMES avanzan el estado de la orden
$isConforming = ($lineData['conformity'] ?? ReceptionItem::CONFORMITY_OK) === ReceptionItem::CONFORMITY_OK;
if ($quantityReceived > 0 && $isConforming) {
    $item->increment('quantity_received', $quantityReceived);
}
```

**Comportamiento correcto después del fix:**

| Evento | `quantity_received` | `quantity_pending` | Estado OC |
|---|---|---|---|
| OC emitida (5 uds) | 0 | 5 | ISSUED |
| Recepción 1: 2 `NO_CONFORME` | 0 | 5 | PARTIALLY_RECEIVED |
| Recepción 2: 3 `CONFORME` | 3 | 2 | PARTIALLY_RECEIVED |
| Recepción 3: 2 `CONFORME` (reposición) | 5 | 0 | RECEIVED ✅ |

> **Implicación de negocio:** Las unidades rechazadas quedan visibles con su registro de no conformidad (tipo, descripción, fotos) pero **no cierran la orden**. El proveedor debe hacer una nueva entrega para cubrir las unidades rechazadas, lo que genera un nuevo `ReceptionItem` en el sistema.

---

### 9.5 `ReceptionController` — Validación alineada con nuevo modelo

**Archivos modificados:**
- `app/Http/Controllers/ReceptionController.php` — métodos `store()` y `storeDirect()`

**Reglas de validación actualizadas:**

Reemplazadas:
```php
// Eliminadas
'items.*.quantity_rejected' => 'nullable|numeric|min:0',
'items.*.rejection_reason'  => 'nullable|string|max:255',
```

Agregadas:
```php
'items.*.conformity'          => 'required|in:CONFORME,NO_CONFORME',
'items.*.nonconformity_type'  => "nullable|string|in:{$nonconformityTypes}",
'items.*.nonconformity_notes' => 'nullable|string|max:2000',
'items.*.photos'              => 'nullable|array|max:5',
'items.*.photos.*'            => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
```

**Nuevos métodos privados:**

| Método | Propósito |
|---|---|
| `validateConformityFields(Request, array): array` | Validación manual de ítems `NO_CONFORME`: exige tipo, notas ≥ 100 chars y al menos 1 foto. Devuelve array de errores con claves `items.N.campo`. Se usa porque `required_if` de Laravel no funciona correctamente con wildcards en arrays anidados. |
| `storeItemPhotos(Request, array): array` | Persiste las fotos de cada ítem en `storage/app/public/recepciones/fotos/` y devuelve el array de items con la clave `photos` reemplazada por las rutas resultantes. |

---

### 9.6 Vista `create.blade.php` — Rediseño de la sección de partidas

**Archivos modificados:**
- `resources/views/receptions/create.blade.php`

**Cambios en la tabla de partidas:**

| Columna anterior | Columna nueva | Cambio |
|---|---|---|
| Cant. Rechazada | Conformidad | Radio buttons: ✅ Conforme / ❌ No Conforme |
| Motivo de Rechazo | Evidencia Fotográfica | Input file por partida (JPG/PNG, máx. 5 fotos) |

**Fila expandible de No Conformidad:**

Cuando se selecciona `❌ No Conforme`, se despliega una fila adicional (`bg-danger-subtle`) debajo de la partida con:
- **Tipo de no conformidad** — select con las 5 categorías del FRU
- **Descripción detallada** — textarea con contador de caracteres en tiempo real (`N/100`). El contador cambia a verde cuando se alcanzan los 100 caracteres requeridos.

**Comportamiento de la evidencia fotográfica:**
- `CONFORME` → label "Opcional", sin atributo `required`
- `NO_CONFORME` → label "Obligatorio ✱", atributo `required` activado por JS

**Validaciones en el frontend (JS):**
- Bloqueo si `quantity_received > quantity_pending` (ya existía, conservado)
- Verificación de tipo de no conformidad seleccionado
- Verificación de longitud mínima de notas (< 100 chars → error)
- Verificación de al menos 1 foto adjunta cuando `NO_CONFORME`
- Límite de 5 fotos por partida (alerta SweetAlert si se excede)

**Resumen de confirmación mejorado (SweetAlert):**

Antes de guardar, el diálogo muestra un resumen dinámico:
```
Esta acción registrará la recepción y actualizará el estado de la orden.

[ ✅ 3 conforme(s) ]  [ ❌ 1 no conforme(s) ]

¿Confirmas el registro?
```

---

## 10. Resumen Ejecutivo Actualizado (2026-03-24 — sesión tarde)

| Componente | Estado | Observación |
|---|---|---|
| `ReceivingLocation` | ✅ Completo | Sin cambios |
| `PurchaseOrder` | ✅ Completo | Sin cambios |
| `PurchaseOrderItem` / `DirectPurchaseOrderItem` | ✅ Completo | Sin cambios |
| `Reception` | ✅ Completo | Sin cambios |
| `ReceptionItem` | ✅ Rediseñado | Sección 9.3: conformidad binaria, categorías, fotos, adaptadores de compat. |
| `ReceptionService` | ✅ Bug crítico corregido | Sección 9.4: cantidades `NO_CONFORME` ya NO avanzan el estado de la orden |
| `ReceptionController` | ✅ Actualizado | Sección 9.5: nueva validación, `validateConformityFields()`, `storeItemPhotos()` |
| `create.blade.php` | ✅ Alineado con FRU 4.3 | Sección 9.6: conformidad, categorías, fotos, resumen de confirmación |
| Flujo de aprobación OCD | ✅ Completo | Sin cambios (sección 7) |
| `BudgetCommitment` | ✅ Completo | Sin cambios |
| Validación REPSE | ✅ Completo | Sin cambios |
| Notificaciones de recepción | ✅ Completo | Pendiente: incorporar rol Finanzas |

**Brechas del FRU aún no implementadas (priorizadas para próximas sesiones):**

| Brecha | Sección FRU | Complejidad |
|---|---|---|
| Estatus "Recibido sin Capturar" + proveedor carga evidencia primero | 4.5 | Alta |
| Bloqueo/desbloqueo automático del portal por estación | 4.5 | Media |
| Recepción masiva con checkbox global | 4.4 | Media |
| Módulo de Provisiones Contables completo | 5 | Alta |
| Scheduler de cierre automático por inactividad (OC 10d / OCD 7d) | 3.2 | Media |
| Dashboard y reportes operativos | 7 | Media |
| Notificación al Proveedor al confirmar recepción | 4.3 | Baja |
| Indicadores visuales de urgencia en bandeja (semáforo) | 4.2 | Baja |

---

---

## 11. Historial de Recepciones en Detalle de OC (2026-03-24)

**Contexto:** El usuario necesitaba visualizar los comprobantes de recepción y los registros de no conformidad directamente desde el contexto de una Orden de Compra, sin tener que navegar a un módulo separado.

### Decisión de arquitectura

Se descartó mostrar comprobantes en `index.blade.php` (listado) porque:
- Una OC puede tener múltiples recepciones parciales → no cabe en una columna
- El listado ya tiene la columna de acciones saturada
- La información de recepción pertenece al contexto de una OC específica

**Flujo de navegación implementado:**
```
index.blade.php          show.blade.php                  receptions/show.blade.php
─────────────────        ──────────────────────────────  ─────────────────────────
Lista de OCs         →   Detalle de la OC            →   Comprobante completo
[badge RECEIVED]         Historial de Recepciones         (imprimible, con fotos,
                         ┌────────────────────────┐        conformidad por partida)
                         │ REC-2026-0001 COMPLETADA│──►
                         │ REC-2026-0002 PARCIAL   │──►
                         └────────────────────────┘
```

---

### 11.1 `PurchaseOrderController::show()` — eager loading de recepciones

**Archivo modificado:** `app/Http/Controllers/PurchaseOrderController.php`

Se agregaron tres relaciones al `load()` existente:

```php
'receptions.items.receivableItem',
'receptions.receiver',
'receptions.receivingLocation',
```

Esto evita N+1 queries al renderizar el historial con múltiples recepciones y sus partidas.

---

### 11.2 `show.blade.php` — sección "Historial de Recepciones"

**Archivo modificado:** `resources/views/purchase-orders/show.blade.php`

Se añadió una nueva tarjeta **fuera del área imprimible** (`d-print-none`) debajo del documento de la OC. Características:

| Elemento | Detalle |
|---|---|
| Barra de progreso | Partidas completamente recibidas / total. Verde cuando llega al 100%. |
| Badge "Contiene no conformidades" | Aparece en rojo si cualquier `ReceptionItem` de la OC es `NO_CONFORME` |
| Botón "Registrar Recepción" | Visible solo si `$purchaseOrder->canBeReceived()` es true |
| Tabla de recepciones | Folio, fecha, receptor, punto de entrega, partidas, no conformes, estado |
| Columna "No Conformes" | Badge rojo con conteo; guion si todas son conformes |
| Botón comprobante | `→ receptions.show` para cada recepción |
| Estado vacío | Mensaje con ícono cuando no hay recepciones registradas |

---

### 11.3 `receptions/show.blade.php` — rediseño completo del comprobante

**Archivo modificado:** `resources/views/receptions/show.blade.php`

**Cambios respecto a la versión anterior:**

| Elemento | Antes | Ahora |
|---|---|---|
| Columnas de tabla | Recibido / Rechazado / Aceptado / Motivo | Recibido / Conformidad |
| Indicador de calidad | Cantidad numérica rechazada | Badge `✅ Conforme` / `❌ No Conforme` |
| Filas de no conformidad | No existían | Fila expandida `bg-danger-subtle` con Tipo + Descripción |
| Evidencia fotográfica | No existía | Miniaturas (64×64 px) con enlace a imagen completa |
| Resumen de conformidad | No existía | Tarjetas de conteo: "N Conformes / N No Conformes" |
| Breadcrumb | Genérico | Incluye enlace a la OC / OCD origen |
| Botones de header | Solo "Regresar" | "Regresar" + "Ver OC" / "Ver OCD" según tipo |
| Estilos de impresión | Básicos | Fila no-conforme con `background-color` para impresión |

**Lógica de las filas de no conformidad:**

Cada `ReceptionItem` con `conformity = NO_CONFORME` genera **dos filas** en la tabla:
1. Fila principal (resaltada en `table-warning`) con descripción, cantidad y badge rojo
2. Fila de detalle (`nonconf-row`, `bg-danger-subtle`) con tipo de no conformidad y descripción completa

Las fotos se muestran como miniaturas clickeables en la misma celda de descripción, abriendo la imagen completa en nueva pestaña.

---

### 11.4 Historial de Recepciones en OCD (`show-direct.blade.php`)

**Contexto:** La sección de historial se implementó inicialmente solo para OC estándar (`show.blade.php`). Se extendió a OCD al identificar que ambos tipos de orden siguen el mismo flujo de recepción.

**Archivos modificados:**
- `app/Http/Controllers/PurchaseOrderController.php` — método `showDirect()`
- `resources/views/purchase-orders/show-direct.blade.php`

**`showDirect()` — eager loading agregado:**
```php
'receptions.items.receivableItem',
'receptions.receiver',
'receptions.receivingLocation',
```

**Vista `show-direct.blade.php`:**

La sección es idéntica a la de `show.blade.php` en estructura y comportamiento. La única diferencia es:
- Variable: `$directPurchaseOrder` en lugar de `$purchaseOrder`
- Botón "Registrar Recepción" apunta a `receptions.create-direct` en lugar de `receptions.create`

> **Nota de implementación:** El bloque HTML de la sección se insertó dentro del `@section('content')` existente, después del `@endpush` del bloque de scripts. La vista `show-direct.blade.php` usa la estructura `@push('scripts')` dentro del `@section('content')`, a diferencia de `show.blade.php` que no tiene scripts propios.

---

### 11.5 Estado del flujo de navegación

| Pantalla | Ruta | Estado |
|---|---|---|
| Listado de OCs | `purchase-orders.index` | Sin cambios — badge de estado indica si fue recibida |
| Detalle de OC estándar | `purchase-orders.show` | ✅ Historial de Recepciones (Sección 11.2) |
| Detalle de OCD | `direct-purchase-orders.show` | ✅ Historial de Recepciones (Sección 11.4) |
| Comprobante de recepción | `receptions.show` | ✅ Actualizado: conformidad, fotos, no conformidades |
| Formulario de recepción OC | `receptions.create` | ✅ Actualizado (Sección 9) |
| Formulario de recepción OCD | `receptions.create-direct` | ✅ Actualizado (Sección 9) |

---

## 12. Resumen Ejecutivo (2026-03-24)

| Componente | Estado | Notas |
|---|---|---|
| Modelos de datos (`PurchaseOrder`, `Reception`, `ReceptionItem`, etc.) | ✅ Completo | — |
| `ReceptionService` | ✅ Completo | Bug de conteo NO_CONFORME corregido (Sección 9.4) |
| `ReceptionController` | ✅ Completo | Validación conformidad + fotos por ítem |
| `create.blade.php` | ✅ Alineado con FRU 4.3 | Conformidad, categorías, fotos, resumen |
| `show.blade.php` (OC estándar) | ✅ Historial de Recepciones | Sección 11.2 |
| `show-direct.blade.php` (OCD) | ✅ Historial de Recepciones | Sección 11.4 |
| `receptions/show.blade.php` | ✅ Rediseñado | Sección 11.3 |
| Flujo de aprobación OCD | ✅ Completo | Sección 7 |
| Notificaciones de recepción | ✅ Completo | Pendiente: rol Finanzas |

**Brechas del FRU pendientes (sin cambios respecto a Sección 10):**

| Brecha | Sección FRU | Prioridad |
|---|---|---|
| Estatus "Recibido sin Capturar" + proveedor carga evidencia | 4.5 | Alta |
| Bloqueo/desbloqueo automático del portal por estación | 4.5 | Media |
| Recepción masiva con checkbox global | 4.4 | Media |
| Módulo de Provisiones Contables | 5 | Alta |
| Scheduler de cierre por inactividad | 3.2 | Media |
| Dashboard y reportes operativos | 7 | Media |
| Notificación al Proveedor al confirmar recepción | 4.3 | Baja |

---

## 13. Visualización de Remisión del Proveedor (2026-03-24)

### 13.1 Contexto

El campo `remission_path` del modelo `Reception` almacena el archivo de remisión que el proveedor carga durante el registro de la recepción. Hasta esta versión, dicho archivo se guardaba correctamente en disco pero no era accesible desde ninguna vista del sistema.

### 13.2 Cambios Implementados

**Nueva ruta de descarga (`routes/web.php`):**
```
GET /receptions/{reception}/remission  →  receptions.remission.download
```

**Nuevo método en `ReceptionController`:**
```php
public function downloadRemission(Reception $reception)
```
- Verifica que `remission_path` exista en el modelo y en disco (`abort_if` / `abort_unless`).
- Devuelve el archivo con nombre legible: `Remision-{folio}.{ext}`.

**`receptions/show.blade.php` — Comprobante de Recepción:**
- Botón "Descargar Remisión" (`ti-paperclip`) junto a la Ref. Remisión existente.
- Solo visible cuando `remission_path` tiene valor.
- Marcado `d-print-none` para no aparecer al imprimir.

**`purchase-orders/show.blade.php` — Historial OC Regular:**
- Nueva columna "Remisión" con botón de descarga por fila (`ti-paperclip`).
- Si la recepción no tiene archivo: muestra `—`.
- El `title` del botón incluye la referencia textual de la remisión para contexto rápido.

**`purchase-orders/show-direct.blade.php` — Historial OCD:**
- Mismo cambio que el historial de OC Regular.

### 13.3 Flujo de Acceso

```
OC/OCD show  →  Historial de Recepciones  →  [📎]  →  descarga directa del archivo
                                                ↓
                                   receptions/show  →  [Descargar Remisión]
```

---

*Documento actualizado con base en análisis de código fuente y revisión contra FRU del 2026-03-24.*
