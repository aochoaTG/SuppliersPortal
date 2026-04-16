# SISTEMA_FLUJO_ACTUAL.md
> Portal de Proveedores TotalGas — Guía técnica para nuevos desarrolladores  
> Generado: 2026-04-15 | Laravel 12 · PHP 8.2 · SQL Server · Spatie Permission

---

## Resumen ejecutivo

El Portal de Proveedores de TotalGas es una plataforma interna que cubre el ciclo completo de compras: desde que un área solicita un bien o servicio hasta que se recibe físicamente en la empresa. El flujo central involucra cuatro actores principales —**Requisitor, Comprador (Buyer), Proveedor y Aprobador**— y tres grandes módulos que se encadenan: **Requisición → Cotización → Orden de Compra → Recepción**.

Paralelamente, el sistema gestiona un **Portal de Autogestión para Proveedores** (carga de documentos, respuesta a solicitudes de cotización) y un módulo de **Presupuesto** que valida la disponibilidad de fondos en cada compra. Todos los flujos de aprobación están gobernados por **niveles configurables por monto** vía `ApprovalLevel`, y cada transición de estado queda auditada mediante Spatie Activity Log y notificaciones por correo + base de datos.

---

## Diagrama de flujo general

```
[Usuario] → POST /login → Middleware auth+lock → /dashboard
                                │
               ┌────────────────┼────────────────────────┐
               │                │                        │
        [role:buyer/         [role:supplier]       [role:superadmin]
         requester/etc.]      (Portal)              (Gestión global)
               │                │                        │
               ▼                ▼                        ▼
        Crea Requisición   Ve RFQs            Configura ApprovalLevels
        DRAFT → PENDING    Cotiza             Aprueba Cotizaciones
               │                │             Gestiona Catálogos
               ▼                │
        Comprador valida   ──────┘
        PENDING → IN_QUOTATION
               │
               ▼
        Comprador crea RFQs → Se envían a Proveedores (email/portal)
               │
               ▼
        Proveedores responden → Comparación → Selección
               │
               ▼
        QuotationSummary (QUOTED) → Aprobación (por nivel de monto)
               │
               ▼
        Orden de Compra (OC) generada → ISSUED → RECEIVED
        o Compra Directa (OCD)       → Aprobación → ISSUED → RECEIVED
               │
               ▼
        Reception creada → Folio REC-YYYY-#### → BudgetCommitment.RECEIVED
               │
               ▼
        Proveedor entrega físicamente → SupplierDeliveryEvidence → COMPLETED
```

---

## Puntos de entrada

| Ruta | Middleware | Descripción |
|------|-----------|-------------|
| `GET /` | — | Redirige a login |
| `GET/POST /login` | guest | Autenticación |
| `GET /lock` | auth | Pantalla de bloqueo |
| `POST /unlock` | auth | Desbloquear sesión |
| `GET /dashboard` | auth, lock | Panel principal |
| `POST /api/empleados/recibir` | api.key | Importar empleado desde Python |
| `POST /api/empleados/resolver-lideres` | api.key | Segunda pasada resolución de líderes |

### Middlewares personalizados

**`CheckLockScreen`** (`lock`): Si la sesión tiene `lock.is_locked = true`, redirige a `/lock` excepto para rutas de desbloqueo y logout. Si el usuario activo cambió, limpia el lock automáticamente.

**`ApiKeyMiddleware`** (`api.key`): Lee el header `X-API-KEY` y lo compara contra `config('app.empleados_api_key')`. Retorna `401` si no coincide. Protege los endpoints de importación de empleados usados por el script Python.

---

## Secuencia de pasos por rol

### Rol: Requisitor (requester)

