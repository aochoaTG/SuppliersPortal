# Exchange Rate (USD/MXN) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Consumir la API de exchangerate-api.com cada hora en horario laboral, persistir el tipo de cambio USD/MXN en BD y mostrarlo en el navbar con el formato `USD $17.43 · act. 10:00`.

**Architecture:** Un Artisan Command hace el fetch HTTP y hace upsert en la tabla `exchange_rates` (un registro por par de divisas). El scheduler lo ejecuta cada hora, L-V, 8-18h. `AppServiceProvider` comparte el rate con todas las vistas vía `View::share`, y el navbar lo renderiza condicionalmente.

**Tech Stack:** Laravel 12, PHPUnit 11, `Illuminate\Support\Facades\Http` (ya incluido en Laravel), SQL Server (producción) / SQLite (tests).

---

## File Map

| Acción | Archivo |
|---|---|
| Crear | `database/migrations/2026_04_21_120000_create_exchange_rates_table.php` |
| Crear | `app/Models/ExchangeRate.php` |
| Crear | `app/Console/Commands/SyncExchangeRate.php` |
| Crear | `tests/Feature/ExchangeRateSyncTest.php` |
| Modificar | `config/services.php` |
| Modificar | `routes/console.php` |
| Modificar | `app/Providers/AppServiceProvider.php` |
| Modificar | `resources/views/layouts/partials/navbar.blade.php` |

---

## Task 1: Migración y Modelo `ExchangeRate`

**Files:**
- Create: `database/migrations/2026_04_21_120000_create_exchange_rates_table.php`
- Create: `app/Models/ExchangeRate.php`
- Create: `tests/Feature/ExchangeRateSyncTest.php`

- [ ] **Step 1: Crear el archivo de test**

Crear `tests/Feature/ExchangeRateSyncTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeRateSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_returns_null_when_no_record_exists(): void
    {
        $result = ExchangeRate::current('USD', 'MXN');

        $this->assertNull($result);
    }

    public function test_current_returns_record_when_it_exists(): void
    {
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to'   => 'MXN',
            'rate'          => 17.4320,
            'fetched_at'    => now(),
        ]);

        $result = ExchangeRate::current('USD', 'MXN');

        $this->assertNotNull($result);
        $this->assertEquals('17.4320', $result->rate);
    }

    public function test_current_returns_null_for_different_pair(): void
    {
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to'   => 'MXN',
            'rate'          => 17.4320,
            'fetched_at'    => now(),
        ]);

        $result = ExchangeRate::current('EUR', 'MXN');

        $this->assertNull($result);
    }
}
```

- [ ] **Step 2: Ejecutar los tests y verificar que fallan**

```bash
php artisan test tests/Feature/ExchangeRateSyncTest.php
```

Resultado esperado: **ERROR** — `App\Models\ExchangeRate` not found.

- [ ] **Step 3: Crear la migración**

Crear `database/migrations/2026_04_21_120000_create_exchange_rates_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('currency_from', 3);
            $table->char('currency_to', 3);
            $table->decimal('rate', 10, 4);
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['currency_from', 'currency_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
```

- [ ] **Step 4: Crear el modelo**

Crear `app/Models/ExchangeRate.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
        'rate'       => 'decimal:4',
    ];

    public static function current(string $from, string $to): ?self
    {
        return static::where('currency_from', $from)
            ->where('currency_to', $to)
            ->first();
    }
}
```

- [ ] **Step 5: Ejecutar los tests y verificar que pasan**

```bash
php artisan test tests/Feature/ExchangeRateSyncTest.php
```

Resultado esperado: **3 tests PASSED**.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_04_21_120000_create_exchange_rates_table.php app/Models/ExchangeRate.php tests/Feature/ExchangeRateSyncTest.php
git commit -m "feat: add exchange_rates table and ExchangeRate model"
```

---

## Task 2: Configuración de la API key

**Files:**
- Modify: `config/services.php`
- Modify: `.env` (instrucción manual — no se commitea)

- [ ] **Step 1: Agregar la entrada en `config/services.php`**

Abrir `config/services.php` y agregar antes del cierre `];`:

```php
    'exchangerate' => [
        'key' => env('EXCHANGERATE_API_KEY'),
    ],
