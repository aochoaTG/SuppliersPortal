<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$markdownPath = $root . '/docs/Matriz_Maestra_Pruebas_Portal_Proveedores.md';
$outputDir = $root . '/docs/exports';

if (! is_file($markdownPath)) {
    fwrite(STDERR, "No se encontro la matriz Markdown en: {$markdownPath}\n");
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0777, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, "No se pudo crear el directorio de salida: {$outputDir}\n");
    exit(1);
}

$content = file($markdownPath, FILE_IGNORE_NEW_LINES);

/**
 * @return array<int, array<int, string>>
 */
function extractMarkdownTable(array $lines, string $anchor): array
{
    $start = null;
    foreach ($lines as $index => $line) {
        if (trim($line) === $anchor) {
            $start = $index + 1;
            break;
        }
    }

    if ($start === null) {
        throw new RuntimeException("No se encontro el ancla de tabla: {$anchor}");
    }

    $table = [];
    for ($i = $start; $i < count($lines); $i++) {
        $line = trim($lines[$i]);

        if ($line === '') {
            if ($table !== []) {
                break;
            }
            continue;
        }

        if (! str_starts_with($line, '|')) {
            if ($table !== []) {
                break;
            }
            continue;
        }

        if (preg_match('/^\|\s*-+/', $line) === 1) {
            continue;
        }

        $table[] = parseMarkdownRow($line);
    }

    return $table;
}

/**
 * @return array<int, string>
 */
function parseMarkdownRow(string $line): array
{
    $trimmed = trim($line);
    $trimmed = trim($trimmed, '|');
    $cells = explode('|', $trimmed);

    return array_map(
        static fn (string $cell): string => trim(str_replace(['`', "\r"], '', $cell)),
        $cells
    );
}

/**
 * @return array<int, string>
 */
function extractBulletList(array $lines, string $anchor): array
{
    $start = null;
    foreach ($lines as $index => $line) {
        if (trim($line) === $anchor) {
            $start = $index + 1;
            break;
        }
    }

    if ($start === null) {
        throw new RuntimeException("No se encontro la lista: {$anchor}");
    }

    $items = [];
    for ($i = $start; $i < count($lines); $i++) {
        $line = trim($lines[$i]);

        if ($line === '') {
            if ($items !== []) {
                break;
            }
            continue;
        }

        if (! str_starts_with($line, '- ')) {
            if ($items !== []) {
                break;
            }
            continue;
        }

        $items[] = substr($line, 2);
    }

    return $items;
}

/**
 * @param array<int, array<int, string>> $rows
 */
function writeCsv(string $path, array $rows): void
{
    $handle = fopen($path, 'wb');
    if ($handle === false) {
        throw new RuntimeException("No se pudo escribir el archivo CSV: {$path}");
    }

    fwrite($handle, "\xEF\xBB\xBF");

    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }

    fclose($handle);
}

/**
 * @param array<int, array<int, string>> $rows
 */
function writeSheet(Spreadsheet $spreadsheet, string $title, array $rows, int $sheetIndex): void
{
    $sheet = $sheetIndex === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet($sheetIndex);
    $sheet->setTitle($title);

    foreach ($rows as $rowIndex => $row) {
        $excelRow = $rowIndex + 1;
        foreach ($row as $colIndex => $value) {
            $excelColumn = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValueExplicit("{$excelColumn}{$excelRow}", $value, DataType::TYPE_STRING);
        }
    }

    if ($rows !== []) {
        $headerColumnCount = count($rows[0]);
        $lastColumn = Coordinate::stringFromColumnIndex($headerColumnCount);
        $headerRange = "A1:{$lastColumn}1";

        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('D9EAF7');
    }

    foreach (range(1, max(array_map(static fn (array $row): int => count($row), $rows))) as $columnIndex) {
        $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
    }
}

$coverageRows = extractMarkdownTable($content, '| Area | Evidencia automatizada localizada | Estado |');
$functionalRows = extractMarkdownTable($content, '| ID | Modulo | Flujo / componente | Nivel | Casos obligatorios a ejecutar | Prioridad | Cobertura actual |');
$nonFunctionalRows = extractMarkdownTable($content, '| ID | Categoria | Alcance | Prueba no funcional obligatoria | Metodo | Criterio de aceptacion | Cobertura actual |');
$fixtures = extractBulletList($content, 'Para que la matriz sea repetible, QA debe preparar al menos estos fixtures:');

$summaryRows = [
    ['Seccion', 'Valor'],
    ['Sistema', 'Portal de Proveedores'],
    ['Version matriz', '1.0'],
    ['Fecha exportacion', date('Y-m-d H:i:s')],
    ['Total casos funcionales', (string) max(count($functionalRows) - 1, 0)],
    ['Total casos no funcionales', (string) max(count($nonFunctionalRows) - 1, 0)],
    ['Total filas cobertura actual', (string) max(count($coverageRows) - 1, 0)],
    ['Total fixtures recomendados', (string) count($fixtures)],
];

$fixturesRows = [['Fixture recomendado']];
foreach ($fixtures as $fixture) {
    $fixturesRows[] = [$fixture];
}

writeCsv($outputDir . '/Matriz_Pruebas_Funcionales.csv', $functionalRows);
writeCsv($outputDir . '/Matriz_Pruebas_No_Funcionales.csv', $nonFunctionalRows);
writeCsv($outputDir . '/Matriz_Pruebas_Cobertura_Actual.csv', $coverageRows);
writeCsv($outputDir . '/Matriz_Pruebas_Fixtures.csv', $fixturesRows);

$spreadsheet = new Spreadsheet();
writeSheet($spreadsheet, 'Resumen', $summaryRows, 0);
writeSheet($spreadsheet, 'Cobertura actual', $coverageRows, 1);
writeSheet($spreadsheet, 'Funcionales', $functionalRows, 2);
writeSheet($spreadsheet, 'No funcionales', $nonFunctionalRows, 3);
writeSheet($spreadsheet, 'Fixtures', $fixturesRows, 4);

$writer = new Xlsx($spreadsheet);
$writer->save($outputDir . '/Matriz_Maestra_Pruebas_Portal_Proveedores.xlsx');

echo "Exportacion completada en: {$outputDir}\n";
