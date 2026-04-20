# EFOS 69-B Sync Button — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Agregar un botón "Sincronizar SAT" en la vista EFOS 69-B que despacha un Job en background y muestra progreso real (barra + contador) en un modal SweetAlert2.

**Architecture:** Un `SyncEfosJob` contiene toda la lógica de sincronización (descarga CSV, cuenta líneas, upsert en lotes) y escribe progreso en caché. El controlador expone dos endpoints: POST para lanzar el job y GET para consultar estado. El frontend hace polling cada 2s y actualiza el modal con barra de progreso Bootstrap 5.

**Tech Stack:** Laravel 11, PHP 8, queue driver `database`, Cache facade, SweetAlert2 (ya en layout), Bootstrap 5, DataTables.

---

## File Map

| Archivo | Acción |
|---------|--------|
| `app/Jobs/SyncEfosJob.php` | **Crear** — lógica de sync con actualizaciones de caché |
| `app/Http/Controllers/SatEfos69bController.php` | **Modificar** — agregar `sync()` y `syncStatus()` |
| `routes/web.php` | **Modificar** — agregar rutas POST y GET dentro del grupo `sat-efos-69b` |
| `resources/views/sat_efos_69b/index.blade.php` | **Modificar** — botón + JS de polling |
| `tests/Feature/EfosSyncTest.php` | **Crear** — tests de los endpoints y del job |

---

## Task 1: Crear SyncEfosJob

**Files:**
- Create: `app/Jobs/SyncEfosJob.php`

- [ ] **Step 1: Crear el archivo del Job**