```
1. Crea requisición → DRAFT
   - Selecciona empresa, centro de costo, fecha requerida
   - Agrega partidas desde catálogo ProductService (solo ACTIVE)
   - Cada partida requiere cantidad y categoría de gasto (expense_category_id)

2. Envía → PENDING
   - Mínimo 1 partida válida
   - Se generan notificaciones: RequisitionSubmittedNotification → Buyers

3. Espera validación de Compras
   - Puede recibir: PAUSED, REJECTED, IN_QUOTATION

4. Si REJECTED → puede corregir y reenviar
5. Si PAUSED → espera hasta que Compras reactive
6. Si IN_QUOTATION → espera sin poder editar

7. Una vez QUOTED → Compras aprueba cotización
8. APPROVED → se genera OC automáticamente
```

### Rol: Buyer (Comprador)

```
1. Bandeja "Pendientes de Validación" (PENDING)
   - Revisa requisición: specs claras, tiempo factible, alternativas evaluadas
   - Puede PAUSAR (esperando catálogo) o RECHAZAR con motivo

2. Aprueba para cotización → IN_QUOTATION
   - Notifica al requisitor: RequisitionInQuotationNotification

3. Crea RFQs (Solicitudes de Cotización)
   - Puede agrupar partidas en QuotationGroups o cotizar por partida individual
   - Selecciona proveedores del catálogo (excluye EFOS 69B)
   - Envía → SENT → proveedores reciben notificación/email

4. Recibe respuestas de proveedores → RFQ status RECEIVED

5. Compara cotizaciones (RfqComparisonController)
   - Selecciona mejor opción
   - Requisición → QUOTED + QuotationSummary creado

6. QuotationSummary es enviado a aprobación según monto:
   - ApprovalService.getLevelForAmount(total) → determina nivel requerido
   - Si superadmin aprueba → Requisición APPROVED

7. Genera OC desde cotización aprobada
```

### Rol: Supplier (Proveedor)

```
Portal: /supplier/dashboard

1. Alta: se registra (rol supplier), completa datos de empresa
2. Carga documentos requeridos en /supplier/documents
3. Admin revisa y acepta documentos (DocumentReviewController)

4. Cuando es invitado a RFQ:
   - Recibe email (NewRfqForSupplierNotification)
   - Ve detalles en /supplier/rfq/{rfq}
   - Sube cotización (PDF + precio) → RfqResponse SUBMITTED

5. Si cotización es rechazada → RfqResponse REJECTED
6. Ve historial de cotizaciones en /supplier/quotations/history

7. Entrega física: /supplier/deliveries
   - Registra SupplierDeliveryEvidence
   - OC pasa a DELIVERED_PENDING_RECEPTION
```

### Rol: Superadmin

```
- Gestión total del sistema
- Configura ApprovalLevels (montos por nivel de aprobación)
- Aprueba QuotationSummaries en /approvals/quotations
- Gestiona catálogos: empresas, estaciones, impuestos, centros de costo
- CRUD de usuarios y asignación de roles
- Puede crear/editar OCDs
- Revisa documentos de proveedores
```

### Rol: Aprobador de OCD (purchasing_manager / buyer / director)

```
1. Recibe notificación: NewDirectPurchaseOrderNotification
2. Revisa OCD en /direct-purchase-orders/{id}
3. Puede: APROBAR → APPROVED, RECHAZAR → REJECTED, DEVOLVER → RETURNED
   - RETURNED: el creador puede editar y reenviar
   - REJECTED: requiere motivo

Advertencia: El sistema auto-cierra una OCD en PENDING_APPROVAL
después de 7 días sin actividad (CLOSED_BY_INACTIVITY).
```

---

## Estados clave de las entidades

### Requisición (`requisitions.status`)

```
DRAFT
  │ submitToCompras()
  ▼
PENDING ──────────────────────────────► PAUSED (hold)
  │                                       │
  │ approveForQuotation()                 │ resume()
  ▼                                       ▼
IN_QUOTATION ◄──────────────────────── PENDING
  │
  │ (proveedor cotiza + comprador selecciona)
  ▼
QUOTED
  │
  ├──► PENDING_BUDGET_ADJUSTMENT (si no hay fondos disponibles)
  │
  ▼
APPROVED ──► COMPLETED (OC generada y recibida)

En cualquier momento: → CANCELLED
PENDING puede ir a: → REJECTED (con motivo)
```

