<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'APLICACIONES Y SOFTWARE';

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$sheetName]);
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$monthCols = [
    'Y' => 1, 'Z' => 2, 'AA' => 3, 'AB' => 4, 'AC' => 5, 'AD' => 6,
    'AE' => 7, 'AF' => 8, 'AG' => 9, 'AH' => 10, 'AI' => 11, 'AJ' => 12
];

for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
    $concept = $sheet->getCell('I' . $row)->getValue();
    if (str_contains(strtolower((string)$concept), 'software')) {
        echo "Fila $row: $concept\n";
        foreach ($monthCols as $col => $m) {
            // Usamos getCalculatedValue() como el servicio
            try {
                $val = $sheet->getCell($col . $row)->getCalculatedValue();
            } catch (\Exception $e) {
                $val = "ERROR: " . $e->getMessage();
            }
            echo "  Mes $m ($col): $val\n";
        }
        echo "-----------------------------------\n";
    }
}