```php
<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;

class SyncEfosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(public string $jobId) {}

    public function handle(): void
    {
        $key = "efos_sync_{$this->jobId}";
        $this->patch($key, ['status' => 'running', 'started_at' => now()->toDateTimeString()]);

        try {
            $url     = config('efos.csv_url', env('EFOS_CSV_URL'));
            $dirAbs  = storage_path('app/efos');
            if (!is_dir($dirAbs)) mkdir($dirAbs, 0775, true);
            $fullPath = $dirAbs . '/Listado_Completo_69-B.csv';

            $response = Http::timeout(120)->retry(3, 2000)->get($url);
            if (!$response->ok()) {
                throw new \RuntimeException("No se pudo descargar el CSV (HTTP {$response->status()})");
            }
            file_put_contents($fullPath, $response->body());

            $total = $this->countDataLines($fullPath);
            $this->patch($key, ['total' => $total, 'message' => 'Procesando registros...']);

            $processed   = 0;
            $rows        = [];
            $headerFound = false;
            $chunkSize   = 1000;

            $lines = LazyCollection::make(function () use ($fullPath) {
                $handle = fopen($fullPath, 'r');
                if (!$handle) { yield from []; return; }
                while (($line = fgets($handle)) !== false) yield $line;
                fclose($handle);
            });

            foreach ($lines as $rawLine) {
                $line = mb_convert_encoding($rawLine, 'UTF-8', 'ISO-8859-1');
                $row  = str_getcsv($line);
                if (!$row || count($row) < 2) continue;
                if (!$headerFound) {
                    if (stripos($row[1] ?? '', 'RFC') !== false) $headerFound = true;
                    continue;
                }
                $rows[] = $row;
                if (count($rows) >= $chunkSize) {
                    $this->upsertChunk($rows);
                    $processed += count($rows);
                    $rows = [];
                    $this->patch($key, [
                        'processed' => $processed,
                        'message'   => "Procesando... {$processed} de {$total} registros",
                    ]);
                }
            }
            if ($rows) {
                $this->upsertChunk($rows);
                $processed += count($rows);
            }

            $this->patch($key, [
                'status'      => 'completed',
                'processed'   => $processed,
                'message'     => 'Sincronización completada',
                'finished_at' => now()->toDateTimeString(),
            ]);
            Log::info("SyncEfosJob completado: {$processed} registros procesados.");
        } catch (\Throwable $e) {
            $this->patch($key, [
                'status'      => 'failed',
                'message'     => $e->getMessage(),
                'finished_at' => now()->toDateTimeString(),
            ]);
            Log::error("SyncEfosJob falló: " . $e->getMessage());
        }
    }

    private function patch(string $key, array $data): void
    {
        Cache::put($key, array_merge(Cache::get($key, []), $data), now()->addHours(2));
    }

    private function countDataLines(string $fullPath): int
    {
        $count       = 0;
        $headerFound = false;
        $handle      = fopen($fullPath, 'r');
        if (!$handle) return 0;
        while (($line = fgets($handle)) !== false) {
            $row = str_getcsv(mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1'));
            if (!$row || count($row) < 2) continue;
            if (!$headerFound) {
                if (stripos($row[1] ?? '', 'RFC') !== false) $headerFound = true;
                continue;
            }
            $count++;
        }
        fclose($handle);
        return $count;
    }

    private function parseDate(?string $value): ?Carbon
    {
        $v = trim((string) $value);
        if ($v === '') return null;
        try { return Carbon::createFromFormat('d/m/Y', $v)->startOfDay(); }
        catch (\Throwable) { return null; }
    }

    private function upsertChunk(array $rows): void
    {
        $now = Carbon::now();
        foreach ($rows as $row) {
            if (count($row) < 8) continue;
            DB::statement("
                MERGE sat_efos_69b AS target
                USING (VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?))
                    AS source (number, rfc, company_name, situation,
                               sat_presumption_notice_date, sat_presumed_publication_date,
                               dof_presumption_notice_date, dof_presumed_pub_date,
                               sat_definitive_publication_date, dof_definitive_publication_date,
                               updated_at, created_at)
                ON (target.rfc = source.rfc)
                WHEN MATCHED THEN UPDATE SET
                    number = source.number,
                    company_name = source.company_name,
                    situation = source.situation,
                    sat_presumption_notice_date = source.sat_presumption_notice_date,
                    sat_presumed_publication_date = source.sat_presumed_publication_date,
                    dof_presumption_notice_date = source.dof_presumption_notice_date,
                    dof_presumed_pub_date = source.dof_presumed_pub_date,
                    sat_definitive_publication_date = source.sat_definitive_publication_date,
                    dof_definitive_publication_date = source.dof_definitive_publication_date,
                    updated_at = source.updated_at
                WHEN NOT MATCHED THEN INSERT (
                    number, rfc, company_name, situation,
                    sat_presumption_notice_date, sat_presumed_publication_date,
                    dof_presumption_notice_date, dof_presumed_pub_date,
                    sat_definitive_publication_date, dof_definitive_publication_date,
                    updated_at, created_at)
                VALUES (
                    source.number, source.rfc, source.company_name, source.situation,
                    source.sat_presumption_notice_date, source.sat_presumed_publication_date,
                    source.dof_presumption_notice_date, source.dof_presumed_pub_date,
                    source.sat_definitive_publication_date, source.dof_definitive_publication_date,
                    source.updated_at, source.created_at);
            ", [
                (int)($row[0] ?? 0),
                mb_substr(trim((string)($row[1] ?? '')), 0, 13),
                mb_substr(trim((string)($row[2] ?? '')), 0, 255),
                mb_substr(trim((string)($row[3] ?? '')), 0, 255),
                mb_substr(trim((string)($row[4] ?? '')), 0, 100),
                optional($this->parseDate($row[5] ?? null))->toDateString(),
                mb_substr(trim((string)($row[6] ?? '')), 0, 100),
                optional($this->parseDate($row[7] ?? null))->toDateString(),
                optional($this->parseDate($row[13] ?? null))->toDateString(),
                optional($this->parseDate($row[15] ?? null))->toDateString(),
                $now,
                $now,
            ]);
        }
    }
}
```

- [ ] **Step 2: Verificar que el archivo existe y no tiene errores de sintaxis**

```bash
php artisan about 2>&1 | head -5
php -l app/Jobs/SyncEfosJob.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add app/Jobs/SyncEfosJob.php
git commit -m "feat: add SyncEfosJob with cache progress tracking"
```

---

## Task 2: Agregar métodos al controlador

**Files:**
- Modify: `app/Http/Controllers/SatEfos69bController.php`

- [ ] **Step 1: Agregar imports al controlador**

En `app/Http/Controllers/SatEfos69bController.php`, reemplazar el bloque de `use` existente:

```php
use App\Jobs\SyncEfosJob;
use App\Models\SatEfos69b;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
```

- [ ] **Step 2: Agregar método `sync()`**

Dentro de la clase, después del método `data()`:

