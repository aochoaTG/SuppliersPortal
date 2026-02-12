<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtenemos a todos los usuarios que tienen el rol de 'supplier' 
        // y que aún NO tienen un perfil de proveedor asociado.
        $availableUsers = User::role('supplier')->doesntHave('supplier')->get();

        if ($availableUsers->isEmpty()) {
            $this->command->warn("¡Soldado! No encontré usuarios con rol 'supplier' disponibles. Revisa el UserRoleSeeder.");
            return;
        }

        // 2. Repartimos los perfiles según tus necesidades usando los usuarios existentes
        // Esto evita que User::factory() se dispare dentro del SupplierFactory

        // Tomamos los primeros 5 para proveedores nacionales básicos
        $availableUsers->take(5)->each(function ($user) {
            Supplier::factory()->create([
                'user_id' => $user->id,
                'email'   => $user->email, // Heredamos el correo como acordamos
            ]);
        });

        // Tomamos los siguientes 3 para REPSE (usando el estado del factory)
        $availableUsers->slice(5, 3)->each(function ($user) {
            Supplier::factory()->repse()->create([
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
        });

        // Tomamos los siguientes 2 para Internacionales
        $availableUsers->slice(8, 2)->each(function ($user) {
            Supplier::factory()->international()->create([
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
        });

        // ====================================================================
        // REGISTRO ESPECÍFICO (User ID: 4)
        // ====================================================================
        // Si el usuario 4 es uno de los que ya procesamos arriba, esto lo ignorará 
        // o lo actualizará si usas updateOrCreate.
        $user4 = User::find(4);
        if ($user4 && !$user4->supplier) {
            Supplier::factory()->create([
                'user_id' => $user4->id,
                'company_name' => 'PROVEEDOR DE PRUEBAS UNITARIAS S.A.',
                'email' => $user4->email,
                'rfc' => 'PRUE900101ABC',
                'status' => 'approved',
            ]);
        }
    }
}
