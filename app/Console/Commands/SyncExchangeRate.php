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