```php
public function sync(): JsonResponse
{
    $currentJobId = Cache::get('efos_sync_current');
    if ($currentJobId) {
        $state = Cache::get("efos_sync_{$currentJobId}", []);
        if (($state['status'] ?? '') === 'running') {
            return response()->json(['message' => 'Ya hay una sincronización en curso'], 409);
        }
    }

    $jobId = uniqid('efos_', true);
    Cache::put('efos_sync_current', $jobId, now()->addHours(2));
    Cache::put("efos_sync_{$jobId}", [
        'status'      => 'pending',
        'processed'   => 0,
        'total'       => 0,
        'message'     => 'Iniciando sincronización...',
        'started_at'  => now()->toDateTimeString(),
        'finished_at' => null,
    ], now()->addHours(2));

    SyncEfosJob::dispatch($jobId);

    return response()->json(['job_id' => $jobId]);
}
```

- [ ] **Step 3: Agregar método `syncStatus()`**

Inmediatamente después de `sync()`:

```php
public function syncStatus(string $jobId): JsonResponse
{
    $state = Cache::get("efos_sync_{$jobId}");
    if (!$state) {
        return response()->json(['message' => 'Job no encontrado'], 404);
    }
    return response()->json($state);
}
```

- [ ] **Step 4: Verificar sintaxis**

```bash
php -l app/Http/Controllers/SatEfos69bController.php
```
Expected: `No syntax errors detected`

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/SatEfos69bController.php
git commit -m "feat: add sync and syncStatus methods to SatEfos69bController"
```

---

## Task 3: Agregar rutas

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Agregar las dos rutas al grupo existente**

En `routes/web.php`, localizar el bloque (líneas ~191-194):

```php
    Route::prefix('sat-efos-69b')->name('sat_efos_69b.')->group(function () {
        Route::get('/', [SatEfos69bController::class, 'index'])->name('index');
        Route::get('/data', [SatEfos69bController::class, 'data'])->name('data');
    });
```

Reemplazarlo por:

```php
    Route::prefix('sat-efos-69b')->name('sat_efos_69b.')->group(function () {
        Route::get('/', [SatEfos69bController::class, 'index'])->name('index');
        Route::get('/data', [SatEfos69bController::class, 'data'])->name('data');
        Route::post('/sync', [SatEfos69bController::class, 'sync'])->name('sync');
        Route::get('/sync/{jobId}', [SatEfos69bController::class, 'syncStatus'])->name('sync.status');
    });
```

- [ ] **Step 2: Verificar que las rutas están registradas**

```bash
php artisan route:list --name=sat_efos_69b
```
Expected: aparecen 4 rutas — `index`, `data`, `sync` (POST), `sync.status` (GET)

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat: add sync routes for EFOS 69-B"
```

---

## Task 4: Actualizar la vista con botón y modal

**Files:**
- Modify: `resources/views/sat_efos_69b/index.blade.php`

- [ ] **Step 1: Reemplazar el contenido completo de la vista**

