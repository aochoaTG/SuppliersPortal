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
    if (in_array($sheetName, ['Concentrado', 'Resumen', 'TOTAL ESTACIONES', 'ER ACUMULADO'])) continue;
    
    $reader->setLoadSheetsOnly([$sheetName]);
    try {
        $spreadsheet = $reader->load($filePath);
    } catch (\Exception $e) { continue; }
    
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    
    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 1; $col <= 60; $col++) {
            $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row);
            $val = $cell->getValue();
            
            if (is_numeric($val) && abs((float)$val - $targetValue) < 1) {
                echo "¡ENCONTRADO (LITERAL) en Hoja: $sheetName, Fila: $row, Col: " . Coordinate::stringFromColumnIndex($col) . "!\n";
            }
        }
    }
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
}
