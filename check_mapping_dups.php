<?php
require 'vendor/autoload.php';
// Simulamos parte del servicio
$sheetMap = [
    'COMERCIAL-MKT' => 'VENTAS/ COSTO MKT',
    'ADMINISTRATIVO-FINANZAS' => 'ADMINISTRATIVO',
    'CONTABILIDAD-FISCAL' => 'CONTABILIDAD',
    'INGRESOS' => 'INGRESOS',
    'SISTEMAS' => 'SISTEMAS',
    'MANTENIMIENTO-OPERACIONES' => 'MANTENIMIENTO',
    'CAPITAL HUMANO' => 'RECURSOS HUMANOS',
    'DIRECCION' => 'DIRECCIÓN',
    'LOGISTICA' => 'LOGISTICA',
    'APLICACIONES Y SOFTWARE' => 'APLICACIONES Y SOFTWARE',
    'AUDITORIA CORP' => 'AUDITORIA',
    'PROYECTOS' => 'PROYECTOS',
    'EXPANSION' => 'EXPANSION 22-26',
    'CORP' => 'CORPORATIVO'
    // ... estaciones ...
];

// Comprobamos si hay valores duplicados en el sheetMap
$counts = array_count_values($sheetMap);
foreach ($counts as $name => $count) {
    if ($count > 1) echo "CC Duplicado en mapeo: $name ($count veces)\n";
}