### RFQ (`rfqs.status`)

```
DRAFT → SENT → RECEIVED
  └──────────────────► CANCELLED (en cualquier momento)

Respuesta del proveedor (rfq_responses):
DRAFT → SUBMITTED → APPROVED / REJECTED
```

### Orden de Compra — OC Regular (`purchase_orders.status`)

```
OPEN → ISSUED → PARTIALLY_RECEIVED → RECEIVED → PAID
  └──────────────────────────────────────────► CANCELLED
  └──────────────────────────────────────────► CLOSED_BY_INACTIVITY (10 días)
                                           ► DELIVERED_PENDING_RECEPTION
```

### Orden de Compra Directa — OCD (`odc_direct_purchase_orders.status`)

```
DRAFT → PENDING_APPROVAL → APPROVED → ISSUED → PARTIALLY_RECEIVED → RECEIVED
                │
                ├──► REJECTED
                └──► RETURNED → (editar) → PENDING_APPROVAL
PENDING_APPROVAL → CLOSED_BY_INACTIVITY (7 días sin actividad)
```

### Producto/Servicio (`products_services.status`)

```
PENDING → ACTIVE (approve)
       → REJECTED (reject con motivo)
ACTIVE → INACTIVE (deactivate)
INACTIVE → ACTIVE (reactivate)

Solo productos ACTIVE pueden usarse en requisiciones.
```

### Presupuesto (`budget_commitments.status`)

```
COMMITTED (bloquea fondos al crear OC/OCD)
  → RECEIVED (al completar recepción)
  → RELEASED (al liberar/pagar)
```

---

## Modelo de datos relevante

```
users (1)──────(1) suppliers
  │                    │
  │                    ├── supplier_documents
  │                    ├── supplier_sirocs
  │                    └── rfq_suppliers (pivot)
  │
  │ (created_by / requested_by)
  ▼
requisitions (1)──────(N) requisition_items
  │                              │
  │                              └── expense_category_id → expense_categories
  │
  ├── (1) quotation_summary ──── (1) approval_level
  │              │
  │              └── selected_supplier_id → suppliers
  │
  ├── (N) rfqs ──── (N) rfq_suppliers (pivot)
  │         └── (N) rfq_responses
  │
  └── (1) purchase_order ──── (N) receptions
                  │                    └── (N) reception_items
                  └── (1) budget_commitment

direct_purchase_orders ──── (N) receptions
                       ──── (1) budget_commitment
                       ──── (N) direct_purchase_order_approvals
                       ──── (N) direct_purchase_order_documents

annual_budgets (1)──── (N) budget_monthly_distributions
  │                              │
  cost_centers                   └── expense_categories

employees ──── (1) users (user_id, nullable)
  - first_name, last_name
  - leader (employee_number del líder, resuelto en 2 pasadas)
  - is_active
```

### Tablas con campos de estado relevantes

| Tabla | Campo de estado | Valores posibles |
|-------|----------------|-----------------|
| `requisitions` | `status` | DRAFT, PENDING, PAUSED, IN_QUOTATION, QUOTED, PENDING_BUDGET_ADJUSTMENT, APPROVED, COMPLETED, REJECTED, CANCELLED |
| `rfqs` | `status` | DRAFT, SENT, RECEIVED, CANCELLED |
| `rfq_responses` | `status` | DRAFT, SUBMITTED, APPROVED, REJECTED |
| `quotation_summaries` | `approval_status` | pending, approved, rejected |
| `purchase_orders` | `status` | OPEN, ISSUED, PARTIALLY_RECEIVED, RECEIVED, PAID, CANCELLED, CLOSED_BY_INACTIVITY, DELIVERED_PENDING_RECEPTION |
| `odc_direct_purchase_orders` | `status` | DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, RETURNED, ISSUED, PARTIALLY_RECEIVED, RECEIVED, CANCELLED, CLOSED_BY_INACTIVITY, DELIVERED_PENDING_RECEPTION |
| `products_services` | `status` | PENDING, ACTIVE, REJECTED, INACTIVE |
| `budget_commitments` | `status` | COMMITTED, RECEIVED, RELEASED |
| `annual_budgets` | `status` | draft, approved, active, archived |
| `receptions` | `status` | PENDING, PARTIAL, COMPLETED |

