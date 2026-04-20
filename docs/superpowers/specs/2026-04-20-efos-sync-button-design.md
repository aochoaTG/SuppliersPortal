# EFOS 69-B Sync Button — Design Spec

**Date:** 2026-04-20  
**Feature:** Botón de sincronización manual del SAT EFOS con progreso en tiempo real

---

## Overview

Agregar un botón "Sincronizar SAT" en `resources/views/sat_efos_69b/index.blade.php` que ejecute el comando `efos:sync` a través de un Job de Laravel en background, mostrando progreso real (barra + contador de registros) en un modal SweetAlert2.

---

## Architecture

```
[Botón "Sincronizar SAT"]
        │ POST /sat-efos-69b/sync
        ▼
[SatEfos69bController::sync()]
  → Verifica no haya job corriendo (409 si hay uno activo)
  → Genera jobId único (uniqid)
  → Inicializa caché: { status: "pending", processed: 0, total: 0 }
  → dispatch(SyncEfosJob($jobId))
  → Retorna { job_id }
        │
        ▼
[SyncEfosJob]
  1. Descarga CSV del SAT
  2. Pase rápido para contar total de líneas → actualiza caché
  3. Procesa por lotes de 1000 → actualiza caché tras cada lote
  4. Al terminar: status = "completed" | "failed"

[Modal SweetAlert2 — polling cada 2s]
  GET /sat-efos-69b/sync/{jobId}
  → Actualiza barra de progreso (%) + texto "12,000 / 87,543 registros"
  → Al completar: Swal.fire success + recarga DataTable
  → Al fallar: Swal.fire error con mensaje
```

---

## Cache Structure

**Key:** `efos_sync_{jobId}`  
**TTL:** 2 horas

```json
{
  "status": "pending|running|completed|failed",
  "processed": 12000,
  "total": 87543,
  "message": "Procesando lote 12...",
  "started_at": "2026-04-20 10:30:00",
  "finished_at": null
}
```

---

## New Files

| Archivo | Descripción |
|---------|-------------|
| `app/Jobs/SyncEfosJob.php` | Job con lógica de sincronización y escritura de progreso en caché |

---

## Modified Files

| Archivo | Cambio |
|---------|--------|
| `app/Http/Controllers/SatEfos69bController.php` | Agregar métodos `sync()` y `syncStatus()` |
| `routes/web.php` | Agregar rutas POST y GET para sync |
| `resources/views/sat_efos_69b/index.blade.php` | Agregar botón + JS de polling + modal Swal |

---

## Routes

```
POST /sat-efos-69b/sync              → SatEfos69bController@sync        (name: sat_efos_69b.sync)
GET  /sat-efos-69b/sync/{jobId}      → SatEfos69bController@syncStatus  (name: sat_efos_69b.sync.status)
```

---

## Controller Methods

### `sync()` — POST
1. Lee caché `efos_sync_current` (apuntador al jobId activo)
2. Si existe y status es `running` → retorna 409 `{ message: "Ya hay una sincronización en curso" }`
3. Genera `$jobId = uniqid('efos_', true)`
4. Guarda en caché `efos_sync_current = $jobId` (TTL 2h)
5. Inicializa caché `efos_sync_{$jobId}` con status `pending`
6. `SyncEfosJob::dispatch($jobId)`
7. Retorna 200 `{ job_id: $jobId }`

### `syncStatus($jobId)` — GET
1. Lee caché `efos_sync_{$jobId}`
2. Si no existe → 404
3. Retorna JSON con el objeto completo

---

## SyncEfosJob

- Extiende `Illuminate\Contracts\Queue\ShouldQueue`
- `$timeout = 1800` (30 min)
- `$tries = 1`
- Lógica extraída de `SyncEfos69b::handle()` más:
  - Primer pase rápido para contar líneas válidas → `total` en caché
  - Actualiza caché `processed += count($chunk)` tras cada `upsertChunk()`
  - En bloque `catch` → `status = failed`, `message = $e->getMessage()`
  - Al final → `status = completed`, `finished_at = now()`

---

## Frontend

### Botón
En `card-header`, alineado a la derecha junto al título existente:
```html
<button id="btn-sync-efos" class="btn btn-primary btn-sm">
  <i class="ti ti-refresh me-1"></i> Sincronizar SAT
</button>
```

### Modal SweetAlert2
- HTML personalizado con `<div class="progress">` de Bootstrap 5
- Muestra: barra de progreso animada + texto "X / Y registros procesados"
- `allowOutsideClick: false`, `showConfirmButton: false` mientras corre
- Al completar: transición a Swal success con botón "Aceptar" que recarga el DataTable
- Al fallar: Swal error con el mensaje del job
- Si 409: Swal warning "Ya hay una sincronización en curso"

### Polling
- `setInterval` cada 2000ms
- Se detiene cuando `status === 'completed'` o `status === 'failed'`
- Manejo de errores de red (si el GET falla 3 veces consecutivas → abortar con error)

---

## Constraints

- El comando CLI `php artisan efos:sync` **no se modifica** — sigue funcionando de forma independiente
- Solo una sincronización simultánea permitida
- La queue connection usada es la configurada en `.env` (`QUEUE_CONNECTION`)
- SweetAlert2 ya está disponible en el layout `layouts.zircos`
