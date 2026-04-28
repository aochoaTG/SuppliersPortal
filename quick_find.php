<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'APLICACIONES Y SOFTWARE';
$targetValue = 126494.46;

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$sheetName]);
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$highestRow = $sheet->getHighestRow();
// J es 10, AJ es 36
for ($row = 1; $row <= $highestRow; $row++) {
    for ($col = 10; $col <= 40; $col++) {
        $val = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
        if (is_numeric($val) && abs($val - $targetValue) < 1) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            echo "¡VALOR ENCONTRADO! Fila $row, Columna $colLetter. Valor: $val\n";
        }
    }
}
