<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $createdBy = 1;

        $categoryIds = DB::table('expense_categories')
            ->pluck('id', 'code');

        $cedulas = [
            ['code' => 'C', 'name' => 'Cumplimiento Sat Anexo 30 y 31'],
            ['code' => 'E', 'name' => 'Maxima'],
            ['code' => 'E', 'name' => 'Diesel'],
            ['code' => 'F', 'name' => 'Cedula de Operaciones (COA)'],
            ['code' => 'H', 'name' => 'Mantenimiento de Estaciones'],
            ['code' => 'H', 'name' => 'Dictamen de Calidad de los Petroliferos NOM-016-CRE-2016'],
            ['code' => 'H', 'name' => 'Dictamen Instalacion Electricas NOM-001-SEDE-2012'],
        ];

        foreach ($cedulas as $cedula) {
            $categoryId = $categoryIds[$cedula['code']] ?? null;
            if (! $categoryId) {
                continue;
            }

            $exists = DB::table('budget_cedulas')
                ->where('expense_category_id', $categoryId)
                ->where('name', $cedula['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('budget_cedulas')->insert([
                'expense_category_id' => $categoryId,
                'name' => $cedula['name'],
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $names = [
            'Cumplimiento Sat Anexo 30 y 31',
            'Maxima',
            'Diesel',
            'Cedula de Operaciones (COA)',
            'Mantenimiento de Estaciones',
            'Dictamen de Calidad de los Petroliferos NOM-016-CRE-2016',
            'Dictamen Instalacion Electricas NOM-001-SEDE-2012',
        ];

        DB::table('budget_cedulas')
            ->whereIn('name', $names)
            ->delete();
    }
};