```blade
@extends('layouts.zircos')

@section('title', 'Listado EFOS 69-B')
@section('page.title', 'Listado EFOS 69-B')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Listado EFOS 69-B</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">RFC listados en EFOS 69-B</h5>
        <button id="btn-sync-efos" class="btn btn-primary btn-sm">
            <i class="ti ti-refresh me-1"></i> Sincronizar SAT
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table-bordered table-hover w-100 table" id="efosTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>RFC</th>
                        <th>Razón Social</th>
                        <th>Situación</th>
                        <th>Presunción SAT</th>
                        <th>Presunción DOF</th>
                        <th>Definitivo SAT</th>
                        <th>Definitivo DOF</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function fmt(d) {
        if (!d) return '—';
        const dt = new Date(d);
        if (isNaN(dt)) return d;
        const dd = String(dt.getDate()).padStart(2,'0');
        const mm = String(dt.getMonth()+1).padStart(2,'0');
        return `${dd}/${mm}/${dt.getFullYear()}`;
    }

    function badge(sit) {
        const s = (sit || '').toString().toUpperCase();
        let cls = 'secondary';
        if (s.includes('DEFINITIVO'))         cls = 'danger';
        else if (s.includes('PRESUNTO'))      cls = 'warning';
        else if (s.includes('SENTENCIA'))     cls = 'primary';
        return `<span class="badge bg-${cls}">${sit ?? ''}</span>`;
    }

    window.efosTable = new DataTable('#efosTable', {
        ajax:        { url: "{{ route('sat_efos_69b.data') }}", dataSrc: 'data' },
        paging:      true,
        searching:   true,
        ordering:    true,
        responsive:  false,
        lengthMenu:  [10, 25, 50, 100],
        pageLength:  100,
        order:       [[1, 'asc']],
        buttons: [{
            extend:    'excel',
            text:      '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
            className: 'btn btn-success btn-sm',
            title:     'EFOS_69B_' + new Date().toISOString().split('T')[0]
        }],
        dom: '<"top"Bf>rt<"bottom"lip>',
        columns: [
            { data: 'number',       name: 'number' },
            { data: 'rfc',          name: 'rfc',          render: d => `<code>${d ?? ''}</code>` },
            { data: 'company_name', name: 'company_name' },
            { data: 'situation',    name: 'situation',    render: badge },
            { data: 'sat_presumption_notice_date', name: 'sat_presumption_notice_date', render: d => d || '—' },
            { data: 'dof_presumption_notice_date', name: 'dof_presumption_notice_date', render: d => d || '—' },
            { data: 'sat_definitive_publication_date', name: 'sat_definitive_publication_date', render: fmt },
            { data: 'dof_definitive_publication_date', name: 'dof_definitive_publication_date', render: fmt },
        ],
        language:   { url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}" },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        }
    });

    // ── Sync button ──────────────────────────────────────────────────────────
    const syncUrl       = "{{ route('sat_efos_69b.sync') }}";
    const syncStatusUrl = "{{ route('sat_efos_69b.sync.status', ':jobId') }}";
    const csrfToken     = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('btn-sync-efos').addEventListener('click', function () {
        Swal.fire({
            title:            'Sincronización SAT EFOS',
            html:             buildModalHtml('Iniciando descarga del CSV...', 0, 0),
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen:          () => startSync(),
        });
    });

    function buildModalHtml(msg, processed, total) {
        const pct = total > 0 ? Math.round((processed / total) * 100) : 0;
        const counter = total > 0
            ? `${processed.toLocaleString('es-MX')} / ${total.toLocaleString('es-MX')} registros`
            : '';
        return `
            <p class="mb-2" id="swal-msg">${msg}</p>
            <div class="progress mb-2" style="height:20px">
                <div id="swal-bar"
                     class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     role="progressbar"
                     style="width:${pct}%"
                     aria-valuenow="${pct}"
                     aria-valuemax="100">${pct > 0 ? pct + '%' : ''}</div>
            </div>
            <small class="text-muted" id="swal-counter">${counter}</small>`;
    }

    function startSync() {
        fetch(syncUrl, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(res => {
            if (res.status === 409) {
                Swal.fire('En curso', 'Ya hay una sincronización activa. Intenta más tarde.', 'warning');
                return null;
            }
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!data?.job_id) return;
            pollStatus(data.job_id);
        })
        .catch(() => Swal.fire('Error', 'No se pudo iniciar la sincronización.', 'error'));
    }

    function pollStatus(jobId) {
        let networkErrors = 0;
        const url = syncStatusUrl.replace(':jobId', jobId);

        const interval = setInterval(() => {
            fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                networkErrors = 0;
                updateModal(data);

                if (data.status === 'completed') {
                    clearInterval(interval);
                    Swal.fire({
                        icon:  'success',
                        title: 'Sincronización completada',
                        text:  `${(data.processed || 0).toLocaleString('es-MX')} registros procesados.`,
                    }).then(() => window.efosTable.ajax.reload());
                } else if (data.status === 'failed') {
                    clearInterval(interval);
                    Swal.fire('Error en sincronización', data.message || 'El proceso falló.', 'error');
                }
            })
            .catch(() => {
                networkErrors++;
                if (networkErrors >= 3) {
                    clearInterval(interval);
                    Swal.fire('Error de red', 'No se pudo obtener el estado del proceso.', 'error');
                }
            });
        }, 2000);
    }

    function updateModal(data) {
        const msg     = document.getElementById('swal-msg');
        const bar     = document.getElementById('swal-bar');
        const counter = document.getElementById('swal-counter');
        if (!msg || !bar) return;

        const processed = data.processed || 0;
        const total     = data.total     || 0;
        const pct       = total > 0 ? Math.round((processed / total) * 100) : 0;

        bar.style.width = pct + '%';
        bar.setAttribute('aria-valuenow', pct);
        bar.textContent = pct > 0 ? pct + '%' : '';
        msg.textContent = data.message || 'Procesando...';
        if (counter && total > 0) {
            counter.textContent = `${processed.toLocaleString('es-MX')} / ${total.toLocaleString('es-MX')} registros`;
        }
    }
});
</script>
@endpush
```

