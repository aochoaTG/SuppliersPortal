# Entregas del Proveedor — Integración en Bandeja de Recepción

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hacer visibles las OCs en estado `DELIVERED_PENDING_RECEPTION` en la bandeja de recepciones, resaltadas como urgentes con su contador de días restantes, y mostrar la evidencia del proveedor en el formulario de recepción.

**Architecture:** Se modifica `ReceptionController` para incluir el nuevo status en los filtros existentes, se agrega un helper privado `getRemainingBusinessDaysBadge()` para reutilizar la lógica de badge, y se actualizan las tres vistas afectadas (`overview.blade.php`, `pending.blade.php`, `create.blade.php`).

**Tech Stack:** Laravel 11, PHP 8.3, Blade, DataTables (server-side), Bootstrap 5, jQuery, Carbon.

---

## Mapa de archivos

| Archivo | Acción | Responsabilidad |
|---|---|---|
| `app/Http/Controllers/ReceptionController.php` | Modificar | Ampliar status filters, helper badge, cargar evidencias |
| `resources/views/receptions/overview.blade.php` | Modificar | Agregar columna `Días Restantes`, `createdRow` urgente, CSS pulse |
| `resources/views/receptions/pending.blade.php` | Modificar | Agregar columna `Días Restantes`, resaltado de filas urgentes |
| `resources/views/receptions/create.blade.php` | Modificar | Card condicional con datos de entrega del proveedor |

---

## Task 1: Ampliar status filters + helper badge en `ReceptionController`

**Files:**
- Modify: `app/Http/Controllers/ReceptionController.php`

- [ ] **Step 1.1: Ampliar `$pendingStatuses` en `overview()`**

En `overview()` (línea 28), cambiar:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];
```
por:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED', 'DELIVERED_PENDING_RECEPTION'];
```

- [ ] **Step 1.2: Ampliar `$pendingStatuses` en `pending()`**

En `pending()` (línea 174), cambiar:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];
```
por:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED', 'DELIVERED_PENDING_RECEPTION'];
```

- [ ] **Step 1.3: Ampliar `$pendingStatuses` en `datatableRegularPending()`**

En `datatableRegularPending()` (línea 45), cambiar:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];
```
por:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED', 'DELIVERED_PENDING_RECEPTION'];
```

- [ ] **Step 1.4: Ampliar `$pendingStatuses` en `datatableDirectPending()`**

