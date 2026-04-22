<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $this->assertSame('17.4320', $result->rate);
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
        $this->assertSame('17.4320', $rate->rate);
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
        $this->assertSame('17.4320', ExchangeRate::current('USD', 'MXN')->rate);
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
}
