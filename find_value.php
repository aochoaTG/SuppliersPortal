<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'APLICACIONES Y SOFTWARE';
$targetValue = 126494.46;

$reader = IOFactory::createReader('Xlsx');
// NO usamos setReadDataOnly(true) para poder ver las fórmulas si es necesario, 
// pero usaremos getCalculatedValue() para el valor.
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getSheetByName($sheetName);

$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

echo "Buscando valor $targetValue en $sheetName...\n";

for ($row = 1; $row <= $highestRow; $row++) {
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, $row);
        $val = $cell->getCalculatedValue();
        
        if (is_numeric($val) && abs($val - $targetValue) < 1) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            echo "¡ENCONTRADO! Fila $row, Columna $colLetter ($col). Valor: $val\n";
            echo "Contenido celda A$row: " . $sheet->getCell('A' . $row)->getValue() . "\n";
            echo "Contenido celda I$row: " . $sheet->getCell('I' . $row)->getValue() . "\n";
        }
    }
}