- [ ] **Step 2: Verificar que la vista carga sin errores PHP**

```bash
php artisan view:clear
php artisan route:cache
```
Expected: sin errores

- [ ] **Step 3: Commit**

```bash
git add resources/views/sat_efos_69b/index.blade.php
git commit -m "feat: add sync button and progress modal to EFOS 69-B view"
```

---

## Task 5: Tests de feature

**Files:**
- Create: `tests/Feature/EfosSyncTest.php`

- [ ] **Step 1: Escribir los tests**

```php
<?php

namespace Tests\Feature;

use App\Jobs\SyncEfosJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EfosSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    public function test_sync_dispatches_job_and_returns_job_id(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/sat-efos-69b/sync');

        $response->assertOk()
                 ->assertJsonStructure(['job_id']);

        Queue::assertPushed(SyncEfosJob::class);
    }

    public function test_sync_returns_409_when_job_already_running(): void
    {
        Queue::fake();

        $jobId = uniqid('efos_', true);
        Cache::put('efos_sync_current', $jobId, now()->addHours(2));
        Cache::put("efos_sync_{$jobId}", ['status' => 'running'], now()->addHours(2));

        $response = $this->actingAs($this->admin)
            ->postJson('/sat-efos-69b/sync');

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Ya hay una sincronización en curso']);

        Queue::assertNothingPushed();
    }

    public function test_sync_status_returns_state_from_cache(): void
    {
        $jobId = uniqid('efos_', true);
        Cache::put("efos_sync_{$jobId}", [
            'status'    => 'running',
            'processed' => 5000,
            'total'     => 80000,
            'message'   => 'Procesando...',
        ], now()->addHours(2));

        $response = $this->actingAs($this->admin)
            ->getJson("/sat-efos-69b/sync/{$jobId}");

        $response->assertOk()
                 ->assertJson([
                     'status'    => 'running',
                     'processed' => 5000,
                     'total'     => 80000,
                 ]);
    }

    public function test_sync_status_returns_404_for_unknown_job(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/sat-efos-69b/sync/nonexistent_job_id');

        $response->assertNotFound();
    }

    public function test_sync_sets_completed_status_on_success(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/sat-efos-69b/sync');

        $jobId = $response->json('job_id');

        // Simular que el job terminó
        Cache::put("efos_sync_{$jobId}", [
            'status'      => 'completed',
            'processed'   => 87000,
            'total'       => 87000,
            'message'     => 'Sincronización completada',
            'finished_at' => now()->toDateTimeString(),
        ], now()->addHours(2));

        $status = $this->actingAs($this->admin)
            ->getJson("/sat-efos-69b/sync/{$jobId}");

        $status->assertOk()
               ->assertJson(['status' => 'completed', 'processed' => 87000]);
    }
}
```

- [ ] **Step 2: Ejecutar los tests y verificar que pasan**

```bash
php artisan test tests/Feature/EfosSyncTest.php --verbose
```
Expected:
```
PASS  Tests\Feature\EfosSyncTest
✓ sync dispatches job and returns job id
✓ sync returns 409 when job already running
✓ sync status returns state from cache
✓ sync status returns 404 for unknown job
✓ sync sets completed status on success
```

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/EfosSyncTest.php
git commit -m "test: add feature tests for EFOS sync endpoints"
```

---

## Notas de despliegue

> El `QUEUE_CONNECTION=database` significa que el job se encola en la tabla `jobs` de la BD. Para que se ejecute es necesario tener un worker corriendo:
> ```bash
> php artisan queue:work --timeout=1800
> ```
> En producción esto debería estar gestionado por Supervisor. Sin un worker activo, el botón despacha el job pero este no se procesará.

---

## Verificación final

- [ ] `php artisan route:list --name=sat_efos_69b` muestra las 4 rutas
- [ ] `php artisan test tests/Feature/EfosSyncTest.php` todos pasan
- [ ] El botón aparece en el `card-header` de la vista EFOS
- [ ] Al hacer click, el modal SweetAlert2 se abre con barra de progreso
- [ ] Con un worker activo, el progreso se actualiza cada 2s
- [ ] Al completar, aparece Swal success y el DataTable recarga
