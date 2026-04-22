# Diseño: Tipo de Cambio USD/MXN en tiempo real

**Fecha:** 2026-04-21  
**Autor:** aldo.ochoa@totalgas.com  
**Alcance:** Consumo periódico de la API exchangerate-api.com, almacenamiento en BD y visualización en el navbar. Las integraciones con Órdenes de Compra y Requisiciones quedan fuera de este alcance y se diseñarán por separado.

---

## 1. Base de datos y modelo

### Tabla `exchange_rates`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK auto-increment | |
| `currency_from` | char(3) | Ej. `'USD'` |
| `currency_to` | char(3) | Ej. `'MXN'` |
| `rate` | decimal(10,4) | Ej. `17.4320` |
| `fetched_at` | timestamp | Momento en que se obtuvo de la API |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

- Un único registro por par de divisas (upsert en cada sync).
- Índice único en `(currency_from, currency_to)`.

### Modelo `App\Models\ExchangeRate`

- Método estático `current(string $from, string $to): ?self` — devuelve el registro activo o `null` si aún no existe.
- Cualquier parte del sistema accede al rate con: `ExchangeRate::current('USD', 'MXN')->rate`

---

## 2. Artisan Command y Scheduler

### Comando `exchange-rates:sync`

**Archivo:** `app/Console/Commands/SyncExchangeRate.php`  
**Signature:** `exchange-rates:sync`

Flujo:
1. Lee la API key desde `config('services.exchangerate.key')` (env: `EXCHANGERATE_API_KEY`).
2. Llama a `GET https://v6.exchangerate-api.com/v6/{key}/pair/USD/MXN` con `Http::timeout(10)`.
3. Si la respuesta es exitosa (`result === 'success'`), hace upsert en `exchange_rates` con `conversion_rate` y `fetched_at = now()`.
4. Si falla (timeout, error HTTP, rate limit), registra en `Log::error()` y devuelve `self::FAILURE`. El sistema sigue mostrando el último rate almacenado en BD.

### Configuración de la API key

En `.env`:
```
EXCHANGERATE_API_KEY=<tu_api_key>
```

En `config/services.php`:
```php
'exchangerate' => [
    'key' => env('EXCHANGERATE_API_KEY'),
],
```

### Scheduler

En `routes/console.php` (siguiendo el patrón existente del proyecto):

```php
Schedule::command('exchange-rates:sync')
    ->hourly()
    ->weekdays()
    ->between('8:00', '18:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/exchange-rates.log'));
```

**Consumo estimado:** 10 requests/día hábil × 22 días/mes ≈ **220 requests/mes** (límite plan gratuito: 1,500/mes).

---

## 3. Navbar — Badge de tipo de cambio

### Inyección de datos

En `App\Providers\AppServiceProvider::boot()`:

```php
View::share('exchangeRate', ExchangeRate::current('USD', 'MXN'));
```

### Visualización

**Archivo:** `resources/views/layouts/partials/navbar.blade.php`

Se agrega un badge en la barra superior del topbar, ubicado entre el enlace de TotalGas y el Log Viewer (para usuarios dev). Solo se renderiza si `$exchangeRate` no es `null`.

Formato de display:
```
USD $17.43 · act. 10:00
```

- El monto se formatea con 2 decimales.
- La hora de actualización se muestra como `H:i` (hora local del servidor) tomada de `fetched_at`.
- Si no hay dato en BD (primer deploy antes del primer sync), el badge no se muestra.

---

## 4. Manejo de errores

| Escenario | Comportamiento |
|---|---|
| API no disponible | Log::error, comando retorna FAILURE, navbar muestra último rate conocido |
| BD vacía (primer deploy) | Badge del navbar no se renderiza |
| Rate limit agotado | Mismo que API no disponible |
| Timeout de conexión | Http::timeout(10) lanza excepción → capturada → Log::error |

---

## 5. Fuera de alcance (para fases posteriores)

- Integración del tipo de cambio en `PurchaseOrder` (columna `exchange_rate` en OC).
- Integración en `DirectPurchaseOrder` (ODC).
- Historial de tipos de cambio.
- Soporte para EUR u otras divisas.