En `datatableDirectPending()` (línea 104), cambiar:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];
```
por:
```php
$pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED', 'DELIVERED_PENDING_RECEPTION'];
```

- [ ] **Step 1.5: Agregar helper privado `getRemainingBusinessDaysBadge()`**

Al final de la clase, antes del cierre `}`, agregar:

```php
private function getRemainingBusinessDaysBadge(mixed $order): string
{
    if ($order->status !== 'DELIVERED_PENDING_RECEPTION' || ! $order->reception_deadline_at) {
        return '<span class="text-muted">—</span>';
    }

    $days = (int) now()->diffInWeekdays($order->reception_deadline_at, false);

    [$class, $label] = match (true) {
        $days >= 3  => ['bg-success',          "{$days} día(s)"],
        $days === 2 => ['bg-warning text-dark', '2 días'],
        $days === 1 => ['bg-danger',            '1 día'],
        $days === 0 => ['bg-danger',            'Vence hoy'],
        default     => ['bg-danger',            'Vencida'],
    };

    return "<span class=\"badge {$class}\"><i class=\"ti ti-clock me-1\"></i>{$label}</span>";
}
```

- [ ] **Step 1.6: Verificar que el método existe**

```bash
php artisan tinker --execute="echo (new App\Http\Controllers\ReceptionController(app(App\Services\ReceptionService::class)))->pending();"
```

Esperado: no hay error de sintaxis (el método existe).

- [ ] **Step 1.7: Commit**

```bash
git add app/Http/Controllers/ReceptionController.php
git commit -m "feat: incluir DELIVERED_PENDING_RECEPTION en filtros de recepción + helper badge"
```

---

## Task 2: Columna `dias_restantes` + urgency en DataTables del controller

**Files:**
- Modify: `app/Http/Controllers/ReceptionController.php`

- [ ] **Step 2.1: Agregar columna y urgency en `datatableRegularPending()`**

Localizar el bloque `->rawColumns(...)` en `datatableRegularPending()` y reemplazar el método completo `DataTables::of($query)...->make(true)` agregando la columna y el atributo de fila. El bloque completo de DataTables debe quedar:

```php
return DataTables::of($query)
    ->addIndexColumn()
    ->addColumn('folio', fn($po) =>
        '<span class="fw-bold text-dark">' . $po->folio . '</span>'
    )
    ->addColumn('proveedor', fn($po) =>
        $po->supplier->company_name ?? '—'
    )
    ->addColumn('punto_entrega', function ($po) {
        if (! $po->receivingLocation) {
            return '<span class="text-danger small">Sin locación</span>';
        }
        return '<span class="badge bg-soft-info text-info me-1">'
            . $po->receivingLocation->code
            . '</span>'
            . e($po->receivingLocation->name);
    })
    ->addColumn('estado', function ($po) {
        $badge = '<span class="badge bg-' . $po->getStatusBadgeClass() . '">'
            . $po->getStatusLabel() . '</span>';
        if ($po->status === 'DELIVERED_PENDING_RECEPTION') {
            $badge .= ' <span class="badge bg-danger urgente-pulse">URGENTE</span>';
        }
        return $badge;
    })
    ->addColumn('emision', fn($po) =>
        $po->issued_at
            ? '<span class="text-muted small">' . $po->issued_at->format('d/m/Y') . '</span>'
            : '<span class="text-muted small">—</span>'
    )
    ->addColumn('dias_transcurridos', fn($po) =>
        $this->receptionService->getElapsedDaysBadge($po)
    )
    ->addColumn('dias_restantes', fn($po) =>
        $this->getRemainingBusinessDaysBadge($po)
    )
    ->addColumn('actions', function ($po) {
        $showUrl    = route('purchase-orders.show', $po->id);
        $receiveUrl = route('receptions.create', $po->id);

        $isUrgent = $po->status === 'DELIVERED_PENDING_RECEPTION';
        $btnClass = $isUrgent ? 'btn-danger' : 'btn-outline-success';
        $icon     = $isUrgent ? 'ti-clock' : 'ti-package-import';

        return '<a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                    <i class="ti ti-eye"></i>
                </a>
                <a href="' . $receiveUrl . '" class="btn btn-sm ' . $btnClass . ' ms-1" title="Registrar Recepción">
                    <i class="ti ' . $icon . '"></i>
                </a>';
    })
    ->setRowAttr([
        'data-urgent' => fn($po) => $po->status === 'DELIVERED_PENDING_RECEPTION' ? '1' : '0',
    ])
    ->rawColumns(['folio', 'punto_entrega', 'estado', 'emision', 'dias_transcurridos', 'dias_restantes', 'actions'])
    ->make(true);
