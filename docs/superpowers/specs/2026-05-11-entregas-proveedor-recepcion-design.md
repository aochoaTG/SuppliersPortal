# Diseño: Integración de Entregas del Proveedor en Bandeja de Recepción

**Fecha:** 2026-05-11  
**Estado:** Aprobado — listo para implementación

---

## Contexto

El flujo de "Proveedor sin Recepción de Estación" ya está implementado en el lado del proveedor:

- El proveedor sube su remisión y la OC pasa a `DELIVERED_PENDING_RECEPTION`
- Se activa un contador de 3 días hábiles
- Los Jobs `SendDeliveryAlertDay0/2/3Job` envían alertas escalonadas

**El hueco:** el `ReceptionController` solo filtra por `['ISSUED', 'PARTIALLY_RECEIVED']`. Las OCs en `DELIVERED_PENDING_RECEPTION` son invisibles para el receptor de la estación.

---

## Alcance

Integrar `DELIVERED_PENDING_RECEPTION` en la bandeja de recepciones existente. Las OCs con este status aparecen en la misma lista que las normales, pero destacadas como urgentes con información del contador de tiempo y los datos de la entrega del proveedor.

---

## Sección 1 — Cambios en `ReceptionController`

### `overview()`
Ampliar `$pendingStatuses` para incluir `DELIVERED_PENDING_RECEPTION` en los conteos de `$regularCount` y `$directCount`.

### `datatableRegularPending()` y `datatableDirectPending()`
- Agregar `DELIVERED_PENDING_RECEPTION` a `$pendingStatuses`
- Agregar columna `dias_restantes`: calcula días hábiles restantes desde hoy hasta `reception_deadline_at`; para OCs sin deadline muestra `—`
- Agregar `rowAttr` que inyecta `data-urgent="1"` cuando el status es `DELIVERED_PENDING_RECEPTION`
- Cambiar el botón de acción de estas filas a `btn-danger` con ícono de reloj

### `pending()`
Ampliar `$pendingStatuses` con `DELIVERED_PENDING_RECEPTION` para que el receptor también vea estas OCs en su bandeja filtrada por locación asignada.

### `create()` y `createDirect()`
Cargar `deliveryEvidences` con `loadMissing(['deliveryEvidences'])` y pasar la primera evidencia a la vista como `$deliveryEvidence`.

---

## Sección 2 — Visual en el DataTable

### Resaltado de filas urgentes
- `createdRow` callback de DataTables aplica clase `table-danger` a filas con `data-urgent="1"`

### Badge de status
Las filas urgentes muestran dos badges:
1. `"Entregada — Pendiente de Captura"` en `bg-danger`
2. `"URGENTE"` en `bg-danger` con animación CSS `pulse`

### Columna "Días Restantes"
Badge de color dinámico según días hábiles restantes:
- **3 días:** `bg-success`
- **2 días:** `bg-warning text-dark`
- **1 día o menos / vencido:** `bg-danger`
- OCs ISSUED / PARTIALLY_RECEIVED: `—`

### Botón de acción en filas urgentes
- Cambia de `btn-outline-success` a `btn-danger`
- Ícono `ti-clock` en lugar de `ti-package-import`

---

## Sección 3 — Formulario de recepción

### Card de entrega del proveedor (condicional)
Visible solo cuando `$order->isDeliveredPendingReception()`. Se muestra **antes** del formulario de captura normal.

**Header:** `bg-danger text-white` — "Entrega registrada por el proveedor — pendiente de captura"

**Contenido:**
| Campo | Fuente |
|---|---|
| Fecha de entrega física | `supplier_delivered_at` |
| Nombre de quien recibió | `physical_receiver_name` |
| Observaciones del proveedor | `delivery_observations` |
| Fecha límite | `reception_deadline_at` + badge de días restantes |
| Remisión digital | `$deliveryEvidence->file_path` |

**Remisión digital:**
- PDF → botón de descarga + `<iframe>` de preview
- Imagen (JPG/PNG) → `<img>` con click para ampliar (lightbox o modal Bootstrap)

### Formulario de captura
Sin cambios — el receptor completa cantidad, conformidad, fotos como siempre.

---

## Lo que NO cambia

- El flujo de `store()` y `storeDirect()` — la transición de status la maneja el ReceptionService existente
- Los Jobs de alerta ya cancelan solos: verifican el status antes de enviar y no hacen nada si la recepción ya fue capturada
- El portal del proveedor — ya implementado y ajustado

---

## Cálculo de días hábiles restantes

Usar `Carbon::now()->diffInWeekdays($order->reception_deadline_at, false)` — devuelve número negativo si ya venció. Este cálculo se hace en el DataTable column callback del controller (no en una vista).

## Archivos a modificar

| Archivo | Cambio |
|---|---|
| `app/Http/Controllers/ReceptionController.php` | Ampliar statuses, agregar columna `dias_restantes`, cargar `deliveryEvidences` en `create()` y `createDirect()` |
| `resources/views/receptions/pending.blade.php` | CSS `pulse` + `createdRow` callback de DataTables |
| `resources/views/receptions/create.blade.php` | Card condicional de entrega del proveedor (usado por ambos `create` y `createDirect`) |

> `createDirect()` usa la misma vista `receptions/create.blade.php`, no hay archivo separado.
