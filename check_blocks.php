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

function printContext($sheet, $row) {
    echo "--- Contexto Fila $row ---\n";
    for ($r = $row - 5; $r <= $row; $r++) {
        $a = $sheet->getCell('A' . $r)->getValue();
        $i = $sheet->getCell('I' . $r)->getValue();
        echo "[$r] A: $a | I: $i\n";
    }
}

printContext($sheet, 65);
echo "\n";
printContext($sheet, 182);
