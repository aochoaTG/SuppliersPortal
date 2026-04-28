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

echo "Fila 65: Col A='" . $sheet->getCell('A65')->getValue() . "', Col I='" . $sheet->getCell('I65')->getValue() . "'\n";
echo "Fila 182: Col A='" . $sheet->getCell('A182')->getValue() . "', Col I='" . $sheet->getCell('I182')->getValue() . "'\n";

// Buscar la sección (gastos de operación, etc)
function findSection($sheet, $row) {
    for ($r = $row; $r >= 1; $r--) {
        $val = $sheet->getCell('A' . $r)->getValue();
        if ($val && preg_match('/^[a-z]\s+/i', (string)$val)) return $val;
    }
    return "No encontrada";
}

echo "Sección Fila 65: " . findSection($sheet, 65) . "\n";
echo "Sección Fila 182: " . findSection($sheet, 182) . "\n";
