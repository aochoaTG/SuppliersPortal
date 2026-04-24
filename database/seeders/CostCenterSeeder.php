<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CostCenterSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureCategories();

        $responsible = User::query()
            ->where('name', 'Super Administrador')
            ->first();

        if (!$responsible) {
            $this->command->error('No se encontro el usuario "Super Administrador" para asignarlo como responsable de los centros de costo.');
            return;
        }

        $companyAliases = [
            'formula gas' => 'FormulaGas',
            'grupo gasolinero tsa del centro' => 'Grupo Operador Gasolinero TSA del Centro',
            'hector armandino' => 'Hector Armandino Fierro Holguin',
            'servicio jarudo' => 'Servicio El Jarudo',
        ];

        $categoryMap = Category::query()
            ->whereIn('name', ['ADMINISTRACION', 'ENPROYECTO', 'STAFF', 'CORPORATIVO', 'OPERACIONES', 'ESTACIONES'])
            ->get(['id', 'name'])
            ->keyBy('name');

        $companyMap = Company::query()
            ->get(['id', 'name'])
            ->keyBy(fn (Company $company) => $this->normalizeKey($company->name));

        $rows = $this->rows();

        DB::transaction(function () use ($rows, $companyAliases, $categoryMap, $companyMap, $responsible) {
            foreach ($rows as $row) {
                $rawCompanyKey = $this->normalizeKey($row['company_name']);
                $resolvedCompanyName = $companyAliases[$rawCompanyKey] ?? $row['company_name'];
                $company = $companyMap->get($this->normalizeKey($resolvedCompanyName));
                if (!$company) {
                    throw new \RuntimeException("La empresa [{$row['company_name']}] no existe y no pudo resolverse con aliases.");
                }

                $category = $categoryMap->get($row['category_name']);
                if (!$category) {
                    throw new \RuntimeException("La categoria [{$row['category_name']}] no existe en el catalogo.");
                }

                CostCenter::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $row['code'],
                        'name' => $row['name'],
                    ],
                    [
                        'description' => null,
                        'purchase_type' => $this->mapPurchaseType($row['purchase_type_raw'], $row['category_name']),
                        'category_id' => $category->id,
                        'responsible_user_id' => $responsible->id,
                        'budget_type' => $this->mapBudgetType($row['budget_type_raw']),
                        'global_amount' => null,
                        'free_consumption_justification' => null,
                        'authorized_by' => null,
                        'authorized_at' => null,
                        'validity_date' => null,
                        'status' => 'ACTIVO',
                        'created_by' => $responsible->id,
                        'updated_by' => null,
                        'deleted_by' => null,
                    ]
                );
            }
        });

        $this->command->info('Seeder de centros de costo ejecutado con ' . count($rows) . ' registros desde la hoja CATALOGOS.');
    }

    private function ensureCategories(): void
    {
        $rows = [
            ['name' => 'ADMINISTRACION', 'description' => 'Gastos y servicios administrativos', 'is_active' => true],
            ['name' => 'ENPROYECTO', 'description' => 'Agrupacion de costos en fase de proyecto', 'is_active' => true],
            ['name' => 'STAFF', 'description' => 'Personal / Recursos Humanos / equipos internos', 'is_active' => true],
            ['name' => 'CORPORATIVO', 'description' => 'Oficina central y servicios compartidos', 'is_active' => true],
            ['name' => 'OPERACIONES', 'description' => 'Operaciones y soporte en campo', 'is_active' => true],
            ['name' => 'ESTACIONES', 'description' => 'Agrupacion de estaciones de servicio', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            Category::firstOrCreate(
                ['name' => $row['name']],
                ['description' => $row['description'], 'is_active' => $row['is_active']]
            );
        }
    }

    private function normalizeKey(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }

    private function mapPurchaseType(string $rawValue, string $categoryName): string
    {
        $value = strtoupper(trim($rawValue));

        return match ($value) {
            'OPERATIVO', 'ESTACIONES' => 'Gasto Operativo',
            'STAFF' => 'Gasto Staff',
            'CORPORATIVO' => 'Gasto Corporativo',
            '' => match ($categoryName) {
                'STAFF' => 'Gasto Staff',
                'CORPORATIVO' => 'Gasto Corporativo',
                default => 'Gasto Operativo',
            },
            default => throw new \RuntimeException("Tipo de compra no reconocido: [{$rawValue}]."),
        };
    }

    private function mapBudgetType(string $rawValue): string
    {
        return match (strtoupper(trim($rawValue))) {
            'ANNUAL' => 'ANNUAL',
            'FREE_CONSUMPTION' => 'FREE_CONSUMPTION',
            default => throw new \RuntimeException("Tipo de presupuesto no reconocido: [{$rawValue}]."),
        };
    }

    private function rows(): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim(<<<'TSV'
618	MasQueGas	CORPORATIVO	Controladora de Vales	ADMINISTRACION	Annual
1149	LERDO	OPERATIVO	Diaz Gas	ESTACIONES	Annual
2526	LÓPEZ MATEOS	OPERATIVO	Diaz Gas	ESTACIONES	Annual
4179	GEMELA CH	OPERATIVO	Diaz Gas	ESTACIONES	Annual
4188	GEMELA GDE	OPERATIVO	Diaz Gas	ESTACIONES	Annual
5317	MPIO LIBRE	OPERATIVO	Diaz Gas	ESTACIONES	Annual
5465	AZTECAS	OPERATIVO	Diaz Gas	ESTACIONES	Annual
6410	MISIONES	OPERATIVO	Diaz Gas	ESTACIONES	Annual
6947	PTO DE PALOS	OPERATIVO	Diaz Gas	ESTACIONES	Annual
7167	M. DE LA MADRID	OPERATIVO	Diaz Gas	ESTACIONES	Annual
8244	PERMUTA	OPERATIVO	Diaz Gas	ESTACIONES	Annual
9191	ELECTROLUX	OPERATIVO	Diaz Gas	ESTACIONES	Annual
9235	AERONAUTICA	OPERATIVO	Diaz Gas	ESTACIONES	Annual
9885	CUSTODIA	OPERATIVO	Diaz Gas	ESTACIONES	Annual
9893	ANAPRA	OPERATIVO	Diaz Gas	ESTACIONES	Annual
2172	PARRAL	OPERATIVO	Diaz Gas	ESTACIONES	Annual
1163	TECNOLOGICO	OPERATIVO	Diaz Gas	ESTACIONES	Annual
23214	HERMANOS ESCOBAR	OPERATIVO	Diaz Gas	ESTACIONES	Annual
24938	TRAVEL	OPERATIVO	Diaz Gas	ESTACIONES	Annual
00145	FORMULA GAS	OPERATIVO	Diaz Gas	ESTACIONES	Annual
00147	GASOMEX	OPERATIVO	Diaz Gas	ESTACIONES	Annual
00157	PROYECTO SAN ISIDRO	CORPORATIVO	Diaz Gas	ENPROYECTO	Annual
00160	BODEGA DE ADITIVO	OPERATIVO	Diaz Gas	OPERACIONES	Annual
00161	LABORATORIO ANALISIS COMBUSTIBLE	OPERATIVO	Diaz Gas	OPERACIONES	Annual
00162	PROYECTO AUTOLAVADO GOMEZ MORIN	CORPORATIVO	Diaz Gas	ENPROYECTO	Annual
00163	PROYECTO AUTOLAVADO PLUTARCO	CORPORATIVO	Diaz Gas	ENPROYECTO	Annual
00996	CORPORATIVO L	CORPORATIVO	Diaz Gas	CORPORATIVO	Annual
00997	CORPORATIVO J	CORPORATIVO	Diaz Gas	CORPORATIVO	Annual
01900	PANELES SOLARES	OPERATIVO	Diaz Gas	OPERACIONES	Annual
02226	EXPANSION 22-26	OPERATIVO	Diaz Gas	OPERACIONES	Annual
03184	PRAXEDIS	OPERATIVO	Diaz Gas	ESTACIONES	Annual
16947	PROYECTO 6947	CORPORATIVO	Diaz Gas	CORPORATIVO	Annual
00171	COMERCIAL	STAFF	Diaz Gas	STAFF	Annual
00172	CONTABILIDAD	STAFF	Diaz Gas	STAFF	Annual
00173	NOMINAS	STAFF	Diaz Gas	STAFF	Annual
00174	LOGISTICA	STAFF	Diaz Gas	STAFF	Annual
00175	INGRESOS	STAFF	Diaz Gas	STAFF	Annual
00176	DIRECCIÓN	STAFF	Diaz Gas	STAFF	Annual
00177	MANTENIMIENTO	STAFF	Diaz Gas	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Diaz Gas	STAFF	Annual
00179	PROYECTOS	STAFF	Diaz Gas	STAFF	Annual
00180	JURIDICO	STAFF	Diaz Gas	STAFF	Annual
00181	SISTEMAS	STAFF	Diaz Gas	STAFF	Annual
00182	PSICOLOGIA	STAFF	Diaz Gas	STAFF	Annual
00183	DIRECCION COMERCIAL Y DE OPERACIONES	STAFF	Diaz Gas	STAFF	Annual
00184	APLICACIONES Y SOFTWARE	STAFF	Diaz Gas	STAFF	Annual
00333	PROYECTO 1G	STAFF	Diaz Gas	STAFF	Annual
00444	OPERATIVO	STAFF	Diaz Gas	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Diaz Gas	STAFF	Annual
00777	ADMINISTRATIVO	STAFF	Diaz Gas	STAFF	Annual
00800	AUDITORIA	STAFF	Diaz Gas	STAFF	Annual
00999	CORPORATIVO	CORPORATIVO	Diaz Gas	CORPORATIVO	Annual
1256	Clara	OPERATIVO	Distribuidora Clara	ESTACIONES	Annual
00171	COMERCIAL	STAFF	Distribuidora Clara	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Distribuidora Clara	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Distribuidora Clara	STAFF	Annual
00777	ADMINISTRATIVO	CORPORATIVO	Distribuidora Clara	ADMINISTRACION	Annual
00999	CORPORATIVO	CORPORATIVO	Distribuidora Clara	CORPORATIVO	Annual
9733	Ejercito	OPERATIVO	Distribuidora Gasomex	ESTACIONES	Annual
1159	Fuentes	OPERATIVO	Distribuidora Gasomex	ESTACIONES	Annual
2097	Santiago T.	OPERATIVO	Distribuidora Gasomex	ESTACIONES	Annual
4457	Satelite	OPERATIVO	Distribuidora Gasomex	ESTACIONES	Annual
1141	Solis	OPERATIVO	Distribuidora Gasomex	ESTACIONES	Annual
00171	COMERCIAL	STAFF	Distribuidora Gasomex	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Distribuidora Gasomex	STAFF	Annual
00444	OPERATIVO	STAFF	Distribuidora Gasomex	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Distribuidora Gasomex	STAFF	Annual
00777	ADMINISTRATIVO	STAFF	Distribuidora Gasomex	STAFF	Annual
00999	CORPORATIVO	CORPORATIVO	Distribuidora Gasomex	CORPORATIVO	Annual
11007	C. Independencia	OPERATIVO	Estacion Custodia	ESTACIONES	Annual
5170	C. Plutarco	OPERATIVO	Estacion Custodia	ESTACIONES	Annual
03195	ZONA BAJIO	OPERATIVO	Estacion Custodia	ESTACIONES	Annual
07437	ESTACION MADRAZO	OPERATIVO	Estacion Custodia	ESTACIONES	Annual
07455	ESTACION VALTIERRA	OPERATIVO	Estacion Custodia	ESTACIONES	Annual
07482	ESTACION TORRES	OPERATIVO	Estacion Custodia	ESTACIONES	Annual
00333	PROYECTO 1G	CORPORATIVO	Estacion Custodia	ENPROYECTO	Annual
00444	OPERATIVO	OPERATIVO	Estacion Custodia	OPERACIONES	Annual
3340	Gabriela Mistral	OPERATIVO	Formula Gas	ESTACIONES	Annual
00171	COMERCIAL	STAFF	Formula Gas	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Formula Gas	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Formula Gas	STAFF	Annual
00777	ADMINISTRATIVO	CORPORATIVO	Formula Gas	ADMINISTRACION	Annual
00999	CORPORATIVO	CORPORATIVO	Formula Gas	CORPORATIVO	Annual
5114	Villa Ahumada	OPERATIVO	Gasolinera Villa Ahumada	ESTACIONES	Annual
4816	San Rafael	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
5627	Puertecito	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
5891	Jesus Maria	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
2600	Colosio	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
00171	COMERCIAL	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
00178	RECURSOS HUMANOS	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
00444	OPERATIVO	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
00555	VENTAS/ COSTO MKT	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
00777	ADMINISTRATIVO	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
00999	CORPORATIVO	OPERATIVO	Grupo gasolinero TSA del centro	ESTACIONES	Annual
702	Praxedis	OPERATIVO	Hector Armandino	ESTACIONES	Annual
7	Plaza Ester	CORPORATIVO	Inmo Diaz	CORPORATIVO	Annual
16	Travel	ESTACIONES	Inmo Diaz	ESTACIONES	Annual
00171	COMERCIAL	ESTACIONES	Petrotal	STAFF	Annual
00174	LOGISTICA	STAFF	Petrotal	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Petrotal	STAFF	Annual
00333	PROYECTO 1G	STAFF	Petrotal	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Petrotal	STAFF	Annual
00777	ADMINISTRATIVO	CORPORATIVO	Petrotal	ADMINISTRACION	Annual
00999	CORPORATIVO		Petrotal	CORPORATIVO	Annual
1148	Jarudo	OPERATIVO	Servicio Jarudo	ESTACIONES	Annual
00171	COMERCIAL	STAFF	Servicio Jarudo	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Servicio Jarudo	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Servicio Jarudo	STAFF	Annual
00777	ADMINISTRATIVO	CORPORATIVO	Servicio Jarudo	ADMINISTRACION	Annual
00999	CORPORATIVO	CORPORATIVO	Servicio Jarudo	CORPORATIVO	Annual
1376	Delicias	OPERATIVO	Servicio SYC	ESTACIONES	Annual
1290	El Castaño	OPERATIVO	Servicios Gasolineros El Castaño	ESTACIONES	Annual
00171	COMERCIAL	STAFF	Servicios Gasolineros El Castaño	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	Servicios Gasolineros El Castaño	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	Servicios Gasolineros El Castaño	STAFF	Annual
00777	ADMINISTRATIVO	CORPORATIVO	Servicios Gasolineros El Castaño	ADMINISTRACION	Annual
00999	CORPORATIVO	CORPORATIVO	Servicios Gasolineros El Castaño	CORPORATIVO	Annual
4499	SMA Picachos	OPERATIVO	SMA Picachos	ESTACIONES	Annual
00171	COMERCIAL	STAFF	SMA Picachos	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	SMA Picachos	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	SMA Picachos	STAFF	Annual
00777	ADMINISTRATIVO	CORPORATIVO	SMA Picachos	ADMINISTRACION	Annual
00999	CORPORATIVO	CORPORATIVO	SMA Picachos	CORPORATIVO	Annual
4500	SMA Ventanas	OPERATIVO	SMA Ventanas	ESTACIONES	Annual
00171	COMERCIAL	STAFF	SMA Ventanas	STAFF	Annual
00178	RECURSOS HUMANOS	STAFF	SMA Ventanas	STAFF	Annual
00555	VENTAS/ COSTO MKT	STAFF	SMA Ventanas	STAFF	Annual
00777	ADMINISTRATIVO	STAFF	SMA Ventanas	STAFF	Annual
00999	CORPORATIVO	CORPORATIVO	SMA Ventanas	CORPORATIVO	Annual
00555	VENTAS/ COSTO MKT	STAFF	Zaidenergy	STAFF	Annual
TSV));

        return array_map(function (string $line) {
            [$code, $name, $purchaseTypeRaw, $companyName, $categoryName, $budgetTypeRaw] = array_pad(explode("\t", $line), 6, '');

            return [
                'code' => $code,
                'name' => $name,
                'purchase_type_raw' => $purchaseTypeRaw,
                'company_name' => $companyName,
                'category_name' => $categoryName,
                'budget_type_raw' => $budgetTypeRaw,
            ];
        }, array_filter($lines));
    }
}
