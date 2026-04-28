<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'APLICACIONES Y SOFTWARE';

echo "Analizando hoja: $sheetName\n";

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$sheetName]);
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

echo "Rango: A1:{$highestColumn}{$highestRow}\n\n";

// Columnas de meses (Y a AJ)
$monthColumns = ['Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ'];

for ($row = 1; $row <= $highestRow; $row++) {
    $colI = $sheet->getCell('I' . $row)->getValue();
    $colE = $sheet->getCell('E' . $row)->getValue();
    
    if (str_contains(strtolower((string)$colI), 'software') || str_contains(strtolower((string)$colE), 'software')) {
        echo "Fila $row:\n";
        echo "  Col I: $colI\n";
        echo "  Col E: $colE\n";
        
        $total = 0;
        foreach ($monthColumns as $index => $col) {
            $val = $sheet->getCell($col . $row)->getValue();
            echo "  Mes " . ($index+1) . " ($col): " . ($val ?? 0) . "\n";
            $total += (float)$val;
        }
        echo "  TOTAL CALCULADO (Y-AJ): $total\n";
        echo "-----------------------------------\n";
    }
}
