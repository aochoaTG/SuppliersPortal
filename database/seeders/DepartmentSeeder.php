<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Dirección General', 'abbreviated' => 'DIR'],
            ['name' => 'Administración y Finanzas', 'abbreviated' => 'ADF'],
            ['name' => 'Operaciones', 'abbreviated' => 'OPE'],
            ['name' => 'Soporte e Infraestructura', 'abbreviated' => 'SEI'],
            ['name' => 'Comercial y Marketing', 'abbreviated' => 'COMK'],
            ['name' => 'Compras', 'abbreviated' => 'COMP'],
            ['name' => 'Proyectos', 'abbreviated' => 'PROY'],
            ['name' => 'Auditoría', 'abbreviated' => 'AUD'],
            ['name' => 'Jurídico', 'abbreviated' => 'JUR'],
            ['name' => 'Expansión', 'abbreviated' => 'EXP'],
            ['name' => 'Capital Humano', 'abbreviated' => 'CH'],
            ['name' => 'Servicio al Cliente', 'abbreviated' => 'SAC'],
            ['name' => 'Logística y Abastos', 'abbreviated' => 'LOG'],
            ['name' => 'Mantenimiento', 'abbreviated' => 'MTTO'],
            ['name' => 'Seguridad', 'abbreviated' => 'SEG'],
            ['name' => 'SASISOPA', 'abbreviated' => 'SSSP'],
            ['name' => 'Aplicaciones y Software', 'abbreviated' => 'AYS'],
            ['name' => 'Ingresos', 'abbreviated' => 'IGS'],
            ['name' => 'Tesorería', 'abbreviated' => 'TES'],
            ['name' => 'Contabilidad', 'abbreviated' => 'CON'],
        ];

        $now = now();
        foreach ($rows as $i => $r) {
            $rows[$i]['is_active'] = true;
            $rows[$i]['notes'] = null;
            $rows[$i]['created_by'] = null;
            $rows[$i]['created_at'] = $now;
            $rows[$i]['updated_at'] = $now;
        }

        DB::table('departments')->upsert($rows, ['name', 'abbreviated'], ['updated_at']);
    }
}
