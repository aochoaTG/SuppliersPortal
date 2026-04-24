<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * CostCenterSeeder
 *
 * Carga masiva de Centros de Costo con la nueva estructura.
 * NOTA: Usa empresas EXISTENTES en lugar de crear una nueva.
 */
class CostCenterSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 1) ASEGURAR QUE EXISTAN CATEGORÍAS =====
        $categoryNames = [
            'ADMINISTRACION',
            'ENPROYECTO',
            'STAFF',
            'CORPORATIVO',
            'OPERACIONES',
            'ESTACIONES',
        ];

        foreach ($categoryNames as $catName) {
            Category::firstOrCreate(['name' => $catName], [
                'description' => $catName . ' (autocreada por seeder de centros)',
                'is_active' => true,
            ]);
        }

        // Mapa nombre => id para resolver FKs
        $catId = Category::query()
            ->whereIn('name', $categoryNames)
            ->pluck('id', 'name')
            ->toArray();

        // ===== 2) OBTENER EMPRESA EXISTENTE =====
        // 🔴 CAMBIO: Usa la primera empresa existente
        $company = Company::first();

        if (!$company) {
            $this->command->error('❌ No hay empresas en la tabla. Por favor, crea al menos una empresa primero.');
            return;
        }

        $this->command->info("📌 Usando empresa: {$company->name}");

        // ===== 3) ASEGURAR QUE EXISTA UN USUARIO PARA AUDITORÍA =====
        $defaultUser = User::firstOrCreate(
            ['email' => 'admin@totalgas.local'],
            [
                'name' => 'Administrador Sistema',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // ===== 4) ABREVIATURAS POR CATEGORÍA =====
        $abbr = [
            'ADMINISTRACION' => 'ADM',
            'ENPROYECTO' => 'ENP',
            'STAFF' => 'STF',
            'CORPORATIVO' => 'COR',
            'OPERACIONES' => 'OPE',
            'ESTACIONES' => 'EST',
        ];

        $purchaseTypes = [
            'ADMINISTRACION' => 'Gasto Staff',
            'ENPROYECTO' => 'Gasto Operativo',
            'STAFF' => 'Gasto Staff',
            'CORPORATIVO' => 'Gasto Corporativo',
            'OPERACIONES' => 'Gasto Operativo',
            'ESTACIONES' => 'Gasto Operativo',
        ];

        // ===== 5) LISTA DE CENTROS DE COSTO =====
        $rows = [
            // ADMINISTRACION
            ['category' => 'ADMINISTRACION', 'name' => 'CC BALANCE', 'budget_type' => 'ANNUAL'],

            // CORPORATIVO
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO', 'budget_type' => 'ANNUAL'],
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO EMPRESA', 'budget_type' => 'ANNUAL'],
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO J', 'budget_type' => 'ANNUAL'],
            ['category' => 'CORPORATIVO', 'name' => 'CORPORATIVO L', 'budget_type' => 'ANNUAL'],

            // ENPROYECTO
            ['category' => 'ENPROYECTO', 'name' => 'ADUANA', 'budget_type' => 'ANNUAL'],
            ['category' => 'ENPROYECTO', 'name' => 'ALUMBRADO PÚBLICO', 'budget_type' => 'ANNUAL'],
            ['category' => 'ENPROYECTO', 'name' => 'ASSESMENT', 'budget_type' => 'FREE_CONSUMPTION'],
            ['category' => 'ENPROYECTO', 'name' => 'AUTOLAVADO', 'budget_type' => 'ANNUAL'],
            ['category' => 'ENPROYECTO', 'name' => 'BAJIO', 'budget_type' => 'ANNUAL'],
            ['category' => 'ENPROYECTO', 'name' => 'BODEGA DE ADITIVO', 'budget_type' => 'ANNUAL'],
            ['category' => 'ENPROYECTO', 'name' => 'CASA DE CAMBIO', 'budget_type' => 'ANNUAL'],

            // ESTACIONES
            ['category' => 'ESTACIONES', 'name' => 'AERONAUTICA', 'budget_type' => 'ANNUAL'],
            ['category' => 'ESTACIONES', 'name' => 'ANAPRA', 'budget_type' => 'ANNUAL'],
            ['category' => 'ESTACIONES', 'name' => 'AZTECAS', 'budget_type' => 'ANNUAL'],
            ['category' => 'ESTACIONES', 'name' => 'CUSTODIA', 'budget_type' => 'ANNUAL'],
            ['category' => 'ESTACIONES', 'name' => 'DELICIAS', 'budget_type' => 'ANNUAL'],
            ['category' => 'ESTACIONES', 'name' => 'ELECTROLUX', 'budget_type' => 'ANNUAL'],

            // OPERACIONES
            ['category' => 'OPERACIONES', 'name' => 'EL CASTAÑO', 'budget_type' => 'ANNUAL'],
            ['category' => 'OPERACIONES', 'name' => 'ES MALECON', 'budget_type' => 'ANNUAL'],
            ['category' => 'OPERACIONES', 'name' => 'ES MUNICIPIO LIBRE', 'budget_type' => 'ANNUAL'],
            ['category' => 'OPERACIONES', 'name' => 'PLUTARCO', 'budget_type' => 'ANNUAL'],

            // STAFF
            ['category' => 'STAFF', 'name' => 'ADMINISTRATIVO', 'budget_type' => 'ANNUAL'],
            ['category' => 'STAFF', 'name' => 'APLICACIONES Y SOFTWARE', 'budget_type' => 'ANNUAL'],
            ['category' => 'STAFF', 'name' => 'AUDITORIA', 'budget_type' => 'ANNUAL'],
            ['category' => 'STAFF', 'name' => 'COMERCIAL', 'budget_type' => 'ANNUAL'],
            ['category' => 'STAFF', 'name' => 'CONTABILIDAD', 'budget_type' => 'ANNUAL'],
            ['category' => 'STAFF', 'name' => 'DIRECCION COMERCIAL Y DE OPERACIONES', 'budget_type' => 'ANNUAL'],
        ];

        // ===== 6) INSERTAR/ASEGURAR REGISTROS =====
        foreach ($rows as $row) {
            $catName = $row['category'];
            $name = $row['name'];
            $budgetType = $row['budget_type'] ?? 'ANNUAL';
            $purchaseType = $row['purchase_type'] ?? ($purchaseTypes[$catName] ?? 'Gasto Operativo');

            // Resuelve category_id
            $categoryId = $catId[$catName] ?? null;
            if (!$categoryId) {
                continue;
            }

            // Genera code único
            $prefix = $abbr[$catName] ?? 'GEN';
            $slug = Str::slug($name, '_');
            $slug = strtoupper($slug);
            $baseCode = $prefix . '_' . $slug;
            $code = $this->uniqueCode($baseCode);

            CostCenter::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => null,
                    'purchase_type' => $purchaseType,
                    'category_id' => $categoryId,
                    'company_id' => $company->id, // 🔴 USA EMPRESA EXISTENTE
                    'responsible_user_id' => $defaultUser->id,
                    'budget_type' => $budgetType,
                    'global_amount' => null,
                    'free_consumption_justification' => null,
                    'authorized_by' => null,
                    'authorized_at' => null,
                    'status' => 'ACTIVO',
                    'created_by' => $defaultUser->id,
                    'updated_by' => null,
                    'deleted_by' => null,
                ]
            );
        }

        $this->command->info('✅ ' . CostCenter::count() . ' Centros de Costo cargados correctamente.');
    }

    /**
     * Genera un code único basado en baseCode.
     * Si ya existe, agrega sufijos _2, _3, etc.
     */
    protected function uniqueCode(string $baseCode): string
    {
        $code = $baseCode;
        $i = 2;

        while (CostCenter::where('code', $code)->exists()) {
            $code = $baseCode . '_' . $i;
            $i++;
        }

        return substr($code, 0, 50);
    }
}
