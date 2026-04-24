<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use App\Services\CostCenterImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CostCenterImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_cost_center_import_template(): void
    {
        $user = User::factory()->create();
        $this->seedCatalogs();

        $response = $this->actingAs($user)->get(route('cost-centers.import.template'));

        $response->assertOk();
        $this->assertStringContainsString('layout_centros_costo.xlsx', (string) $response->headers->get('content-disposition'));

        $binaryFile = $response->baseResponse->getFile();
        $this->assertNotNull($binaryFile);

        $spreadsheet = IOFactory::load($binaryFile->getPathname());
        $headers = $spreadsheet->getSheet(0)->rangeToArray('A1:L1')[0];

        $this->assertSame(CostCenterImportService::HEADERS, $headers);
        $this->assertSame(2, $spreadsheet->getSheetCount());
    }

    public function test_preview_accepts_valid_annual_cost_center_file(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-100',
                'nombre' => 'Centro de prueba',
                'descripcion' => 'Carga masiva annual',
                'tipo de compra' => 'Gasto Operativo',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Presupuesto Anual',
                'monto_global' => '',
                'fecha_vigencia' => '',
                'justificacion_consumo_libre' => '',
                'estado' => 'ACTIVO',
            ],
        ]);

        $this->actingAs($authUser)
            ->post(route('cost-centers.import.preview'), ['excel_file' => $file])
            ->assertRedirect(route('cost-centers.import.preview.show'));

        $preview = session('cost_center_import.preview');

        $this->assertNotNull($preview);
        $this->assertTrue($preview['can_import']);
        $this->assertSame('CC-100', $preview['valid_rows'][0]['values']['codigo']);
        $this->assertSame('Gasto Operativo', $preview['valid_rows'][0]['values']['tipo de compra']);
    }

    public function test_preview_allows_duplicate_codes_in_database(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        CostCenter::create([
            'code' => 'CC-DUP',
            'name' => 'Ya existe',
            'description' => null,
            'purchase_type' => 'Gasto Staff',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
            'budget_type' => 'ANNUAL',
            'global_amount' => null,
            'free_consumption_justification' => null,
            'authorized_by' => null,
            'authorized_at' => null,
            'validity_date' => null,
            'status' => 'ACTIVO',
            'created_by' => $authUser->id,
            'updated_by' => null,
            'deleted_by' => null,
        ]);

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-DUP',
                'nombre' => 'Centro duplicado permitido',
                'descripcion' => '',
                'tipo de compra' => 'Gasto Staff',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Presupuesto Anual',
                'monto_global' => '',
                'fecha_vigencia' => '',
                'justificacion_consumo_libre' => '',
                'estado' => 'ACTIVO',
            ],
        ]);

        $this->actingAs($authUser)
            ->post(route('cost-centers.import.preview'), ['excel_file' => $file])
            ->assertRedirect(route('cost-centers.import.preview.show'));

        $preview = session('cost_center_import.preview');

        $this->assertTrue($preview['can_import']);
        $this->assertSame(1, $preview['valid_rows_count']);
    }

    public function test_preview_allows_duplicate_codes_inside_file(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-DUP',
                'nombre' => 'Centro 1',
                'descripcion' => '',
                'tipo de compra' => 'Gasto Operativo',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Presupuesto Anual',
                'monto_global' => '',
                'fecha_vigencia' => '',
                'justificacion_consumo_libre' => '',
                'estado' => 'ACTIVO',
            ],
            [
                'codigo' => 'CC-DUP',
                'nombre' => 'Centro 2',
                'descripcion' => '',
                'tipo de compra' => 'Gasto Operativo',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Presupuesto Anual',
                'monto_global' => '',
                'fecha_vigencia' => '',
                'justificacion_consumo_libre' => '',
                'estado' => 'ACTIVO',
            ],
        ]);

        $this->actingAs($authUser)
            ->post(route('cost-centers.import.preview'), ['excel_file' => $file])
            ->assertRedirect(route('cost-centers.import.preview.show'));

        $preview = session('cost_center_import.preview');

        $this->assertTrue($preview['can_import']);
        $this->assertSame(2, $preview['valid_rows_count']);
    }

    public function test_preview_requires_free_consumption_fields(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-FREE',
                'nombre' => 'Centro libre',
                'descripcion' => '',
                'tipo de compra' => 'Gasto Corporativo',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Consumo Libre',
                'monto_global' => '',
                'fecha_vigencia' => '',
                'justificacion_consumo_libre' => '',
                'estado' => 'ACTIVO',
            ],
        ]);

        $this->actingAs($authUser)->post(route('cost-centers.import.preview'), ['excel_file' => $file]);

        $this->actingAs($authUser)
            ->get(route('cost-centers.import.preview.show'))
            ->assertSee('El monto global es obligatorio para "Consumo Libre".')
            ->assertSee('La fecha de vigencia es obligatoria para "Consumo Libre".');
    }

    public function test_preview_rejects_invalid_purchase_type(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-TIPO',
                'nombre' => 'Centro invalido',
                'descripcion' => '',
                'tipo de compra' => 'Otro',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Presupuesto Anual',
                'monto_global' => '',
                'fecha_vigencia' => '',
                'justificacion_consumo_libre' => '',
                'estado' => 'ACTIVO',
            ],
        ]);

        $this->actingAs($authUser)->post(route('cost-centers.import.preview'), ['excel_file' => $file]);

        $this->actingAs($authUser)
            ->get(route('cost-centers.import.preview.show'))
            ->assertSee('El tipo de compra debe ser Gasto Operativo, Gasto Staff o Gasto Corporativo.');
    }

    public function test_confirm_import_inserts_valid_rows(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-INSERT',
                'nombre' => 'Centro insertado',
                'descripcion' => 'Desde Excel',
                'tipo de compra' => 'Gasto Corporativo',
                'empresa' => "{$company->code} - {$company->name}",
                'categoria' => $category->name,
                'responsable' => "{$responsible->name} <{$responsible->email}>",
                'tipo_presupuesto' => 'Consumo Libre',
                'monto_global' => '5000',
                'fecha_vigencia' => now()->addDays(10)->format('Y-m-d'),
                'justificacion_consumo_libre' => 'Proyecto temporal con control especial',
                'estado' => 'ACTIVO',
            ],
        ]);

        $this->actingAs($authUser)->post(route('cost-centers.import.preview'), ['excel_file' => $file]);
        $this->actingAs($authUser)
            ->post(route('cost-centers.import.confirm'))
            ->assertRedirect(route('cost-centers.index'));

        $this->assertDatabaseHas('cost_centers', [
            'code' => 'CC-INSERT',
            'name' => 'Centro insertado',
            'purchase_type' => 'Gasto Corporativo',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
        ]);
    }

    private function seedCatalogs(): array
    {
        $company = Company::create([
            'code' => 'TG01',
            'name' => 'TotalGas Test',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'OPERACIONES',
            'description' => 'Operaciones',
            'is_active' => true,
        ]);

        $responsible = User::factory()->create([
            'name' => 'Maria Responsable',
            'email' => 'maria.responsable@example.com',
            'is_active' => true,
        ]);

        return [$company, $category, $responsible];
    }

    private function buildImportFile(array $rows): \Illuminate\Http\UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(CostCenterImportService::HEADERS, null, 'A1');

        $currentRow = 2;
        foreach ($rows as $row) {
            $orderedRow = [];
            foreach (CostCenterImportService::HEADERS as $header) {
                $orderedRow[] = $row[$header] ?? '';
            }
            $sheet->fromArray($orderedRow, null, "A{$currentRow}");
            $currentRow++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'cc-import-test-');
        $xlsxPath = $tempPath . '.xlsx';
        @unlink($tempPath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxPath);

        return new \Illuminate\Http\UploadedFile(
            $xlsxPath,
            'cost_centers_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
