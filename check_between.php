<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'APLICACIONES Y SOFTWARE';

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$sheetName]);
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();

for ($r = 120; $r <= 135; $r++) {
    $a = $sheet->getCell('A' . $r)->getValue();
    $i = $sheet->getCell('I' . $r)->getValue();
    $j = $sheet->getCell('J' . $r)->getCalculatedValue();
    echo "[$r] A: $a | I: $i | J: $j\n";
}