```

- [ ] **Step 2.2: Agregar columna y urgency en `datatableDirectPending()`**

Mismo cambio para el DataTable de OC Directas. Reemplazar el bloque `DataTables::of($query)...->make(true)`:

```php
return DataTables::of($query)
    ->addIndexColumn()
    ->addColumn('folio', fn($ocd) =>
        '<span class="fw-bold text-dark">' . ($ocd->folio ?? 'DRAFT') . '</span>'
    )
    ->addColumn('proveedor', fn($ocd) =>
        $ocd->supplier->company_name ?? '—'
    )
    ->addColumn('punto_entrega', function ($ocd) {
        if (! $ocd->receivingLocation) {
            return '<span class="text-danger small">Sin locación</span>';
        }
        return '<span class="badge bg-soft-info text-info me-1">'
            . $ocd->receivingLocation->code
            . '</span>'
            . e($ocd->receivingLocation->name);
    })
    ->addColumn('solicitante', fn($ocd) =>
        '<span class="badge bg-soft-secondary text-secondary">'
        . e($ocd->creator->name)
        . '</span>'
    )
    ->addColumn('estado', function ($ocd) {
        $badge = '<span class="badge bg-' . $ocd->getStatusBadgeClass() . '">'
            . $ocd->getStatusLabel() . '</span>';
        if ($ocd->status === 'DELIVERED_PENDING_RECEPTION') {
            $badge .= ' <span class="badge bg-danger urgente-pulse">URGENTE</span>';
        }
        return $badge;
    })
    ->addColumn('emision', fn($ocd) =>
        $ocd->issued_at
            ? '<span class="text-muted small">' . $ocd->issued_at->format('d/m/Y') . '</span>'
            : '<span class="text-muted small">—</span>'
    )
    ->addColumn('dias_transcurridos', fn($ocd) =>
        $this->receptionService->getElapsedDaysBadge($ocd)
    )
    ->addColumn('dias_restantes', fn($ocd) =>
        $this->getRemainingBusinessDaysBadge($ocd)
    )
    ->addColumn('actions', function ($ocd) {
        $showUrl    = route('direct-purchase-orders.show', $ocd->id);
        $receiveUrl = route('receptions.create-direct', $ocd->id);

        $isUrgent = $ocd->status === 'DELIVERED_PENDING_RECEPTION';
        $btnClass = $isUrgent ? 'btn-danger' : 'btn-outline-success';
        $icon     = $isUrgent ? 'ti-clock' : 'ti-package-import';

        return '<a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                    <i class="ti ti-eye"></i>
                </a>
                <a href="' . $receiveUrl . '" class="btn btn-sm ' . $btnClass . ' ms-1" title="Registrar Recepción">
                    <i class="ti ' . $icon . '"></i>
                </a>';
    })
    ->setRowAttr([
        'data-urgent' => fn($ocd) => $ocd->status === 'DELIVERED_PENDING_RECEPTION' ? '1' : '0',
    ])
    ->rawColumns(['folio', 'punto_entrega', 'solicitante', 'estado', 'emision', 'dias_transcurridos', 'dias_restantes', 'actions'])
    ->make(true);
```

- [ ] **Step 2.3: Commit**

```bash
git add app/Http/Controllers/ReceptionController.php
git commit -m "feat: columna días_restantes y botón urgente en DataTables de recepciones"
```

---

## Task 3: Actualizar `overview.blade.php` — columna + createdRow + CSS pulse

**Files:**
- Modify: `resources/views/receptions/overview.blade.php`

- [ ] **Step 3.1: Agregar columna `Días Restantes` en ambas tablas**

En el `<thead>` de `#regular-pending-table` (línea 82–91), agregar el `<th>` antes del de Acciones:

```html
<thead class="table-light">
    <tr>
        <th>Folio</th>
        <th>Proveedor</th>
        <th>Punto de Entrega</th>
        <th>Estado</th>
        <th>Fecha Emisión</th>
        <th>Días Transcurridos</th>
        <th>Días Restantes</th>
        <th width="100px">Acciones</th>
    </tr>
</thead>
```

En el `<thead>` de `#direct-pending-table` (línea 101–110), agregar igual:

```html
<thead class="table-light">
    <tr>
        <th>Folio</th>
        <th>Proveedor</th>
        <th>Punto de Entrega</th>
        <th>Solicitante</th>
        <th>Estado</th>
        <th>Fecha Emisión</th>
        <th>Días Transcurridos</th>
        <th>Días Restantes</th>
        <th width="100px">Acciones</th>
    </tr>
</thead>
```

- [ ] **Step 3.2: Agregar `dias_restantes` en la config del DataTable regular + `createdRow`**

En la configuración del `regularTable` (dentro del `columns: [...]`), agregar la nueva columna y el callback `createdRow`:

```javascript
columns: [
    { data: 'folio',             name: 'folio',                         orderable: true },
    { data: 'proveedor',         name: 'supplier.company_name',         orderable: true },
    { data: 'punto_entrega',     name: 'receivingLocation.name',        orderable: true },
    { data: 'estado',            name: 'status',                        orderable: true },
    { data: 'emision',           name: 'issued_at',                     orderable: true },
    { data: 'dias_transcurridos',name: 'issued_at',                     orderable: false, searchable: false },
    { data: 'dias_restantes',    name: 'dias_restantes',                orderable: false, searchable: false },
    { data: 'actions',           name: 'actions',                       orderable: false, searchable: false }
],
createdRow: function (row, data, index) {
    if ($(row).attr('data-urgent') === '1') {
        $(row).addClass('table-danger');
    }
},
```

