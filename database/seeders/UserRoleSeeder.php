<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $password = Hash::make('Fl3x.2025');

        $users = [
            ['name' => 'Super Administrador', 'email' => 'tgsuperadmin@yopmail.com', 'role' => 'SuperAdmin'],
            ['name' => 'Compras User',        'email' => 'tgcompras@yopmail.com',    'role' => 'Compras'],
            ['name' => 'Contabilidad User',   'email' => 'tgcontabilidad@yopmail.com','role' => 'Contabilidad'],
            ['name' => 'Proveedor User',      'email' => 'tgproveedor@yopmail.com',   'role' => 'Proveedor'],
            ['name' => 'Autorizador User',    'email' => 'tgautorizador@yopmail.com', 'role' => 'Autorizador'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::where('name', $data['role'])->first();
            if ($role) {
                $user->syncRoles([$role]);
            }
        }
    }
}