```

El archivo completo debe quedar así:

```php
<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'exchangerate' => [
        'key' => env('EXCHANGERATE_API_KEY'),
    ],

];
```

- [ ] **Step 2: Agregar la API key al `.env`**

Abrir `.env` y agregar (sustituir con la clave real):

```
EXCHANGERATE_API_KEY=tu_api_key_aqui
```

> **Nota:** No commitear `.env`. Verificar que `.gitignore` ya lo excluye (lo hace por defecto en Laravel).

- [ ] **Step 3: Commit**

```bash
git add config/services.php
git commit -m "feat: add exchangerate-api service config"
```

---

## Task 3: Artisan Command `exchange-rates:sync`

**Files:**
- Create: `app/Console/Commands/SyncExchangeRate.php`
- Modify: `tests/Feature/ExchangeRateSyncTest.php`

- [ ] **Step 1: Agregar tests del comando al archivo existente**

Abrir `tests/Feature/ExchangeRateSyncTest.php` y agregar estos imports y métodos al final de la clase (antes del último `}`):

Imports adicionales a agregar al inicio del archivo:

```php
use Illuminate\Support\Facades\Http;
```

Métodos a agregar dentro de la clase:

```php
    public function test_sync_command_creates_exchange_rate_record(): void
    {
        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([
                'result'          => 'success',
                'conversion_rate' => 17.4320,
            ], 200),
        ]);

        $this->artisan('exchange-rates:sync')
            ->assertSuccessful();

        $this->assertDatabaseHas('exchange_rates', [
            'currency_from' => 'USD',
            'currency_to'   => 'MXN',
        ]);

        $rate = ExchangeRate::current('USD', 'MXN');
        $this->assertEquals('17.4320', $rate->rate);
    }

    public function test_sync_command_updates_existing_record(): void
    {
        ExchangeRate::create([
            'currency_from' => 'USD',
            'currency_to'   => 'MXN',
            'rate'          => 16.0000,
            'fetched_at'    => now()->subHour(),
        ]);

        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([
                'result'          => 'success',
                'conversion_rate' => 17.4320,
            ], 200),
        ]);

        $this->artisan('exchange-rates:sync')
            ->assertSuccessful();

        $this->assertDatabaseCount('exchange_rates', 1);
        $this->assertEquals('17.4320', ExchangeRate::current('USD', 'MXN')->rate);
    }

    public function test_sync_command_fails_gracefully_on_api_error(): void
    {
        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([], 429),
        ]);

        $this->artisan('exchange-rates:sync')
            ->assertFailed();

        $this->assertDatabaseCount('exchange_rates', 0);
    }
