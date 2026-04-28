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

echo "Cabeceras de columnas (Fila 1 a 10):\n";
$cols = ['T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ'];

echo "Col | ";
foreach ($cols as $c) echo str_pad($c, 10) . " | ";
echo "\n" . str_repeat("-", 200) . "\n";

for ($r = 1; $r <= 20; $r++) {
    echo str_pad($r, 3) . " | ";
    foreach ($cols as $c) {
        $val = $sheet->getCell($c . $r)->getValue();
        echo str_pad(substr((string)$val, 0, 10), 10) . " | ";
    }
    echo "\n";
}
