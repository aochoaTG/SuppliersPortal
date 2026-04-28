<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$targetValue = 126494.46;

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$sheets = $reader->listWorksheetNames($filePath);

foreach ($sheets as $sheetName) {
    if (in_array($sheetName, ['Concentrado', 'Resumen', 'TOTAL ESTACIONES'])) continue;
    
    $reader->setLoadSheetsOnly([$sheetName]);
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    
    for ($row = 1; $row <= $highestRow; $row++) {
        // Buscamos en un rango amplio de columnas
        for ($col = 1; $col <= 50; $col++) {
            $val = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getCalculatedValue();
            if (is_numeric($val) && abs($val - $targetValue) < 1) {
                echo "¡ENCONTRADO en Hoja: $sheetName, Fila: $row, Col: " . Coordinate::stringFromColumnIndex($col) . "!\n";
                echo "  Concepto (Col I): " . $sheet->getCell('I' . $row)->getValue() . "\n";
            }
        }
    }
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
}