```

- [ ] **Step 2: Ejecutar los nuevos tests y verificar que fallan**

```bash
php artisan test tests/Feature/ExchangeRateSyncTest.php --filter=sync_command
```

Resultado esperado: **ERROR** — command `exchange-rates:sync` not found.

- [ ] **Step 3: Crear el comando**

Crear `app/Console/Commands/SyncExchangeRate.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncExchangeRate extends Command
{
    protected $signature   = 'exchange-rates:sync';
    protected $description = 'Obtiene el tipo de cambio USD/MXN desde exchangerate-api.com y lo guarda en BD';

    public function handle(): int
    {
        $key = config('services.exchangerate.key');
        $url = "https://v6.exchangerate-api.com/v6/{$key}/pair/USD/MXN";

        try {
            $response = Http::timeout(10)->get($url);
        } catch (\Throwable $e) {
            Log::error('exchange-rates:sync - Error de conexión: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$response->successful() || ($response->json('result') !== 'success')) {
            Log::error('exchange-rates:sync - Respuesta inválida', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return self::FAILURE;
        }

        $rate = $response->json('conversion_rate');

        ExchangeRate::updateOrCreate(
            ['currency_from' => 'USD', 'currency_to' => 'MXN'],
            ['rate' => $rate, 'fetched_at' => now()]
        );

        $this->info("Tipo de cambio actualizado: USD/MXN = {$rate}");
        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Ejecutar todos los tests y verificar que pasan**

```bash
php artisan test tests/Feature/ExchangeRateSyncTest.php
```

Resultado esperado: **6 tests PASSED**.

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/SyncExchangeRate.php tests/Feature/ExchangeRateSyncTest.php
git commit -m "feat: add exchange-rates:sync Artisan command"
```

---

## Task 4: Scheduler

**Files:**
- Modify: `routes/console.php`

- [ ] **Step 1: Agregar el schedule en `routes/console.php`**

Abrir `routes/console.php` y agregar después del schedule existente de `purchase-orders:close-inactive`:

```php
// Sincronización del tipo de cambio USD/MXN — cada hora, L-V, 8-18h
Schedule::command('exchange-rates:sync')
    ->hourly()
    ->weekdays()
    ->between('8:00', '18:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/exchange-rates.log'));
```

El archivo completo debe quedar así:

```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierre automático por inactividad de OC directas (7 días) y estándar (10 días)
Schedule::command('purchase-orders:close-inactive')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/close-inactive-purchase-orders.log'));

// Sincronización del tipo de cambio USD/MXN — cada hora, L-V, 8-18h
Schedule::command('exchange-rates:sync')
    ->hourly()
    ->weekdays()
    ->between('8:00', '18:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/exchange-rates.log'));
```

- [ ] **Step 2: Verificar que el schedule aparece en la lista**

```bash
php artisan schedule:list
```

Resultado esperado: debe aparecer `exchange-rates:sync` con frecuencia `Hourly, between 8:00 and 18:00, on weekdays`.

- [ ] **Step 3: Commit**

```bash
git add routes/console.php
git commit -m "feat: schedule exchange-rates:sync hourly on weekdays 8-18h"
```

---

## Task 5: Inyección en vistas y badge en navbar

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `resources/views/layouts/partials/navbar.blade.php`

- [ ] **Step 1: Agregar `View::share` en `AppServiceProvider`**

Abrir `app/Providers/AppServiceProvider.php`.

Agregar el import del modelo al inicio del archivo, junto a los existentes:

```php
use App\Models\ExchangeRate;
```

En el método `boot()`, agregar al final (después del bloque `View::composer` existente):

```php
        // Tipo de cambio USD/MXN para el navbar
        View::share('exchangeRate', rescue(fn () => ExchangeRate::current('USD', 'MXN'), null));
```

El método `boot()` completo debe quedar así:

```php
    public function boot(): void
    {
        Gate::policy(ReceivingLocation::class, ReceivingLocationPolicy::class);

        View::composer('layouts.partials.sidebar', function ($view) {
            try {
                $pendingCount = SupplierDocument::where('status', 'pending_review')->count();
            } catch (\Throwable $e) {
                $pendingCount = 0;
            }

            $view->with('pendingReviewCount', $pendingCount);
        });

        // Tipo de cambio USD/MXN para el navbar
        View::share('exchangeRate', rescue(fn () => ExchangeRate::current('USD', 'MXN'), null));
    }
```

- [ ] **Step 2: Agregar el badge en el navbar**

Abrir `resources/views/layouts/partials/navbar.blade.php`.

Localizar el bloque del enlace de TotalGas (líneas ~31-38):

```html
            <!-- Mega Menu Dropdown -->
            <div class="topbar-item d-none d-md-flex">
                <div class="dropdown">
                    <a href="https://totalgas.com/" target="_blank">
                        TOTALGAS
                    </a>
                </div> <!-- .dropdown-->
            </div> <!-- end topbar-item -->
```

Agregar el badge de tipo de cambio **después** de ese bloque (entre el cierre `</div>` del topbar-item de TotalGas y antes del `</div>` del contenedor izquierdo):

```html
            <!-- Tipo de cambio USD/MXN -->
            @if($exchangeRate)
            <div class="topbar-item d-none d-md-flex">
                <span class="badge bg-light text-dark border fs-12 fw-semibold px-2 py-1">
                    <i class="ti ti-currency-dollar me-1 text-success"></i>USD
                    ${{ number_format($exchangeRate->rate, 2) }}
                    <span class="text-muted fw-normal">· act. {{ $exchangeRate->fetched_at->format('H:i') }}</span>
                </span>
            </div>
            @endif
```

- [ ] **Step 3: Ejecutar la migración en desarrollo**

```bash
php artisan migrate
```

Resultado esperado: `exchange_rates` table created.

- [ ] **Step 4: Ejecutar el comando manualmente para verificar que funciona**

```bash
php artisan exchange-rates:sync
```

Resultado esperado: `Tipo de cambio actualizado: USD/MXN = 17.XXXX`

- [ ] **Step 5: Verificar en el navegador**

Abrir el sistema en el navegador y verificar que el badge aparece en el header con el formato `USD $17.43 · act. HH:MM`.

- [ ] **Step 6: Ejecutar la suite completa de tests**

```bash
php artisan test
```

Resultado esperado: todos los tests existentes pasan (sin regresiones).

- [ ] **Step 7: Commit**

```bash
git add app/Providers/AppServiceProvider.php resources/views/layouts/partials/navbar.blade.php
git commit -m "feat: show USD/MXN exchange rate badge in navbar"
```

---

## Verificación final

- [ ] El badge aparece en el navbar con el tipo de cambio y la hora de actualización.
- [ ] Al ejecutar `php artisan exchange-rates:sync` nuevamente, el badge se actualiza (un solo registro en `exchange_rates`).
- [ ] `php artisan schedule:list` muestra el comando con la frecuencia correcta.
- [ ] Todos los tests pasan: `php artisan test`.
- [ ] El `.env` tiene `EXCHANGERATE_API_KEY` configurada con la clave real.
