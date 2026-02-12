<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $taxes = [
            [
                'name' => 'Exento',
                'rate_percent' => 0.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Frontera',
                'rate_percent' => 8.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'General',
                'rate_percent' => 16.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ];

        DB::table('taxes')->insert($taxes);
    }
}
