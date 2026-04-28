<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'Lerdo';

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$sheetName]);
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();

echo "--- Hoja: $sheetName ---\n";
for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
    $i = $sheet->getCell('I' . $r)->getValue();
    if (str_contains(strtolower((string)$i), 'total gasto')) {
        echo "[$r] I: $i\n";
    }
}
