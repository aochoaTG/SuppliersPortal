<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/Presupuesto Anual 2026 DGA.xlsx';
$sheetName = 'SISTEMAS';

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setLoadSheetsOnly([$sheetName]);
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$concepts = [];
for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
    $c = $sheet->getCell('I' . $r)->getValue();
    if ($c) {
        if (isset($concepts[$c])) {
            echo "Concepto repetido: '$c' en filas " . implode(', ', $concepts[$c]) . " y $r\n";
            $concepts[$c][] = $r;
        } else {
            $concepts[$c] = [$r];
        }
    }
}