- [ ] **Step 3.3: Agregar `dias_restantes` en la config del DataTable directo + `createdRow`**

En la configuración del `directTable` (lazy init), mismo cambio:

```javascript
columns: [
    { data: 'folio',             name: 'folio',                     orderable: true },
    { data: 'proveedor',         name: 'supplier.company_name',     orderable: true },
    { data: 'punto_entrega',     name: 'receivingLocation.name',    orderable: true },
    { data: 'solicitante',       name: 'creator.name',              orderable: true },
    { data: 'estado',            name: 'status',                    orderable: true },
    { data: 'emision',           name: 'issued_at',                 orderable: true },
    { data: 'dias_transcurridos',name: 'issued_at',                 orderable: false, searchable: false },
    { data: 'dias_restantes',    name: 'dias_restantes',            orderable: false, searchable: false },
    { data: 'actions',           name: 'actions',                   orderable: false, searchable: false }
],
createdRow: function (row, data, index) {
    if ($(row).attr('data-urgent') === '1') {
        $(row).addClass('table-danger');
    }
},
```

- [ ] **Step 3.4: Agregar CSS `@keyframes pulse` para el badge URGENTE**

Agregar antes del `@push('scripts')` o dentro de un `@push('styles')`:

```html
@push('styles')
<style>
@keyframes pulse-urgente {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}
.urgente-pulse {
    animation: pulse-urgente 1.4s ease-in-out infinite;
}
</style>
@endpush
```

- [ ] **Step 3.5: Commit**

```bash
git add resources/views/receptions/overview.blade.php
git commit -m "feat: columna Días Restantes y resaltado urgente en overview de recepciones"
```

---

## Task 4: Actualizar `pending.blade.php` — resaltado urgente + columna días restantes

**Files:**
- Modify: `resources/views/receptions/pending.blade.php`

- [ ] **Step 4.1: Agregar CSS pulse**

Agregar antes del `@endsection` de content (o en un `@push('styles')`):

```html
@push('styles')
<style>
@keyframes pulse-urgente {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}
.urgente-pulse {
    animation: pulse-urgente 1.4s ease-in-out infinite;
}
</style>
@endpush
```

- [ ] **Step 4.2: Agregar columna `Días Restantes` en la tabla OC Estándar**

En el `<thead>` de la tabla de OC Estándar (dentro de `#tab-regular`), cambiar:

```html
<tr>
    <th>Folio</th>
    <th>Proveedor</th>
    <th>Punto de Entrega</th>
    <th>Total</th>
    <th>Estado</th>
    <th>Emitida</th>
    <th class="text-center">Acción</th>
</tr>
```

por:

```html
<tr>
    <th>Folio</th>
    <th>Proveedor</th>
    <th>Punto de Entrega</th>
    <th>Total</th>
    <th>Estado</th>
    <th>Emitida</th>
    <th class="text-center">Días Restantes</th>
    <th class="text-center">Acción</th>
</tr>
```

- [ ] **Step 4.3: Actualizar el `@foreach` de OC Estándar — fila urgente + columna + botón**

Reemplazar el `<tr>` completo del `@foreach($purchaseOrders as $po)`:

