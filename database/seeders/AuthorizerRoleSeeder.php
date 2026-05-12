<?php

namespace Database\Seeders;

use App\Models\AuthorizerRole;
use Illuminate\Database\Seeder;

class AuthorizerRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Asamblea de Accionistas', 'approval_limit' => null, 'display_order' => 1],
            ['name' => 'Consejo de Administración', 'approval_limit' => null, 'display_order' => 2],
            ['name' => 'Dirección General', 'approval_limit' => 556925.28, 'display_order' => 3],
            ['name' => 'Dirección de Administración y Finanzas', 'approval_limit' => 144336.48, 'display_order' => 4],
            ['name' => 'Dirección de Operaciones', 'approval_limit' => 144336.48, 'display_order' => 5],
            ['name' => 'Gerencia de Finanzas y Normatividad', 'approval_limit' => 36084.12, 'display_order' => 6],
            ['name' => 'Jefatura de Administración e Ingresos', 'approval_limit' => 13920.00, 'display_order' => 7],
            ['name' => 'Jefe de Contabilidad', 'approval_limit' => null, 'display_order' => 8],
            ['name' => 'Gerente de Recursos Humanos', 'approval_limit' => 36084.12, 'display_order' => 9],
            ['name' => 'Gerente de Expansión', 'approval_limit' => null, 'display_order' => 10],
            ['name' => 'Gerencias de Proyectos', 'approval_limit' => null, 'display_order' => 11],
            ['name' => 'Gerente de Operaciones', 'approval_limit' => 36084.12, 'display_order' => 12],
            ['name' => 'Gerente Comercial y de MKT', 'approval_limit' => 36084.12, 'display_order' => 13],
            ['name' => 'Jefatura de Soporte e Infraestructura', 'approval_limit' => null, 'display_order' => 14],
            ['name' => 'Jefatura de Abasto y Logística', 'approval_limit' => 13922.32, 'display_order' => 15],
            ['name' => 'Jefatura de Jurídico', 'approval_limit' => null, 'display_order' => 16],
        ];

        foreach ($roles as $role) {
            AuthorizerRole::updateOrCreate(
                ['name' => $role['name']],
                [
                    'approval_limit' => $role['approval_limit'],
                    'matrix_sheet' => '15 abr 26',
                    'matrix_reference' => 'Fila 42 - Autorizacion + IVA',
                    'display_order' => $role['display_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
