<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserRoleSeeder::class,
            CompanySeeder::class,
            StationSeeder::class,
            CategorySeeder::class,
            CostCenterSeeder::class,
            AnnualBudgetSeeder::class,
            DepartmentSeeder::class,
            TaxSeeder::class,
        ]);
    }
}
