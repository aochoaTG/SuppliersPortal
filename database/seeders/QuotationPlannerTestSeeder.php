<?php

namespace Database\Seeders;

use App\Enum\RequisitionStatus;
use App\Models\User;
use App\Models\Category;
use App\Models\ProductService;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuotationPlannerTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando despliegue de datos estratégicos para TotalGas...');

        // ====================================================================
        // 1. USUARIO ADMINISTRADOR (Paso de lista obligatorio)
        // ====================================================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@totalgas.com'],
            [
                'name' => 'Admin Sistema',
                'password' => bcrypt('password123')
            ]
        );

        $this->command->info('✅ Usuario admin verificado [ID: ' . $admin->id . ']');

        // ====================================================================
        // 2. CATEGORÍA DE GASTO (Requerida por RN-010A)
        // ====================================================================
        $expenseCat = ExpenseCategory::firstOrCreate(
            ['code' => 'EXP-OP-001'],
            [
                'name' => 'Gasto Operativo',
                'description' => 'Operación general de estaciones de servicio',
                'status' => 'ACTIVO',
                'created_by' => $admin->id,
            ]
        );

        $this->command->info('✅ Categoría de gasto verificada');

        // ====================================================================
        // 3. CATÁLOGO DE PRODUCTOS (Alineado a su migración técnica)
        // ====================================================================
        $testData = [
            [
                'category' => 'Equipo de Cómputo',
                'products' => [
                    ['name' => 'Mouse inalámbrico Logitech', 'code' => 'MOUSE-001'],
                    ['name' => 'Teclado mecánico Keychron', 'code' => 'TECLADO-001'],
                    ['name' => 'Monitor LG 27 pulgadas', 'code' => 'MONITOR-001'],
                ]
            ],
            [
                'category' => 'Papelería',
                'products' => [
                    ['name' => 'Resma papel bond carta', 'code' => 'PAPEL-001'],
                    ['name' => 'Plumas BIC azul caja 50', 'code' => 'PLUMA-001'],
                ]
            ],
        ];

        foreach ($testData as $categoryData) {
            $category = Category::firstOrCreate(
                ['name' => $categoryData['category']],
                ['created_by' => $admin->id]
            );

            foreach ($categoryData['products'] as $productData) {
                ProductService::firstOrCreate(
                    ['code' => $productData['code']],
                    [
                        'short_name' => $productData['name'],
                        'technical_description' => $productData['name'] . ' - Especificación técnica corporativa requerida por TotalGas.',
                        'product_type' => 'PRODUCTO',
                        'category_id' => $category->id,
                        'cost_center_id' => 1,
                        'company_id' => 1,
                        'status' => 'ACTIVE',
                        'is_active' => true,
                        'created_by' => $admin->id,
                    ]
                );
            }
        }

        $this->command->info('✅ Catálogo de productos en posición.');

        // ====================================================================
        // 4. REQUISICIÓN MAESTRA (Ajustado a su modelo Requisition)
        // ====================================================================
        $requisition = Requisition::create([
            'company_id' => 1,
            'cost_center_id' => 1,
            'department_id' => 1,
            'folio' => Requisition::nextFolio(),
            'requested_by' => $admin->id,
            'required_date' => now()->addDays(15),
            'description' => 'Requisición para pruebas del Planificador de Cotización',
            'status' => RequisitionStatus::IN_QUOTATION,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->command->info('✅ Requisición creada: ' . $requisition->folio);

        // ====================================================================
        // 5. PARTIDAS (Siguiendo su migración SIN PRECIOS)
        // ====================================================================
        $products = ProductService::all();
        foreach ($products as $index => $product) {
            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'product_service_id' => $product->id,
                'line_number' => $index + 1,
                'item_category' => 'PRODUCTO',
                'product_code' => $product->code,
                'description' => $product->short_name,
                'expense_category_id' => $expenseCat->id,
                'quantity' => 5,
                'unit' => 'PZA',
                'notes' => 'Partida generada para validación del Portal de Proveedores.',
            ]);
        }

        // ====================================================================
        // 6. RESUMEN FINAL (Blindado contra RelationNotFoundException)
        // ====================================================================
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('   ✅ OPERACIÓN EXITOSA: DATOS DE PRUEBA LISTOS');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('ID:       ' . $requisition->id);
        $this->command->info('Folio:    ' . $requisition->folio);
        $this->command->info('Estado:   ' . $requisition->statusLabel());
        $this->command->info('Partidas: ' . $requisition->items->count());
        $this->command->newLine();
        $this->command->warn('🔗 URL DEL PLANIFICADOR:');
        $this->command->line('   http://localhost/requisitions/' . $requisition->id . '/quotation-planner');
        $this->command->newLine();
        $this->command->info('📋 DESGLOSE POR CATEGORÍA:');

        // Cargamos las relaciones correctas para evitar errores
        $requisition->load(['items.productService.category']);

        $itemsByCat = $requisition->items->groupBy(function ($item) {
            return $item->productService->category->name ?? 'Sin Categoría';
        });

        foreach ($itemsByCat as $categoryName => $items) {
            $this->command->line('');
            $this->command->line('  📦 ' . $categoryName . ':');
            foreach ($items as $item) {
                $this->command->line('     • ' . $item->description . ' (' . (int)$item->quantity . ' ' . $item->unit . ')');
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Copia la URL de arriba y pégala en tu navegador');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}