```html
@foreach($purchaseOrders as $po)
@php
    $isUrgent = $po->status === 'DELIVERED_PENDING_RECEPTION';
    $daysLeft = $isUrgent && $po->reception_deadline_at
        ? (int) now()->diffInWeekdays($po->reception_deadline_at, false)
        : null;
    $daysBadgeClass = match(true) {
        $daysLeft === null  => '',
        $daysLeft >= 3      => 'bg-success',
        $daysLeft === 2     => 'bg-warning text-dark',
        default             => 'bg-danger',
    };
    $daysLabel = match(true) {
        $daysLeft === null  => '—',
        $daysLeft > 0       => "{$daysLeft} día(s)",
        $daysLeft === 0     => 'Vence hoy',
        default             => 'Vencida',
    };
@endphp
<tr class="{{ $isUrgent ? 'table-danger' : '' }}">
    <td class="fw-bold">{{ $po->folio }}</td>
    <td>{{ $po->supplier->company_name ?? '—' }}</td>
    <td>
        @if($po->receivingLocation)
            <span class="badge bg-soft-info text-info">{{ $po->receivingLocation->code }}</span>
            {{ $po->receivingLocation->name }}
        @else
            <span class="text-danger">Sin locación</span>
        @endif
    </td>
    <td class="fw-bold text-primary">${{ number_format($po->total, 2) }}</td>
    <td>
        <span class="badge bg-{{ $po->getStatusBadgeClass() }}">{{ $po->getStatusLabel() }}</span>
        @if($isUrgent)
            <span class="badge bg-danger urgente-pulse ms-1">URGENTE</span>
        @endif
    </td>
    <td class="text-muted small">
        {{ $po->issued_at?->format('d/m/Y') ?? $po->created_at->format('d/m/Y') }}
    </td>
    <td class="text-center">
        @if($daysLeft !== null)
            <span class="badge {{ $daysBadgeClass }}">
                <i class="ti ti-clock me-1"></i>{{ $daysLabel }}
            </span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td class="text-center">
        <a href="{{ route('receptions.create', $po) }}"
           class="btn btn-sm {{ $isUrgent ? 'btn-danger' : 'btn-success' }}">
            <i class="ti {{ $isUrgent ? 'ti-clock' : 'ti-package-import' }} me-1"></i>Recibir
        </a>
    </td>
</tr>
@endforeach
```

- [ ] **Step 4.4: Aplicar el mismo patrón al `@foreach` de OC Directas**

Mismo cambio en el thead de OC Directas (agregar columna `Días Restantes`) y en el `@foreach($directOrders as $ocd)`:

```html
@foreach($directOrders as $ocd)
@php
    $isUrgent = $ocd->status === 'DELIVERED_PENDING_RECEPTION';
    $daysLeft = $isUrgent && $ocd->reception_deadline_at
        ? (int) now()->diffInWeekdays($ocd->reception_deadline_at, false)
        : null;
    $daysBadgeClass = match(true) {
        $daysLeft === null  => '',
        $daysLeft >= 3      => 'bg-success',
        $daysLeft === 2     => 'bg-warning text-dark',
        default             => 'bg-danger',
    };
    $daysLabel = match(true) {
        $daysLeft === null  => '—',
        $daysLeft > 0       => "{$daysLeft} día(s)",
        $daysLeft === 0     => 'Vence hoy',
        default             => 'Vencida',
    };
@endphp
<tr class="{{ $isUrgent ? 'table-danger' : '' }}">
    <td class="fw-bold">{{ $ocd->folio }}</td>
    <td>{{ $ocd->supplier->company_name ?? '—' }}</td>
    <td>
        @if($ocd->receivingLocation)
            <span class="badge bg-soft-info text-info">{{ $ocd->receivingLocation->code }}</span>
            {{ $ocd->receivingLocation->name }}
        @else
            <span class="text-danger">Sin locación</span>
        @endif
    </td>
    <td class="fw-bold text-primary">${{ number_format($ocd->total, 2) }}</td>
    <td>
        <span class="badge bg-{{ $ocd->getStatusBadgeClass() }}">{{ $ocd->getStatusLabel() }}</span>
        @if($isUrgent)
            <span class="badge bg-danger urgente-pulse ms-1">URGENTE</span>
        @endif
    </td>
    <td class="text-muted small">{{ $ocd->issued_at?->format('d/m/Y') ?? '—' }}</td>
    <td class="text-center">
        @if($daysLeft !== null)
            <span class="badge {{ $daysBadgeClass }}">
                <i class="ti ti-clock me-1"></i>{{ $daysLabel }}
            </span>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td class="text-center">
        <a href="{{ route('receptions.create-direct', $ocd) }}"
           class="btn btn-sm {{ $isUrgent ? 'btn-danger' : 'btn-success' }}">
            <i class="ti {{ $isUrgent ? 'ti-clock' : 'ti-package-import' }} me-1"></i>Recibir
        </a>
    </td>
</tr>
@endforeach
```

