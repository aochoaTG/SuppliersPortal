<?php

namespace App\Services;

use App\Enum\PurchaseType;
use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CostCenterImportService
{
    private const BUDGET_TYPE_OPTIONS = [
        'Presupuesto Anual' => 'ANNUAL',
        'Consumo Libre' => 'FREE_CONSUMPTION',
    ];

    public const HEADERS = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo de compra',
        'empresa',
        'categoria',
        'responsable',
        'tipo_presupuesto',
        'monto_global',
        'fecha_vigencia',
        'justificacion_consumo_libre',
        'estado',
    ];

    private const TEMPLATE_ROW_LIMIT = 500;

    public function downloadTemplateResponse(): BinaryFileResponse
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'cc-layout-');
        $xlsxPath = $tempPath . '.xlsx';
        @unlink($tempPath);

        $writer = new Xlsx($this->buildTemplateSpreadsheet());
        $writer->save($xlsxPath);

        return response()->download($xlsxPath, 'layout_centros_costo.xlsx')->deleteFileAfterSend(true);
    }

    public function buildPreviewFromUpload(UploadedFile $file, int $uploadedBy): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);

        [$headers, $rawRows] = $this->extractRows($sheet);

        $preview = $this->validateRawRows($headers, $rawRows);
        $preview['uploaded_by'] = $uploadedBy;
        $preview['uploaded_at'] = now()->toDateTimeString();
        $preview['original_filename'] = $file->getClientOriginalName();

        return $preview;
    }

    public function confirmPreview(array $preview, int $createdBy): array
    {
        $revalidated = $this->validateRawRows($preview['columns_detected'] ?? [], $preview['raw_rows'] ?? []);

        if (!$revalidated['can_import']) {
            $revalidated['uploaded_by'] = $preview['uploaded_by'] ?? $createdBy;
            $revalidated['uploaded_at'] = $preview['uploaded_at'] ?? now()->toDateTimeString();
            $revalidated['original_filename'] = $preview['original_filename'] ?? null;

            return [
                'success' => false,
                'message' => 'La información cambió o el archivo tiene errores. Revisa el preview antes de confirmar.',
                'preview' => $revalidated,
            ];
        }

        $timestamp = now();
        $payload = collect($revalidated['normalized_rows'])
            ->map(function (array $row) use ($createdBy, $timestamp) {
                return [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'purchase_type' => $row['purchase_type'],
                    'company_id' => $row['company_id'],
                    'category_id' => $row['category_id'],
                    'responsible_user_id' => $row['responsible_user_id'],
                    'budget_type' => $row['budget_type'],
                    'global_amount' => $row['global_amount'],
                    'free_consumption_justification' => $row['free_consumption_justification'],
                    'authorized_by' => null,
                    'authorized_at' => null,
                    'validity_date' => $row['validity_date'],
                    'status' => $row['status'],
                    'created_by' => $createdBy,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })
            ->all();

        DB::transaction(function () use ($payload) {
            CostCenter::insert($payload);
        });

        return [
            'success' => true,
            'inserted' => count($payload),
        ];
    }

    private function buildTemplateSpreadsheet(): Spreadsheet
    {
        $catalogs = $this->catalogs();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setValueBinder(new StringValueBinder());

        $captureSheet = $spreadsheet->getActiveSheet();
        $captureSheet->setTitle('CentrosCosto');
        $captureSheet->fromArray(self::HEADERS, null, 'A1');
        $captureSheet->freezePane('A2');
        $captureSheet->setAutoFilter('A1:L1');

        $captureSheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
        ]);

        $columnWidths = [
            'A' => 18,
            'B' => 30,
            'C' => 40,
            'D' => 18,
            'E' => 28,
            'F' => 24,
            'G' => 34,
            'H' => 22,
            'I' => 18,
            'J' => 18,
            'K' => 44,
            'L' => 18,
        ];

        foreach ($columnWidths as $column => $width) {
            $captureSheet->getColumnDimension($column)->setWidth($width);
        }

        $captureSheet->getStyle('J2:J' . self::TEMPLATE_ROW_LIMIT)
            ->getNumberFormat()
            ->setFormatCode('yyyy-mm-dd');

        $hiddenSheet = new Worksheet($spreadsheet, 'Catalogos');
        $spreadsheet->addSheet($hiddenSheet);
        $hiddenSheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);

        $lists = [
            'A' => $catalogs['companies']->values()->all(),
            'B' => $catalogs['categories']->values()->all(),
            'C' => $catalogs['responsibles']->values()->all(),
            'D' => PurchaseType::values(),
            'E' => array_keys(self::BUDGET_TYPE_OPTIONS),
            'F' => ['ACTIVO', 'INACTIVO'],
        ];

        foreach ($lists as $column => $values) {
            foreach (array_values($values) as $index => $value) {
                $hiddenSheet->setCellValueExplicit(
                    sprintf('%s%d', $column, $index + 1),
                    $value,
                    DataType::TYPE_STRING
                );
            }
        }

        $this->applyListValidation($captureSheet, 'D', 'Catalogos!$D$1:$D$' . count(PurchaseType::cases()));
        $this->applyListValidation($captureSheet, 'E', 'Catalogos!$A$1:$A$' . max(1, $catalogs['companies']->count()));
        $this->applyListValidation($captureSheet, 'F', 'Catalogos!$B$1:$B$' . max(1, $catalogs['categories']->count()));
        $this->applyListValidation($captureSheet, 'G', 'Catalogos!$C$1:$C$' . max(1, $catalogs['responsibles']->count()));
        $this->applyListValidation($captureSheet, 'H', 'Catalogos!$E$1:$E$2');
        $this->applyListValidation($captureSheet, 'L', 'Catalogos!$F$1:$F$2');
        $this->applyBudgetTypeGuidance($captureSheet);

        $captureSheet->setCellValue('A2', '');

        return $spreadsheet;
    }

    private function applyListValidation(Worksheet $sheet, string $column, string $formulaRange): void
    {
        for ($row = 2; $row <= self::TEMPLATE_ROW_LIMIT; $row++) {
            $validation = $sheet->getCell("{$column}{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Selecciona uno de los valores disponibles en la lista.');
            $validation->setFormula1('=' . $formulaRange);
        }
    }

    private function applyBudgetTypeGuidance(Worksheet $sheet): void
    {
        for ($row = 2; $row <= self::TEMPLATE_ROW_LIMIT; $row++) {
            foreach (['I', 'J', 'K'] as $column) {
                $validation = $sheet->getCell("{$column}{$row}")->getDataValidation();
                $validation->setShowInputMessage(true);
                $validation->setPromptTitle('Campo condicionado');
                $validation->setPrompt('Llena este campo solo cuando el tipo de presupuesto sea "Consumo Libre".');
            }
        }

        $sheet->getStyle('H2:H' . self::TEMPLATE_ROW_LIMIT)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);

        $sheet->getStyle('I2:K' . self::TEMPLATE_ROW_LIMIT)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);
    }

    private function extractRows(Worksheet $sheet): array
    {
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = $sheet->getHighestDataRow();

        $headers = [];
        for ($column = 1; $column <= $highestColumn; $column++) {
            $coordinate = Coordinate::stringFromColumnIndex($column) . '1';
            $headers[] = $this->normalizeHeaderValue((string) $sheet->getCell($coordinate)->getFormattedValue());
        }

        $rawRows = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $values = [];
            $hasData = false;

            foreach (self::HEADERS as $index => $header) {
                $columnLetter = Coordinate::stringFromColumnIndex($index + 1);
                $cell = $sheet->getCell("{$columnLetter}{$row}");
                $value = $cell->getValue();

                if ($header === 'fecha_vigencia' && is_numeric($value) && ExcelDate::isDateTime($cell)) {
                    $value = ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
                }

                if (is_string($value)) {
                    $value = trim($value);
                }

                if ($value !== null && $value !== '') {
                    $hasData = true;
                }

                $values[$header] = $value;
            }

            if ($hasData) {
                $rawRows[] = [
                    'row_number' => $row,
                    'values' => $values,
                ];
            }
        }

        return [$headers, $rawRows];
    }

    private function validateRawRows(array $headers, array $rawRows): array
    {
        $errors = $this->validateHeaders($headers);
        $normalizedRows = [];
        $validRows = [];
        $catalogs = $this->catalogMaps();

        if (empty($rawRows)) {
            $errors[] = $this->errorEntry(0, 'archivo', 'El archivo no contiene filas de datos.');
        }

        $duplicateCodes = $this->duplicateCodesInFile($rawRows);
        $existingCodes = $this->existingCodesInDatabase($rawRows);

        foreach ($rawRows as $row) {
            $rowResult = $this->validateSingleRow(
                $row['row_number'],
                $row['values'],
                $catalogs,
                $duplicateCodes,
                $existingCodes
            );

            if (!empty($rowResult['errors'])) {
                $errors = [...$errors, ...$rowResult['errors']];
                continue;
            }

            $normalizedRows[] = $rowResult['normalized'];
            $validRows[] = [
                'row_number' => $row['row_number'],
                'values' => $row['values'],
            ];
        }

        $errorRows = collect($errors)
            ->pluck('row')
            ->filter(fn($row) => $row > 0)
            ->unique()
            ->count();

        return [
            'columns_detected' => $headers,
            'expected_columns' => self::HEADERS,
            'raw_rows' => $rawRows,
            'valid_rows' => $validRows,
            'normalized_rows' => $normalizedRows,
            'errors' => $errors,
            'total_rows' => count($rawRows),
            'valid_rows_count' => count($normalizedRows),
            'error_rows_count' => $errorRows,
            'can_import' => empty($errors) && !empty($normalizedRows),
        ];
    }

    private function validateHeaders(array $headers): array
    {
        if ($headers === self::HEADERS) {
            return [];
        }

        $errors = [];
        $missing = array_values(array_diff(self::HEADERS, $headers));
        $unexpected = array_values(array_diff($headers, self::HEADERS));

        if (!empty($missing)) {
            $errors[] = $this->errorEntry(1, 'encabezados', 'Faltan columnas requeridas: ' . implode(', ', $missing) . '.');
        }

        if (!empty($unexpected)) {
            $errors[] = $this->errorEntry(1, 'encabezados', 'Se detectaron columnas no esperadas: ' . implode(', ', $unexpected) . '.');
        }

        if (empty($errors)) {
            $errors[] = $this->errorEntry(1, 'encabezados', 'El orden o nombres de columnas no coincide con la plantilla vigente.');
        }

        return $errors;
    }

    private function validateSingleRow(
        int $rowNumber,
        array $values,
        array $catalogs,
        array $duplicateCodes,
        array $existingCodes
    ): array {
        $errors = [];

        $code = $this->sanitizeText($values['codigo']);
        $name = $this->sanitizeText($values['nombre']);
        $description = $this->sanitizeNullableText($values['descripcion']);
        $purchaseType = $this->sanitizeText($values['tipo de compra']);
        $companyLabel = $this->sanitizeText($values['empresa']);
        $categoryLabel = $this->sanitizeText($values['categoria']);
        $responsibleLabel = $this->sanitizeText($values['responsable']);
        $budgetTypeLabel = $this->sanitizeText($values['tipo_presupuesto']);
        $budgetType = $this->normalizeBudgetType($budgetTypeLabel);
        $globalAmountRaw = $values['monto_global'];
        $validityDateRaw = $values['fecha_vigencia'];
        $justification = $this->sanitizeNullableText($values['justificacion_consumo_libre']);
        $status = $this->sanitizeText($values['estado']);

        if ($code === '') {
            $errors[] = $this->errorEntry($rowNumber, 'codigo', 'El código es obligatorio.');
        } elseif (mb_strlen($code) > 50) {
            $errors[] = $this->errorEntry($rowNumber, 'codigo', 'El código no debe exceder 50 caracteres.');
        } elseif (isset($duplicateCodes[mb_strtolower($code)])) {
            $errors[] = $this->errorEntry($rowNumber, 'codigo', 'El código está repetido dentro del archivo.');
        } elseif (isset($existingCodes[mb_strtolower($code)])) {
            $errors[] = $this->errorEntry($rowNumber, 'codigo', 'El código ya existe en el catálogo actual.');
        }

        if ($name === '') {
            $errors[] = $this->errorEntry($rowNumber, 'nombre', 'El nombre es obligatorio.');
        } elseif (mb_strlen($name) > 200) {
            $errors[] = $this->errorEntry($rowNumber, 'nombre', 'El nombre no debe exceder 200 caracteres.');
        }

        if ($description !== null && mb_strlen($description) > 500) {
            $errors[] = $this->errorEntry($rowNumber, 'descripcion', 'La descripción no debe exceder 500 caracteres.');
        }

        if (!in_array($purchaseType, PurchaseType::values(), true)) {
            $errors[] = $this->errorEntry($rowNumber, 'tipo de compra', 'El tipo de compra debe ser Gasto Operativo, Gasto Staff o Gasto Corporativo.');
        }

        $company = $catalogs['companies'][$companyLabel] ?? null;
        if ($companyLabel === '') {
            $errors[] = $this->errorEntry($rowNumber, 'empresa', 'La empresa es obligatoria.');
        } elseif (!$company) {
            $errors[] = $this->errorEntry($rowNumber, 'empresa', 'La empresa no existe o ya no está disponible.');
        }

        $category = $catalogs['categories'][$categoryLabel] ?? null;
        if ($categoryLabel === '') {
            $errors[] = $this->errorEntry($rowNumber, 'categoria', 'La categoría es obligatoria.');
        } elseif (!$category) {
            $errors[] = $this->errorEntry($rowNumber, 'categoria', 'La categoría no existe o ya no está disponible.');
        }

        $responsible = $catalogs['responsibles'][$responsibleLabel] ?? null;
        if ($responsibleLabel === '') {
            $errors[] = $this->errorEntry($rowNumber, 'responsable', 'El responsable es obligatorio.');
        } elseif (!$responsible) {
            $errors[] = $this->errorEntry($rowNumber, 'responsable', 'El responsable no existe o ya no está disponible.');
        }

        if ($budgetType === null) {
            $errors[] = $this->errorEntry($rowNumber, 'tipo_presupuesto', 'El tipo de presupuesto debe ser "Presupuesto Anual" o "Consumo Libre".');
        }

        if (!in_array($status, ['ACTIVO', 'INACTIVO'], true)) {
            $errors[] = $this->errorEntry($rowNumber, 'estado', 'El estado debe ser ACTIVO o INACTIVO.');
        }

        $globalAmount = null;
        $validityDate = null;

        if ($budgetType === 'FREE_CONSUMPTION') {
            $globalAmount = $this->parseAmount($globalAmountRaw);
            if ($globalAmountRaw === null || $globalAmountRaw === '') {
                $errors[] = $this->errorEntry($rowNumber, 'monto_global', 'El monto global es obligatorio para "Consumo Libre".');
            } elseif ($globalAmount === null || $globalAmount <= 0) {
                $errors[] = $this->errorEntry($rowNumber, 'monto_global', 'El monto global debe ser numérico y mayor a 0.');
            }

            $validityDate = $this->parseDate($validityDateRaw);
            if ($validityDateRaw === null || $validityDateRaw === '') {
                $errors[] = $this->errorEntry($rowNumber, 'fecha_vigencia', 'La fecha de vigencia es obligatoria para "Consumo Libre".');
            } elseif ($validityDate === null) {
                $errors[] = $this->errorEntry($rowNumber, 'fecha_vigencia', 'La fecha de vigencia no tiene un formato válido.');
            } elseif (Carbon::parse($validityDate)->lt(today())) {
                $errors[] = $this->errorEntry($rowNumber, 'fecha_vigencia', 'La fecha de vigencia debe ser igual o posterior a hoy.');
            }

            if ($justification === null || $justification === '') {
                $errors[] = $this->errorEntry($rowNumber, 'justificacion_consumo_libre', 'La justificación es obligatoria para FREE_CONSUMPTION.');
            } elseif (mb_strlen($justification) < 10 || mb_strlen($justification) > 1000) {
                $errors[] = $this->errorEntry($rowNumber, 'justificacion_consumo_libre', 'La justificación debe tener entre 10 y 1000 caracteres.');
            }
        }

        if (!empty($errors)) {
            return [
                'errors' => $errors,
                'normalized' => null,
            ];
        }

        return [
            'errors' => [],
            'normalized' => [
                'row_number' => $rowNumber,
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'purchase_type' => $purchaseType,
                'company_id' => $company->id,
                'company_label' => $companyLabel,
                'category_id' => $category->id,
                'category_label' => $categoryLabel,
                'responsible_user_id' => $responsible->id,
                'responsible_label' => $responsibleLabel,
                'budget_type' => $budgetType,
                'global_amount' => $budgetType === 'FREE_CONSUMPTION' ? $globalAmount : null,
                'validity_date' => $budgetType === 'FREE_CONSUMPTION' ? $validityDate : null,
                'free_consumption_justification' => $budgetType === 'FREE_CONSUMPTION' ? $justification : null,
                'status' => $status,
            ],
        ];
    }

    private function duplicateCodesInFile(array $rawRows): array
    {
        $counts = [];

        foreach ($rawRows as $row) {
            $code = $this->sanitizeText($row['values']['codigo'] ?? null);
            if ($code === '') {
                continue;
            }

            $key = mb_strtolower($code);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return array_filter($counts, fn($count) => $count > 1);
    }

    private function existingCodesInDatabase(array $rawRows): array
    {
        $codes = collect($rawRows)
            ->map(fn(array $row) => $this->sanitizeText($row['values']['codigo'] ?? null))
            ->filter()
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return [];
        }

        return CostCenter::query()
            ->whereIn('code', $codes->all())
            ->pluck('code')
            ->mapWithKeys(fn(string $code) => [mb_strtolower($code) => true])
            ->all();
    }

    private function catalogMaps(): array
    {
        return [
            'companies' => Company::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name'])
                ->mapWithKeys(fn(Company $company) => [$this->companyLabel($company) => $company])
                ->all(),
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->mapWithKeys(fn(Category $category) => [$this->categoryLabel($category) => $category])
                ->all(),
            'responsibles' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->mapWithKeys(fn(User $user) => [$this->responsibleLabel($user) => $user])
                ->all(),
        ];
    }

    private function catalogs(): array
    {
        return [
            'companies' => Company::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name'])
                ->map(fn(Company $company) => $this->companyLabel($company)),
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn(Category $category) => $this->categoryLabel($category)),
            'responsibles' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn(User $user) => $this->responsibleLabel($user)),
        ];
    }

    private function companyLabel(Company $company): string
    {
        return "{$company->code} - {$company->name}";
    }

    private function categoryLabel(Category $category): string
    {
        return $category->name;
    }

    private function responsibleLabel(User $user): string
    {
        return "{$user->name} <{$user->email}>";
    }

    private function normalizeBudgetType(mixed $value): ?string
    {
        $text = $this->sanitizeText($value);

        if ($text === '') {
            return null;
        }

        return self::BUDGET_TYPE_OPTIONS[$text] ?? match ($text) {
            'ANNUAL', 'FREE_CONSUMPTION' => $text,
            default => null,
        };
    }

    private function sanitizeText(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function sanitizeNullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }

    private function parseAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $normalized = str_replace([',', '$', ' '], '', (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $stringValue = trim((string) $value);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $stringValue);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($stringValue)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeHeaderValue(string $header): string
    {
        return trim(mb_strtolower($header));
    }

    private function errorEntry(int $row, string $column, string $message): array
    {
        return [
            'row' => $row,
            'column' => $column,
            'message' => $message,
        ];
    }
}
