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
}
