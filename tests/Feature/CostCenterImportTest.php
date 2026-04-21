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
        $this->assertStringContainsString(
            'layout_centros_costo.xlsx',
            (string) $response->headers->get('content-disposition')
        );

        $binaryFile = $response->baseResponse->getFile();
        $this->assertNotNull($binaryFile);
        $path = $binaryFile->getPathname();
        $spreadsheet = IOFactory::load($path);

        $headers = $spreadsheet->getSheet(0)->rangeToArray('A1:K1')[0];

        $this->assertSame(CostCenterImportService::HEADERS, $headers);
        $this->assertSame(2, $spreadsheet->getSheetCount());
        $this->assertTrue($spreadsheet->getSheet(1)->getSheetState() !== \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE);
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

        $this->actingAs($authUser)
            ->get(route('cost-centers.import.preview.show'))
            ->assertOk()
            ->assertSee('Listo para importar')
            ->assertSee('CC-100')
            ->assertSee('Centro de prueba');
    }

    public function test_preview_rejects_existing_code_in_database(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        CostCenter::create([
            'code' => 'CC-EXISTE',
            'name' => 'Ya existe',
            'description' => null,
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
                'codigo' => 'CC-EXISTE',
                'nombre' => 'Centro duplicado',
                'descripcion' => '',
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
            ->assertSee('El código ya existe en el catálogo actual.');
    }

    public function test_preview_rejects_duplicate_codes_inside_file(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-DUP',
                'nombre' => 'Centro 1',
                'descripcion' => '',
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
            ->assertSee('El código está repetido dentro del archivo.');
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
            ->assertSee('La fecha de vigencia es obligatoria para "Consumo Libre".')
            ->assertSee('La justificación es obligatoria para FREE_CONSUMPTION.');
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
            'company_id' => $company->id,
            'category_id' => $category->id,
            'responsible_user_id' => $responsible->id,
            'budget_type' => 'FREE_CONSUMPTION',
            'status' => 'ACTIVO',
            'created_by' => $authUser->id,
        ]);
    }

    public function test_confirm_does_not_insert_when_preview_has_errors(): void
    {
        $authUser = User::factory()->create();
        [$company, $category, $responsible] = $this->seedCatalogs();

        $file = $this->buildImportFile([
            [
                'codigo' => 'CC-BLOCK',
                'nombre' => 'Centro 1',
                'descripcion' => '',
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
                'codigo' => 'CC-BLOCK',
                'nombre' => 'Centro 2',
                'descripcion' => '',
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
            ->post(route('cost-centers.import.confirm'))
            ->assertRedirect(route('cost-centers.import.preview.show'));

        $this->assertDatabaseMissing('cost_centers', [
            'code' => 'CC-BLOCK',
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