---

## Reglas de negocio críticas

- **RN-001** Solo productos con `status = ACTIVE` y estructura contable completa (`account_major` + `account_sub` + `account_subsub`) pueden usarse en requisiciones. Verificado en `ProductService::isAvailableForRequisitions()`.

- **RN-002** Las requisiciones **no manejan precios**. Los precios se asignan exclusivamente en cotizaciones. El costo se conoce hasta la etapa `QUOTED`.

- **RN-003** Una requisición debe tener al menos una partida válida para poder enviarse. Cada partida requiere obligatoriamente una `expense_category_id`.

- **RN-004** Compras **no puede modificar** la categoría de gasto (`expense_category_id`) de una partida. Solo el requisitor la asigna.

- **RN-005** El nivel de aprobación se determina automáticamente por monto total via `ApprovalService::getLevelForAmount()`. Los niveles se configuran en `/approval-levels` (solo superadmin).

- **RN-006** Un proveedor en lista SAT EFOS 69B con situación `Definitivo` o `Presunto` **no puede ser seleccionado** en ninguna cotización. El scope `notEfos69b()` en `Supplier` filtra automáticamente.

- **RN-007** Si el proveedor ofrece servicios especializados (`provides_specialized_services = true`), se valida REPSE: registro activo y no vencido.

- **RN-008** Al crear una OC o OCD aprobada, se genera automáticamente un `BudgetCommitment` en estado `COMMITTED` que bloquea el monto del presupuesto mensual correspondiente.

- **RN-009** Las OC se **auto-cierran** (`CLOSED_BY_INACTIVITY`) si no hay actividad en **10 días naturales** desde su creación. Se envía aviso 2 días antes.

- **RN-010** Las OCD se **auto-cierran** si no son aprobadas en **7 días** desde su envío. Se envía aviso 2 días antes.

- **RN-011** Una OCD en estado `RETURNED` puede ser editada por su creador y reenviada. Una OCD en `REJECTED` no puede modificarse.

- **RN-012** Una OCD en `DRAFT` solo puede ser enviada si tiene al menos un ítem (`canBeSubmitted()` valida `items()->count() > 0`).

- **RN-013** El folio de OCD es asignado al momento de **submit**, no al crear el borrador (`generateNextFolio()` en `submit()`).

- **RN-014** El campo `leader` en `employees` se almacena como el `employee_number` del líder (no nombre) después de resolución. La importación realiza dos pasadas: una inmediata durante el upsert y una segunda pasada al finalizar el archivo completo (`POST /api/empleados/resolver-lideres`).

- **RN-015** El proveedor no puede editar ni eliminar una cotización que ya fue `SUBMITTED`. Solo puede eliminar borradores (`deleteDraft()`).

- **RN-016** La validación presupuestal es **bloqueante**. Si no hay saldo disponible en `BudgetMonthlyDistribution` para el centro de costo + categoría de gasto + mes correspondiente, el sistema impide completamente la creación de la OC u OCD. No es una advertencia.

- **RN-017** La visibilidad de entidades (productos, requisiciones, presupuestos) está **acotada por empresa y centro de costo**. Un usuario solo tiene acceso a los registros de las empresas y centros de costo a los que está asignado en la relación `company_user` / `cost_center_user`. Los nuevos desarrolladores deben aplicar estos filtros en cualquier query que construyan sobre estas entidades.

- **RN-018** El sistema sincroniza automáticamente la lista SAT EFOS 69B todos los días a las **2:30 AM** via `php artisan efos:sync`. Un proveedor puede quedar bloqueado de nuevas cotizaciones al día siguiente de aparecer en la lista, sin intervención manual.

---

## Notificaciones enviadas