- [ ] **Step 4.5: Commit**

```bash
git add resources/views/receptions/pending.blade.php
git commit -m "feat: resaltado urgente y columna Días Restantes en bandeja pending de recepciones"
```

---

## Task 5: Cargar `deliveryEvidences` en `create()` y `createDirect()`

**Files:**
- Modify: `app/Http/Controllers/ReceptionController.php`

- [ ] **Step 5.1: Actualizar `create()` — cargar evidencia y URL, pasarlas a la vista**

En `create(PurchaseOrder $purchaseOrder)`, agregar el import al inicio del archivo si no existe:
```php
use Illuminate\Support\Facades\Storage;
```

Luego cambiar el `load`:

```php
$purchaseOrder->load(['items.requisitionItem', 'supplier', 'receivingLocation', 'deliveryEvidences']);
```

Y en el `return view(...)`, agregar `$deliveryEvidence` y `$deliveryEvidenceUrl`:

```php
$evidence = $purchaseOrder->deliveryEvidences->first();

return view('receptions.create', [
    'order'                => $purchaseOrder,
    'orderType'            => 'purchase_order',
    'storeRoute'           => route('receptions.store', $purchaseOrder),
    'repseWarning'         => $repseWarning,
    'receivingLocations'   => $receivingLocations,
    'deliveryEvidence'     => $evidence,
    'deliveryEvidenceUrl'  => $evidence ? Storage::disk('public')->url($evidence->file_path) : null,
]);
```

- [ ] **Step 5.2: Actualizar `createDirect()` — cargar evidencia y URL, pasarlas a la vista**

En `createDirect(DirectPurchaseOrder $directPurchaseOrder)`, cambiar el `load`:

```php
$directPurchaseOrder->load(['items.expenseCategory', 'supplier', 'receivingLocation', 'deliveryEvidences']);
```

Y en el `return view(...)`:

```php
$evidence = $directPurchaseOrder->deliveryEvidences->first();

return view('receptions.create', [
    'order'                => $directPurchaseOrder,
    'orderType'            => 'direct_purchase_order',
    'storeRoute'           => route('receptions.store-direct', $directPurchaseOrder),
    'repseWarning'         => $repseWarning,
    'receivingLocations'   => $receivingLocations,
    'deliveryEvidence'     => $evidence,
    'deliveryEvidenceUrl'  => $evidence ? Storage::disk('public')->url($evidence->file_path) : null,
]);
```

- [ ] **Step 5.3: Commit**

```bash
git add app/Http/Controllers/ReceptionController.php
git commit -m "feat: cargar evidencia de entrega del proveedor en formulario de recepción"
```

---

## Task 6: Card de entrega del proveedor en `create.blade.php`

**Files:**
- Modify: `resources/views/receptions/create.blade.php`

- [ ] **Step 6.1: Insertar card condicional antes del Card 2 (Datos generales de recepción)**

Insertar el siguiente bloque entre el cierre de **Card 1** (después del `</div>` que cierra la card de "Datos de la Orden", línea ~97) y la apertura de **Card 2** ("Datos Generales de Recepción"):

