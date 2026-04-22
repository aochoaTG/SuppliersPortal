<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExchangeRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'currency_from' => 'USD',
            'currency_to'   => 'MXN',
            'rate'          => $this->faker->randomFloat(4, 16.0, 20.0),
            'fetched_at'    => now(),
        ];
    }
}