| Evento | Notificación | Destinatario |
|--------|-------------|--------------|
| Requisición enviada | `RequisitionSubmittedNotification` | Requisitor + todos los Buyers |
| Requisición validada | `RequisitionInQuotationNotification` | Requisitor |
| Requisición rechazada | `RequisitionRejectedNotification` | Requisitor |
| Requisición reactivada | `RequisitionReactivatedNotification` | Requisitor |
| RFQ enviada | `RfqSentToSuppliersNotification` + `NewRfqForSupplierNotification` | Proveedores invitados |
| RFQ cancelada | `RfqCancelledForSupplierNotification` + `RfqCancelledForRequesterNotification` | Proveedor + Requisitor |
| OCD creada | `NewDirectPurchaseOrderNotification` | `assigned_approver_id` |
| OCD aprobada | `DirectPurchaseOrderApprovedNotification` | Creador |
| OCD rechazada | `DirectPurchaseOrderRejectedNotification` | Creador |
| OCD devuelta | `DirectPurchaseOrderReturnedNotification` | Creador |
| OCD por vencer | `DirectPurchaseOrderInactivityWarningNotification` | Aprobador asignado |
| OCD auto-cerrada | `DirectPurchaseOrderClosedByInactivityNotification` | Creador |
| OC por vencer | `PurchaseOrderInactivityWarningNotification` | Creador/Aprobador |
| OC auto-cerrada | `PurchaseOrderClosedByInactivityNotification` | Creador |
| Recepción completada | `ReceptionCompletedNotification` | Receptor + stakeholders |
| Nuevo producto solicitado | `NewProductRequestedNotification` | Administrador |

**Canales activos:** `mail` (correo) + `database` (notificaciones en UI).

---

## Estructura de roles (Spatie Permission)

| Rol | Acceso principal |
|-----|-----------------|
| `superadmin` | Acceso total: catálogos, aprobaciones, configuración |
| `buyer` | Requisiciones, RFQs, OCs, OCDs, validación de proveedores |
| `supplier` | Portal de proveedor (solo rutas `/supplier/*`) |
| `requester` | Crear y enviar requisiciones propias |
| `purchasing_manager` | Aprobación de OCDs de alto monto |

La asignación de roles es many-to-many entre `users` y `roles` via Spatie. Los usuarios también tienen relaciones many-to-many con `companies` y `cost_centers` (con pivote que incluye `is_default`, `is_active`).

---

## Folios autogenerados

| Entidad | Formato | Método |
|---------|---------|--------|
| Requisición | `REQ-YYYY-###` | `Requisition::nextFolio()` |
| RFQ | `RFQ-YYYY-###` | `Rfq::nextFolio()` |
| OC Regular | `OC-YYYY-####` | Generado al aprobar cotización |
| OC Directa | `OCD-YYYY-####` | `DirectPurchaseOrder::generateNextFolio()` |
| Recepción | `REC-YYYY-####` | `Reception::generateNextFolio()` |
| Producto/Servicio | `PROD-######` | `ProductService::nextCode()` |

---

## Módulo de empleados (integración con Python)

El sistema recibe datos de empleados desde un script Python externo que lee archivos CSV. El flujo es:

```
Script Python
  ├── Por cada empleado:
  │     POST /api/empleados/recibir  (Header: X-API-KEY)
  │     - Upsert del empleado (busca por employee_number+company o RFC)
  │     - Limpia nombre: "Apellidos, Nombre" → first_name / last_name
  │     - Limpia líder: quita prefijo (ej: "Jarudo - ") → busca employee_number
  │     - Registra EmployeeEvents por cada campo que cambió
  │
  └── Al terminar todos:
        POST /api/empleados/resolver-lideres
        - Segunda pasada: resuelve líderes que quedaron como nombre
          (líder procesado después que sus subordinados en el mismo archivo)
        - Retorna { resueltos: N, sin_resolver: M }
```