```html
{{-- ╔══════════════════════════════════════════════════════════════╗
     ║  CARD — Entrega del proveedor (solo si DELIVERED_PENDING)   ║
     ╚══════════════════════════════════════════════════════════════╝ --}}
@if($order->isDeliveredPendingReception() && $deliveryEvidence)
@php
    $daysLeft = $order->reception_deadline_at
        ? (int) now()->diffInWeekdays($order->reception_deadline_at, false)
        : null;
    $deadlineBadgeClass = match(true) {
        $daysLeft === null  => 'bg-secondary',
        $daysLeft >= 3      => 'bg-success',
        $daysLeft === 2     => 'bg-warning text-dark',
        default             => 'bg-danger',
    };
    $deadlineLabel = match(true) {
        $daysLeft === null  => '—',
        $daysLeft > 0       => "{$daysLeft} día(s) restante(s)",
        $daysLeft === 0     => 'Vence hoy',
        default             => 'Plazo vencido',
    };
    // $deliveryEvidenceUrl viene del controller (Storage::disk('public')->url(...))
    $evidenceUrl = $deliveryEvidenceUrl;
@endphp
<div class="col-12">
    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex align-items-center gap-2">
            <i class="ti ti-truck-delivery fs-18"></i>
            <h6 class="mb-0 text-white">Entrega registrada por el proveedor — pendiente de captura</h6>
            <span class="badge bg-white text-danger ms-auto {{ $daysLeft !== null && $daysLeft <= 2 ? 'urgente-pulse' : '' }}">
                <i class="ti ti-clock me-1"></i>{{ $deadlineLabel }}
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Fecha de entrega física</label>
                    <p class="mb-0 fw-semibold">
                        {{ $order->supplier_delivered_at?->format('d/m/Y H:i') ?? '—' }}
                    </p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Recibió en la estación</label>
                    <p class="mb-0">{{ $order->physical_receiver_name ?: '—' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Fecha límite de captura</label>
                    <p class="mb-0">
                        {{ $order->reception_deadline_at?->format('d/m/Y') ?? '—' }}
                        <span class="badge {{ $deadlineBadgeClass }} ms-1">{{ $deadlineLabel }}</span>
                    </p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Observaciones del proveedor</label>
                    <p class="mb-0 small text-muted">{{ $order->delivery_observations ?: 'Sin observaciones.' }}</p>
                </div>
            </div>

            {{-- Remisión digital --}}
            <div class="mt-3">
                <label class="form-label small fw-bold text-muted">Remisión digital cargada por el proveedor</label>
                @if($deliveryEvidence->isPdf())
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <a href="{{ $evidenceUrl }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-file-type-pdf me-1"></i>Descargar PDF
                        </a>
                    </div>
                    <iframe src="{{ $evidenceUrl }}"
                            class="w-100 border rounded"
                            style="height: 400px;"
                            title="Remisión del proveedor">
                    </iframe>
                @elseif($deliveryEvidence->isImage())
                    <div>
                        <img src="{{ $evidenceUrl }}"
                             alt="Remisión del proveedor"
                             class="img-fluid rounded border"
                             style="max-height: 400px; cursor: zoom-in;"
                             data-bs-toggle="modal"
                             data-bs-target="#modalRemision">
                    </div>
                    {{-- Modal de zoom --}}
                    <div class="modal fade" id="modalRemision" tabindex="-1">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title">Remisión del proveedor</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="{{ $evidenceUrl }}" alt="Remisión" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
```

- [ ] **Step 6.2: Agregar CSS pulse si no está ya en el layout**

Añadir al `@push('styles')` (o crear uno si no existe en esta vista):

```html
@push('styles')
<style>
@keyframes pulse-urgente {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}
.urgente-pulse {
    animation: pulse-urgente 1.4s ease-in-out infinite;
}
</style>
@endpush
```

- [ ] **Step 6.3: Verificar que la vista compila sin errores**

```bash
php artisan view:clear
php artisan route:list --name=receptions.create
```

Esperado: la ruta existe y no hay errores.

- [ ] **Step 6.4: Commit final**

```bash
git add resources/views/receptions/create.blade.php
git commit -m "feat: card de entrega del proveedor en formulario de recepción con preview de remisión"
```

---

## Verificación manual end-to-end

Después de implementar todos los tasks:

1. En el portal del proveedor, registrar una entrega contra una OC (el status pasa a `DELIVERED_PENDING_RECEPTION`)
2. Ir a `/receptions/overview` — verificar que la OC aparece en el DataTable con fila roja, badge URGENTE parpadeante y columna `Días Restantes`
3. Ir a `/receptions/pending` — verificar lo mismo en la vista de bandeja
4. Hacer click en "Recibir" (botón rojo con reloj) — verificar que aparece la card con la info del proveedor y la remisión
5. Completar el formulario de recepción normalmente — verificar que el status cambia a `RECEIVED` o `PARTIALLY_RECEIVED`
