<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetNames = ['SISTEMAS', 'CONTABILIDAD-FISCAL', 'DIRECCION'];

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);

foreach ($sheetNames as $sheetName) {
    echo "--- Hoja: $sheetName ---\n";
    $reader->setLoadSheetsOnly([$sheetName]);
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
        $i = $sheet->getCell('I' . $r)->getValue();
        if (str_contains(strtolower((string)$i), 'total gasto')) {
            echo "[$r] I: $i\n";
        }
    }
    $spreadsheet->disconnectWorksheets();
}