**Lógica de resolución de líder** (`EmployeeController::resolverLider`):
- Con coma en origen (`"Rivas Herna, Jonatan"`): LIKE doble `first_name LIKE 'Jonatan%' AND last_name LIKE 'Rivas Herna%'`
- Sin coma (`"Alejandra Orozco Muñoz"`): búsqueda concatenada `(first_name + ' ' + last_name) LIKE 'Alejandra Orozco Muñoz%'`
- Múltiples candidatos: desempata por `is_active = 'SI'`; si aún hay empate, toma el `employee_number` mayor

---

## Áreas identificadas para mejorar / atención

### Código que requiere revisión

- **`Rfq.supplier_id`** — Columna legacy para "único proveedor". El modelo actual usa la tabla pivot `rfq_suppliers`. El campo `supplier_id` en `rfqs` puede generar confusión. `[REVISAR MANUALMENTE]` — ¿Está siendo usado aún o se puede deprecar?

- **`ProductService.cost_center_id` y `company_id`** — Son filtros de visibilidad activos. Un requester solo ve productos asociados a sus empresas y centros de costo asignados. Lo mismo aplica a requisiciones, presupuestos y cualquier entidad acotada por empresa.

- **`RequisitionWorkflowController::consume()`** — Existe el método pero no es claro su propósito final (¿marcar partidas como consumidas?). `[REVISAR MANUALMENTE]`

- **`BudgetService`** — Referenciado en código pero su existencia/implementación no se confirmó en la exploración. `[REVISAR MANUALMENTE]` — ¿Está implementado o es trabajo en progreso?

### Puntos frágiles

- **Auto-cierre por inactividad** — El servidor ejecuta `php artisan schedule:run` cada minuto via cron (`* * * * *`). El cierre automático de OCs (10 días) y OCDs (7 días) se define en el scheduler de Laravel (`app/Console/Kernel.php` o `routes/console.php`). Adicionalmente, corre un job diario a las 2:30 AM (`30 2 * * *`) para `efos:sync` (sincronización de lista negra SAT EFOS 69B).

- **Resolución de líderes en empleados** — Cuando hay discrepancias de escritura en el apellido (ej: `"Garcili"` vs `"Garciglia"`), el LIKE no hace match. Estos casos quedan como nombre limpio y deben resolverse manualmente o con fuzzy matching.

- **Presupuesto bloqueante** — La validación es **estricta**: si no hay fondos disponibles en el presupuesto mensual del centro de costo y categoría de gasto, el sistema no permite crear la OC/OCD. No es una advertencia, es un bloqueo.

- **Notificaciones a Buyers en requisición** — Se notifica a "todos los usuarios con rol buyer". Si hay muchos compradores, esto puede generar spam de notificaciones. `[REVISAR MANUALMENTE]` — ¿Hay filtro por empresa o centro de costo del comprador?

### Oportunidades de refactorización

- Los estados de `purchase_orders` y `odc_direct_purchase_orders` están como strings libres (varchar). Sería más robusto usando PHP Enums (disponible desde PHP 8.1).

- `DirectPurchaseOrderController` y `PurchaseOrderController` comparten lógica de recepciones y auto-cierre que podría extraerse a un trait o servicio compartido.

- La lógica de resolución del campo `leader` en `EmployeeController` tiene 5 métodos (`quitarPrefijo`, `limpiarLider`, `resolverLider`, `reordenarNombre`, `parsearNombre`) que podrían moverse a un `EmployeeNameParser` dedicado para facilitar pruebas unitarias.

---

## Cron Jobs del servidor

```bash
# Cada minuto — Scheduler de Laravel (auto-cierre OCs/OCDs, recordatorios, etc.)
* * * * *  cd /var/www/suppliersPortal && php artisan schedule:run >> /dev/null 2>&1

# Diario a las 2:30 AM — Sincronización lista negra SAT EFOS 69B
30 2 * * *  cd /var/www/suppliersPortal && /usr/bin/php artisan efos:sync \
             >> /var/www/suppliersPortal/storage/logs/efos.log 2>&1
```

Las tareas programadas dentro del scheduler de Laravel (auto-cierre por inactividad, avisos de vencimiento) se definen en `app/Console/Kernel.php` o `routes/console.php`.
