# Generador CFDI de Prueba — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crear una sección exclusiva para superadmin que genera XML CFDI 4.0 y PDF estilo PAC descargables para pruebas, sin persistencia en base de datos.

**Architecture:** Tres rutas bajo `/tools` (GET form + POST xml + POST pdf) atendidas por `CfdiGeneratorController` que delega la generación a `CfdiGeneratorService`. El servicio construye el XML con `DOMDocument` y el PDF con DomPDF vía un Blade template. Nada se guarda en disco ni en DB. Los dos botones de descarga cambian el `action` del formulario vía JS antes de hacer submit.

**Tech Stack:** Laravel 10+, PHP `DOMDocument`, `barryvdh/laravel-dompdf` (ya instalado como facade `\PDF`), Bootstrap/Zircos layout, Spatie Laravel Permission (`@role` directive).

---

### Task 1: Registrar rutas

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Agregar grupo de rutas** en `routes/web.php`. Insertar justo antes del bloque `role:superadmin|accounting|general_director` (línea ~533), después del bloque `role:superadmin` existente:

```php
// ============================================================================
//  Tools (superadmin)
// ============================================================================
use App\Http\Controllers\Tools\CfdiGeneratorController;

Route::middleware(['auth', 'lock', 'role:superadmin'])->prefix('tools')->name('tools.')->group(function () {
    Route::get('cfdi-generator', [CfdiGeneratorController::class, 'form'])->name('cfdi.form');
    Route::post('cfdi-generator/xml', [CfdiGeneratorController::class, 'downloadXml'])->name('cfdi.xml');
    Route::post('cfdi-generator/pdf', [CfdiGeneratorController::class, 'downloadPdf'])->name('cfdi.pdf');
});
```

- [ ] **Step 2: Verificar que las rutas están registradas**

```bash
php artisan route:list --name=tools
```

Expected: 3 filas con `tools.cfdi.form`, `tools.cfdi.xml`, `tools.cfdi.pdf`.

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat(tools): register CFDI generator routes"
```

---

### Task 2: CfdiGeneratorService — buildXml()

**Files:**
- Create: `app/Services/CfdiGeneratorService.php`
- Create: `tests/Unit/CfdiGeneratorServiceXmlTest.php`

- [ ] **Step 1: Escribir el test que falla**

Crea `tests/Unit/CfdiGeneratorServiceXmlTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Services\CfdiGeneratorService;
use PHPUnit\Framework\TestCase;

class CfdiGeneratorServiceXmlTest extends TestCase
{
    private CfdiGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CfdiGeneratorService();
    }

    private function baseData(): array
    {
        return [
            'rfcEmisor'               => 'AAA010101AAA',
            'nombreEmisor'            => 'Proveedor SA de CV',
            'regimenFiscal'           => '601',
            'rfcReceptor'             => 'TGT010101TGT',
            'nombreReceptor'          => 'Total Gas SA',
            'domicilioFiscalReceptor' => '64000',
            'regimenFiscalReceptor'   => '601',
            'usoCFDI'                 => 'G03',
            'serie'                   => 'A',
            'folio'                   => '1',
            'fecha'                   => '2026-05-19T10:00',
            'formaPago'               => '03',
            'metodoPago'              => 'PUE',
            'moneda'                  => 'MXN',
            'claveProdServ'           => '84111506',
            'claveUnidad'             => 'E48',
            'descripcion'             => 'Servicios profesionales',
            'cantidad'                => 1,
            'valorUnitario'           => 1000,
            'tasaIVA'                 => '0.16',
            'subtotal'                => 1000.00,
            'iva'                     => 160.00,
            'total'                   => 1160.00,
            'ret_enabled'             => [],
            'ret_tasa'                => [],
            'ret_impuesto'            => [],
        ];
    }

    public function test_buildXml_returns_valid_xml_string(): void
    {
        $xml = $this->service->buildXml($this->baseData());

        $this->assertIsString($xml);
        $this->assertStringStartsWith('<?xml', $xml);
    }

    public function test_buildXml_contains_emisor_rfc(): void
    {
        $xml = $this->service->buildXml($this->baseData());

        $this->assertStringContainsString('Rfc="AAA010101AAA"', $xml);
    }

    public function test_buildXml_contains_receptor_rfc(): void
    {
        $xml = $this->service->buildXml($this->baseData());

        $this->assertStringContainsString('Rfc="TGT010101TGT"', $xml);
    }

    public function test_buildXml_contains_uuid_in_tfd(): void
    {
        $xml = $this->service->buildXml($this->baseData());

        $this->assertMatchesRegularExpression('/UUID="[0-9a-f-]{36}"/i', $xml);
    }

    public function test_buildXml_omits_retenciones_when_none_selected(): void
    {
        $xml = $this->service->buildXml($this->baseData());

        $this->assertStringNotContainsString('cfdi:Retenciones', $xml);
        $this->assertStringNotContainsString('TotalImpuestosRetenidos', $xml);
    }

    public function test_buildXml_includes_retenciones_when_selected(): void
    {
        $data                 = $this->baseData();
        $data['ret_enabled']  = ['ISR-HON'];
        $data['ret_tasa']     = ['ISR-HON' => 0.10];
        $data['ret_impuesto'] = ['ISR-HON' => '001'];
        $data['total']        = 1060.00;

        $xml = $this->service->buildXml($data);

        $this->assertStringContainsString('cfdi:Retenciones', $xml);
        $this->assertStringContainsString('TotalImpuestosRetenidos="100.00"', $xml);
        $this->assertStringContainsString('Impuesto="001"', $xml);
    }

    public function test_buildXml_groups_retenciones_at_comprobante_level(): void
    {
        $data                 = $this->baseData();
        $data['ret_enabled']  = ['ISR-ARR', 'ISR-HON'];
        $data['ret_tasa']     = ['ISR-ARR' => 0.10, 'ISR-HON' => 0.10];
        $data['ret_impuesto'] = ['ISR-ARR' => '001', 'ISR-HON' => '001'];
        $data['total']        = 960.00;

        $xml = $this->service->buildXml($data);

        $this->assertStringContainsString('TotalImpuestosRetenidos="200.00"', $xml);
    }

    public function test_buildXml_appends_seconds_to_fecha(): void
    {
        $xml = $this->service->buildXml($this->baseData());

        $this->assertStringContainsString('Fecha="2026-05-19T10:00:00"', $xml);
    }
}
```

- [ ] **Step 2: Verificar que el test falla**

```bash
php artisan test tests/Unit/CfdiGeneratorServiceXmlTest.php
```

Expected: error `Class "App\Services\CfdiGeneratorService" not found`

- [ ] **Step 3: Crear el servicio** `app/Services/CfdiGeneratorService.php`:

```php
<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;

class CfdiGeneratorService
{
    public const RETENCIONES_CATALOG = [
        'ISR-ARR' => ['nombre' => 'ISR Arrendamiento',            'impuesto' => '001', 'tasa' => 0.10,     'variable' => false, 'no_cfdi' => false],
        'ISR-DIV' => ['nombre' => 'ISR Dividendos',               'impuesto' => '001', 'tasa' => 0.10,     'variable' => false, 'no_cfdi' => false],
        'ISR-EXT' => ['nombre' => 'ISR Extranjeros',              'impuesto' => '001', 'tasa' => 0.25,     'variable' => true,  'no_cfdi' => false],
        'ISR-HON' => ['nombre' => 'ISR Honorarios',               'impuesto' => '001', 'tasa' => 0.10,     'variable' => false, 'no_cfdi' => false],
        'ISR-INT' => ['nombre' => 'ISR Intereses',                'impuesto' => '001', 'tasa' => 0.0015,   'variable' => true,  'no_cfdi' => false],
        'ISR-RES' => ['nombre' => 'ISR RESICO PF',                'impuesto' => '001', 'tasa' => 0.0125,   'variable' => false, 'no_cfdi' => false],
        'ISR-SUE' => ['nombre' => 'ISR Sueldos',                  'impuesto' => '001', 'tasa' => 0.00,     'variable' => true,  'no_cfdi' => true],
        'IVA-ARR' => ['nombre' => 'IVA Arrendamiento',            'impuesto' => '002', 'tasa' => 0.106667, 'variable' => false, 'no_cfdi' => false],
        'IVA-COM' => ['nombre' => 'IVA Comisionistas',            'impuesto' => '002', 'tasa' => 0.106667, 'variable' => false, 'no_cfdi' => false],
        'IVA-DES' => ['nombre' => 'IVA Desperdicios',             'impuesto' => '002', 'tasa' => 0.16,     'variable' => false, 'no_cfdi' => false],
        'IVA-DIG' => ['nombre' => 'IVA Plataformas digitales',    'impuesto' => '002', 'tasa' => 0.014,    'variable' => true,  'no_cfdi' => false],
        'IVA-ESP' => ['nombre' => 'IVA Servicios especializados', 'impuesto' => '002', 'tasa' => 0.06,     'variable' => false, 'no_cfdi' => false],
        'IVA-HON' => ['nombre' => 'IVA Honorarios',               'impuesto' => '002', 'tasa' => 0.106667, 'variable' => false, 'no_cfdi' => false],
        'IVA-TRA' => ['nombre' => 'IVA Transporte',               'impuesto' => '002', 'tasa' => 0.04,     'variable' => false, 'no_cfdi' => false],
    ];

    public function buildXml(array $data): string
    {
        $uuid     = (string) Str::uuid();
        $subtotal = number_format((float) $data['subtotal'], 2, '.', '');
        $iva      = number_format((float) $data['iva'], 2, '.', '');
        $total    = number_format((float) $data['total'], 2, '.', '');
        $tasaIVA  = number_format((float) $data['tasaIVA'], 6, '.', '');
        $cantidad = number_format((float) $data['cantidad'], 6, '.', '');
        $valUnit  = number_format((float) $data['valorUnitario'], 2, '.', '');
        $fecha    = $this->normalizeFecha($data['fecha']);

        $retenciones   = $this->resolveRetenciones($data, (float) $data['subtotal']);
        $totalRetenido = number_format(
            collect($retenciones)->sum(fn($r) => (float) $r['importe']), 2, '.', ''
        );

        $ns    = 'http://www.sat.gob.mx/cfd/4';
        $nsTfd = 'http://www.sat.gob.mx/TimbreFiscalDigital';
        $nsXsi = 'http://www.w3.org/2001/XMLSchema-instance';

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $comp = $dom->createElementNS($ns, 'cfdi:Comprobante');
        $comp->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:tfd', $nsTfd);
        $comp->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', $nsXsi);
        $comp->setAttribute('xsi:schemaLocation',
            'http://www.sat.gob.mx/cfd/4 cfdv40.xsd ' .
            'http://www.sat.gob.mx/TimbreFiscalDigital ' .
            'http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd'
        );
        $this->setAttrs($comp, [
            'Version'           => '4.0',
            'Serie'             => $data['serie'] ?? '',
            'Folio'             => $data['folio'] ?? '',
            'Fecha'             => $fecha,
            'FormaPago'         => $data['formaPago'],
            'MetodoPago'        => $data['metodoPago'],
            'Moneda'            => $data['moneda'],
            'SubTotal'          => $subtotal,
            'Total'             => $total,
            'TipoDeComprobante' => 'I',
            'Exportacion'       => '01',
            'NoCertificado'     => '00000000000000000000',
            'Certificado'       => '',
            'Sello'             => '',
        ]);
        $dom->appendChild($comp);

        $emisor = $dom->createElementNS($ns, 'cfdi:Emisor');
        $this->setAttrs($emisor, [
            'Rfc'           => $data['rfcEmisor'],
            'Nombre'        => $data['nombreEmisor'],
            'RegimenFiscal' => $data['regimenFiscal'],
        ]);
        $comp->appendChild($emisor);

        $receptor = $dom->createElementNS($ns, 'cfdi:Receptor');
        $this->setAttrs($receptor, [
            'Rfc'                     => $data['rfcReceptor'],
            'Nombre'                  => $data['nombreReceptor'],
            'DomicilioFiscalReceptor' => $data['domicilioFiscalReceptor'],
            'RegimenFiscalReceptor'   => $data['regimenFiscalReceptor'],
            'UsoCFDI'                 => $data['usoCFDI'],
        ]);
        $comp->appendChild($receptor);

        $conceptos = $dom->createElementNS($ns, 'cfdi:Conceptos');
        $concepto  = $dom->createElementNS($ns, 'cfdi:Concepto');
        $this->setAttrs($concepto, [
            'ClaveProdServ' => $data['claveProdServ'],
            'ClaveUnidad'   => $data['claveUnidad'],
            'Cantidad'      => $cantidad,
            'Descripcion'   => $data['descripcion'],
            'ValorUnitario' => $valUnit,
            'Importe'       => $subtotal,
        ]);

        $concImp  = $dom->createElementNS($ns, 'cfdi:Impuestos');
        $trasladosNode = $dom->createElementNS($ns, 'cfdi:Traslados');
        $traslado = $dom->createElementNS($ns, 'cfdi:Traslado');
        $this->setAttrs($traslado, [
            'Base'       => $subtotal,
            'Impuesto'   => '002',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => $tasaIVA,
            'Importe'    => $iva,
        ]);
        $trasladosNode->appendChild($traslado);
        $concImp->appendChild($trasladosNode);

        if (!empty($retenciones)) {
            $concRets = $dom->createElementNS($ns, 'cfdi:Retenciones');
            foreach ($retenciones as $ret) {
                $r = $dom->createElementNS($ns, 'cfdi:Retencion');
                $this->setAttrs($r, [
                    'Base'       => $subtotal,
                    'Impuesto'   => $ret['impuesto'],
                    'TipoFactor' => 'Tasa',
                    'TasaOCuota' => number_format((float) $ret['tasa'], 6, '.', ''),
                    'Importe'    => $ret['importe'],
                ]);
                $concRets->appendChild($r);
            }
            $concImp->appendChild($concRets);
        }
        $concepto->appendChild($concImp);
        $conceptos->appendChild($concepto);
        $comp->appendChild($conceptos);

        $impNode = $dom->createElementNS($ns, 'cfdi:Impuestos');
        $impNode->setAttribute('TotalImpuestosTrasladados', $iva);
        if (!empty($retenciones)) {
            $impNode->setAttribute('TotalImpuestosRetenidos', $totalRetenido);
        }

        $gTraslados = $dom->createElementNS($ns, 'cfdi:Traslados');
        $gTraslado  = $dom->createElementNS($ns, 'cfdi:Traslado');
        $this->setAttrs($gTraslado, [
            'Base'       => $subtotal,
            'Impuesto'   => '002',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => $tasaIVA,
            'Importe'    => $iva,
        ]);
        $gTraslados->appendChild($gTraslado);
        $impNode->appendChild($gTraslados);

        if (!empty($retenciones)) {
            $gRets   = $dom->createElementNS($ns, 'cfdi:Retenciones');
            $grouped = collect($retenciones)->groupBy('impuesto');
            foreach ($grouped as $impuesto => $items) {
                $gRet = $dom->createElementNS($ns, 'cfdi:Retencion');
                $this->setAttrs($gRet, [
                    'Impuesto' => $impuesto,
                    'Importe'  => number_format(
                        $items->sum(fn($r) => (float) $r['importe']), 2, '.', ''
                    ),
                ]);
                $gRets->appendChild($gRet);
            }
            $impNode->appendChild($gRets);
        }
        $comp->appendChild($impNode);

        $complemento = $dom->createElementNS($ns, 'cfdi:Complemento');
        $tfd = $dom->createElementNS($nsTfd, 'tfd:TimbreFiscalDigital');
        $this->setAttrs($tfd, [
            'Version'          => '1.1',
            'UUID'             => $uuid,
            'FechaTimbrado'    => $fecha,
            'RfcProvCertif'    => 'SAT970701NN3',
            'NoCertificadoSAT' => '00000000000000000000',
            'SelloSAT'         => '',
            'SelloCFD'         => '',
        ]);
        $complemento->appendChild($tfd);
        $comp->appendChild($complemento);

        return $dom->saveXML();
    }

    public function buildPdf(array $data): string
    {
        $retenciones = $this->resolveRetenciones($data, (float) $data['subtotal']);

        $viewData = array_merge($data, [
            'uuid'            => (string) Str::uuid(),
            'retencionesData' => $retenciones,
            'fecha'           => $this->normalizeFecha($data['fecha']),
        ]);

        $html = view('tools.cfdi-pdf', $viewData)->render();

        return \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function resolveRetenciones(array $data, float $subtotal): array
    {
        $enabled   = $data['ret_enabled']  ?? [];
        $tasas     = $data['ret_tasa']     ?? [];
        $impuestos = $data['ret_impuesto'] ?? [];

        return collect($enabled)
            ->map(fn(string $clave) => [
                'clave'    => $clave,
                'impuesto' => $impuestos[$clave] ?? '001',
                'tasa'     => (float) ($tasas[$clave] ?? 0),
                'importe'  => number_format($subtotal * (float) ($tasas[$clave] ?? 0), 2, '.', ''),
            ])
            ->values()
            ->toArray();
    }

    public function normalizeFecha(string $fecha): string
    {
        return strlen($fecha) === 16 ? $fecha . ':00' : $fecha;
    }

    private function setAttrs(DOMElement $el, array $attrs): void
    {
        foreach ($attrs as $k => $v) {
            $el->setAttribute($k, (string) $v);
        }
    }
}
```

- [ ] **Step 4: Correr los tests**

```bash
php artisan test tests/Unit/CfdiGeneratorServiceXmlTest.php
```

Expected: 8 tests pass, 0 failures.

- [ ] **Step 5: Commit**

```bash
git add app/Services/CfdiGeneratorService.php tests/Unit/CfdiGeneratorServiceXmlTest.php
git commit -m "feat(tools): add CfdiGeneratorService with buildXml"
```

---

### Task 3: CfdiGeneratorService — buildPdf() y vista PDF

**Files:**
- Create: `resources/views/tools/cfdi-pdf.blade.php`
- Create: `tests/Unit/CfdiGeneratorServicePdfTest.php`

> Nota: `buildPdf()` ya está incluido en `CfdiGeneratorService` del Task 2. Este task solo crea la vista Blade y el test.

- [ ] **Step 1: Escribir el test que falla**

Crea `tests/Unit/CfdiGeneratorServicePdfTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Services\CfdiGeneratorService;
use Tests\TestCase;

class CfdiGeneratorServicePdfTest extends TestCase
{
    private CfdiGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CfdiGeneratorService();
    }

    private function baseData(): array
    {
        return [
            'rfcEmisor'               => 'AAA010101AAA',
            'nombreEmisor'            => 'Proveedor SA de CV',
            'regimenFiscal'           => '601',
            'rfcReceptor'             => 'TGT010101TGT',
            'nombreReceptor'          => 'Total Gas SA',
            'domicilioFiscalReceptor' => '64000',
            'regimenFiscalReceptor'   => '601',
            'usoCFDI'                 => 'G03',
            'serie'                   => 'A',
            'folio'                   => '1',
            'fecha'                   => '2026-05-19T10:00',
            'formaPago'               => '03',
            'metodoPago'              => 'PUE',
            'moneda'                  => 'MXN',
            'claveProdServ'           => '84111506',
            'claveUnidad'             => 'E48',
            'descripcion'             => 'Servicios',
            'cantidad'                => 1,
            'valorUnitario'           => 1000,
            'tasaIVA'                 => '0.16',
            'subtotal'                => 1000.00,
            'iva'                     => 160.00,
            'total'                   => 1160.00,
            'ret_enabled'             => [],
            'ret_tasa'                => [],
            'ret_impuesto'            => [],
        ];
    }

    public function test_buildPdf_returns_non_empty_string(): void
    {
        $pdf = $this->service->buildPdf($this->baseData());

        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
    }

    public function test_buildPdf_starts_with_pdf_header(): void
    {
        $pdf = $this->service->buildPdf($this->baseData());

        $this->assertStringStartsWith('%PDF', $pdf);
    }
}
```

- [ ] **Step 2: Verificar que el test falla**

```bash
php artisan test tests/Unit/CfdiGeneratorServicePdfTest.php
```

Expected: error `View [tools.cfdi-pdf] not found`

- [ ] **Step 3: Crear el directorio y la vista PDF**

Crea `resources/views/tools/cfdi-pdf.blade.php`:

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: #333; }

        .watermark {
            position: fixed;
            top: 42%;
            left: 5%;
            width: 90%;
            text-align: center;
            font-size: 48px;
            font-weight: bold;
            color: rgba(220, 0, 0, 0.10);
            transform: rotate(-35deg);
            white-space: nowrap;
            z-index: 0;
        }

        .content { position: relative; z-index: 1; padding: 15px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background-color: #2c3e50; color: #fff; padding: 5px 6px; text-align: left; font-size: 8px; }
        td { padding: 4px 6px; border: 1px solid #ddd; vertical-align: top; }
        .label { font-weight: bold; color: #555; width: 30%; background: #f8f8f8; }

        .header-box { border: 2px solid #2c3e50; padding: 10px; margin-bottom: 8px; }
        .header-title { font-size: 20px; font-weight: bold; color: #2c3e50; text-align: right; }
        .header-sub { font-size: 11px; color: #666; text-align: right; }
        .emisor-name { font-size: 13px; font-weight: bold; }

        .totals-table td { text-align: right; }
        .totals-table .label { text-align: left; }
        .total-row td { font-weight: bold; font-size: 11px; background: #2c3e50; color: #fff; }

        .uuid-box { background: #f0f0f0; border: 1px solid #ccc; padding: 6px; font-size: 8px; word-break: break-all; margin-bottom: 8px; }
        .badge-prueba { background: #dc3545; color: #fff; text-align: center; padding: 4px; font-size: 10px; font-weight: bold; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="watermark">DOCUMENTO DE PRUEBA — NO VÁLIDO FISCALMENTE</div>

    <div class="content">
        <div class="badge-prueba">DOCUMENTO DE PRUEBA — NO VÁLIDO FISCALMENTE</div>

        {{-- Encabezado Emisor --}}
        <div class="header-box">
            <table style="border:none; margin:0;">
                <tr>
                    <td style="border:none; width:60%; padding:0;">
                        <div class="emisor-name">{{ $nombreEmisor }}</div>
                        <div>RFC: <strong>{{ $rfcEmisor }}</strong></div>
                        <div>Régimen: {{ $regimenFiscal }}</div>
                    </td>
                    <td style="border:none; text-align:right; padding:0;">
                        <div class="header-title">FACTURA</div>
                        <div class="header-sub">Serie <strong>{{ $serie ?: 'N/A' }}</strong> &nbsp; Folio <strong>{{ $folio ?: 'N/A' }}</strong></div>
                        <div class="header-sub">Fecha: <strong>{{ $fecha }}</strong></div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Receptor --}}
        <table>
            <tr><th colspan="4">DATOS DEL RECEPTOR</th></tr>
            <tr>
                <td class="label">RFC</td><td>{{ $rfcReceptor }}</td>
                <td class="label">Nombre</td><td>{{ $nombreReceptor }}</td>
            </tr>
            <tr>
                <td class="label">Código Postal</td><td>{{ $domicilioFiscalReceptor }}</td>
                <td class="label">Régimen</td><td>{{ $regimenFiscalReceptor }}</td>
            </tr>
            <tr>
                <td class="label">Uso CFDI</td><td colspan="3">{{ $usoCFDI }}</td>
            </tr>
        </table>

        {{-- Datos del comprobante --}}
        <table>
            <tr><th colspan="6">DATOS DEL COMPROBANTE</th></tr>
            <tr>
                <td class="label">Forma de pago</td><td>{{ $formaPago }}</td>
                <td class="label">Método de pago</td><td>{{ $metodoPago }}</td>
                <td class="label">Moneda</td><td>{{ $moneda }}</td>
            </tr>
        </table>

        {{-- Conceptos --}}
        <table>
            <tr>
                <th>Clave Prod/Serv</th>
                <th>Clave Unidad</th>
                <th>Cantidad</th>
                <th>Descripción</th>
                <th style="text-align:right;">Val. Unitario</th>
                <th style="text-align:right;">Importe</th>
            </tr>
            <tr>
                <td>{{ $claveProdServ }}</td>
                <td>{{ $claveUnidad }}</td>
                <td>{{ number_format((float)$cantidad, 2) }}</td>
                <td>{{ $descripcion }}</td>
                <td style="text-align:right;">${{ number_format((float)$valorUnitario, 2) }}</td>
                <td style="text-align:right;">${{ number_format((float)$subtotal, 2) }}</td>
            </tr>
        </table>

        {{-- Totales --}}
        <table class="totals-table" style="width: 50%; margin-left: 50%;">
            <tr>
                <td class="label">Subtotal</td>
                <td>${{ number_format((float)$subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">IVA ({{ number_format((float)$tasaIVA * 100, 4) }}%)</td>
                <td>${{ number_format((float)$iva, 2) }}</td>
            </tr>
            @foreach($retencionesData as $ret)
            <tr>
                <td class="label">Ret. {{ $ret['clave'] }} ({{ number_format($ret['tasa'] * 100, 4) }}%)</td>
                <td>-${{ number_format((float)$ret['importe'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td class="label" style="color:#fff; background:#2c3e50;">TOTAL</td>
                <td style="background:#2c3e50;">${{ number_format((float)$total, 2) }} {{ $moneda }}</td>
            </tr>
        </table>

        {{-- UUID / Timbre --}}
        <div class="uuid-box">
            <strong>Folio Fiscal (UUID):</strong> {{ $uuid }}<br>
            <strong>Fecha Timbrado:</strong> {{ $fecha }}<br>
            <strong>RFC PAC (mock):</strong> SAT970701NN3 &nbsp;&nbsp;
            <strong>No. Certificado SAT:</strong> 00000000000000000000
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 4: Correr los tests**

```bash
php artisan test tests/Unit/CfdiGeneratorServicePdfTest.php
```

Expected: 2 tests pass.

- [ ] **Step 5: Correr todos los tests del servicio juntos**

```bash
php artisan test tests/Unit/CfdiGeneratorServiceXmlTest.php tests/Unit/CfdiGeneratorServicePdfTest.php
```

Expected: 10 tests pass, 0 failures.

- [ ] **Step 6: Commit**

```bash
git add resources/views/tools/cfdi-pdf.blade.php tests/Unit/CfdiGeneratorServicePdfTest.php
git commit -m "feat(tools): add PDF blade template and PDF tests"
```

---

### Task 4: CfdiGeneratorController

**Files:**
- Create: `app/Http/Controllers/Tools/CfdiGeneratorController.php`
- Create: `tests/Feature/CfdiGeneratorControllerTest.php`

- [ ] **Step 1: Escribir los tests que fallan**

Crea `tests/Feature/CfdiGeneratorControllerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CfdiGeneratorControllerTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        return $user;
    }

    private function basePost(): array
    {
        return [
            'rfcEmisor'               => 'AAA010101AAA',
            'nombreEmisor'            => 'Proveedor SA',
            'regimenFiscal'           => '601',
            'rfcReceptor'             => 'TGT010101TGT',
            'nombreReceptor'          => 'Total Gas',
            'domicilioFiscalReceptor' => '64000',
            'regimenFiscalReceptor'   => '601',
            'usoCFDI'                 => 'G03',
            'serie'                   => 'A',
            'folio'                   => '1',
            'fecha'                   => '2026-05-19T10:00',
            'formaPago'               => '03',
            'metodoPago'              => 'PUE',
            'moneda'                  => 'MXN',
            'claveProdServ'           => '84111506',
            'claveUnidad'             => 'E48',
            'descripcion'             => 'Servicios',
            'cantidad'                => 1,
            'valorUnitario'           => 1000,
            'tasaIVA'                 => '0.16',
            'subtotal'                => 1000,
            'iva'                     => 160,
            'total'                   => 1160,
        ];
    }

    public function test_form_accessible_by_superadmin(): void
    {
        Supplier::factory()->create(['rfc' => 'AAA010101AAA', 'company_name' => 'Proveedor SA']);
        Company::factory()->create(['rfc' => 'TGT010101TGT', 'is_active' => true]);

        $response = $this->actingAs($this->superadmin())->get(route('tools.cfdi.form'));

        $response->assertOk();
    }

    public function test_form_denied_for_non_superadmin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('accounting');

        $response = $this->actingAs($user)->get(route('tools.cfdi.form'));

        $response->assertForbidden();
    }

    public function test_download_xml_returns_xml_content_type(): void
    {
        Supplier::factory()->create(['rfc' => 'AAA010101AAA', 'company_name' => 'Proveedor SA']);
        Company::factory()->create(['rfc' => 'TGT010101TGT', 'is_active' => true]);

        $response = $this->actingAs($this->superadmin())
            ->post(route('tools.cfdi.xml'), $this->basePost());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_download_pdf_returns_pdf_content_type(): void
    {
        Supplier::factory()->create(['rfc' => 'AAA010101AAA', 'company_name' => 'Proveedor SA']);
        Company::factory()->create(['rfc' => 'TGT010101TGT', 'is_active' => true]);

        $response = $this->actingAs($this->superadmin())
            ->post(route('tools.cfdi.pdf'), $this->basePost());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_download_xml_fails_validation_without_required_fields(): void
    {
        $response = $this->actingAs($this->superadmin())
            ->post(route('tools.cfdi.xml'), []);

        $response->assertSessionHasErrors(['rfcEmisor', 'rfcReceptor', 'fecha']);
    }
}
```

- [ ] **Step 2: Verificar que los tests fallan**

```bash
php artisan test tests/Feature/CfdiGeneratorControllerTest.php
```

Expected: error `Class "App\Http\Controllers\Tools\CfdiGeneratorController" not found`

- [ ] **Step 3: Crear el controlador** `app/Http/Controllers/Tools/CfdiGeneratorController.php`:

```php
<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Supplier;
use App\Services\CfdiGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CfdiGeneratorController extends Controller
{
    public function __construct(private CfdiGeneratorService $service) {}

    public function form(): View
    {
        $suppliers = Supplier::orderBy('company_name')->get(['id', 'company_name', 'rfc']);
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'rfc']);

        return view('tools.cfdi-generator', [
            'suppliers'        => $suppliers,
            'companies'        => $companies,
            'regimenes'        => $this->regimenesFiscales(),
            'usosCfdi'         => $this->usosCfdi(),
            'formasPago'       => $this->formasPago(),
            'retencionCatalog' => CfdiGeneratorService::RETENCIONES_CATALOG,
        ]);
    }

    public function downloadXml(Request $request): Response
    {
        $data     = $this->validated($request);
        $xml      = $this->service->buildXml($data);
        $filename = 'cfdi_prueba_' . now()->format('YmdHis') . '.xml';

        return response($xml, 200, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadPdf(Request $request): Response
    {
        $data     = $this->validated($request);
        $pdf      = $this->service->buildPdf($data);
        $filename = 'cfdi_prueba_' . now()->format('YmdHis') . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rfcEmisor'               => 'required|string|exists:suppliers,rfc',
            'nombreEmisor'            => 'required|string|max:300',
            'regimenFiscal'           => 'required|string',
            'rfcReceptor'             => 'required|string|exists:companies,rfc',
            'nombreReceptor'          => 'required|string|max:300',
            'domicilioFiscalReceptor' => 'required|string|max:5',
            'regimenFiscalReceptor'   => 'required|string',
            'usoCFDI'                 => 'required|string',
            'serie'                   => 'nullable|string|max:25',
            'folio'                   => 'nullable|string|max:40',
            'fecha'                   => 'required|date_format:Y-m-d\TH:i',
            'formaPago'               => 'required|string',
            'metodoPago'              => 'required|in:PUE,PPD',
            'moneda'                  => 'required|in:MXN,USD',
            'claveProdServ'           => 'required|string|max:8',
            'claveUnidad'             => 'required|string|max:3',
            'descripcion'             => 'required|string|max:1000',
            'cantidad'                => 'required|numeric|min:0.001',
            'valorUnitario'           => 'required|numeric|min:0',
            'tasaIVA'                 => 'required|string|in:0,0.08,0.16',
            'subtotal'                => 'required|numeric|min:0',
            'iva'                     => 'required|numeric|min:0',
            'total'                   => 'required|numeric|min:0',
            'ret_enabled'             => 'nullable|array',
            'ret_enabled.*'           => 'string',
            'ret_tasa'                => 'nullable|array',
            'ret_tasa.*'              => 'numeric|min:0|max:1',
            'ret_impuesto'            => 'nullable|array',
            'ret_impuesto.*'          => 'in:001,002',
        ]);
    }

    private function regimenesFiscales(): array
    {
        return [
            '601' => '601 - General de Ley Personas Morales',
            '603' => '603 - Personas Morales con Fines no Lucrativos',
            '605' => '605 - Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => '606 - Arrendamiento',
            '607' => '607 - Régimen de Enajenación o Adquisición de Bienes',
            '608' => '608 - Demás ingresos',
            '610' => '610 - Residentes en el Extranjero sin Establecimiento Permanente',
            '611' => '611 - Ingresos por Dividendos (socios y accionistas)',
            '612' => '612 - Personas Físicas con Actividades Empresariales y Profesionales',
            '614' => '614 - Ingresos por intereses',
            '615' => '615 - Régimen de los ingresos por obtención de premios',
            '616' => '616 - Sin obligaciones fiscales',
            '620' => '620 - Sociedades Cooperativas de Producción',
            '621' => '621 - Incorporación Fiscal',
            '622' => '622 - Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => '623 - Opcional para Grupos de Sociedades',
            '624' => '624 - Coordinados',
            '625' => '625 - Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626' => '626 - Régimen Simplificado de Confianza',
        ];
    }

    private function usosCfdi(): array
    {
        return [
            'G01'  => 'G01 - Adquisición de mercancias',
            'G02'  => 'G02 - Devoluciones, descuentos o bonificaciones',
            'G03'  => 'G03 - Gastos en general',
            'I01'  => 'I01 - Construcciones',
            'I02'  => 'I02 - Mobilario y equipo de oficina por inversiones',
            'I03'  => 'I03 - Equipo de transporte',
            'I04'  => 'I04 - Equipo de computo y accesorios',
            'I05'  => 'I05 - Dados, troqueles, moldes, matrices y herramental',
            'I06'  => 'I06 - Comunicaciones telefónicas',
            'I07'  => 'I07 - Comunicaciones satelitales',
            'I08'  => 'I08 - Otra maquinaria y equipo',
            'D01'  => 'D01 - Honorarios médicos, dentales y gastos hospitalarios',
            'D02'  => 'D02 - Gastos médicos por incapacidad o discapacidad',
            'D03'  => 'D03 - Gastos funerales',
            'D04'  => 'D04 - Donativos',
            'D05'  => 'D05 - Intereses reales efectivamente pagados por créditos hipotecarios',
            'D06'  => 'D06 - Aportaciones voluntarias al SAR',
            'D07'  => 'D07 - Primas por seguros de gastos médicos',
            'D08'  => 'D08 - Gastos de transportación escolar obligatoria',
            'D09'  => 'D09 - Depósitos en cuentas para el ahorro',
            'D10'  => 'D10 - Pagos por servicios educativos (colegiaturas)',
            'S01'  => 'S01 - Sin efectos fiscales',
            'CP01' => 'CP01 - Pagos',
            'CN01' => 'CN01 - Nómina',
        ];
    }

    private function formasPago(): array
    {
        return [
            '01' => '01 - Efectivo',
            '02' => '02 - Cheque nominativo',
            '03' => '03 - Transferencia electrónica de fondos',
            '04' => '04 - Tarjeta de crédito',
            '05' => '05 - Monedero electrónico',
            '06' => '06 - Dinero electrónico',
            '08' => '08 - Vales de despensa',
            '12' => '12 - Dación en pago',
            '13' => '13 - Pago por subrogación',
            '14' => '14 - Pago por consignación',
            '15' => '15 - Condonación',
            '17' => '17 - Compensación',
            '23' => '23 - Novación',
            '24' => '24 - Confusión',
            '25' => '25 - Remisión de deuda',
            '26' => '26 - Prescripción o caducidad',
            '27' => '27 - A satisfacción del acreedor',
            '28' => '28 - Tarjeta de débito',
            '29' => '29 - Tarjeta de servicios',
            '30' => '30 - Aplicación de anticipos',
            '31' => '31 - Intermediario pagos',
            '99' => '99 - Por definir',
        ];
    }
}
```

- [ ] **Step 4: Correr los tests**

```bash
php artisan test tests/Feature/CfdiGeneratorControllerTest.php
```

Expected: 5 tests pass, 0 failures.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Tools/CfdiGeneratorController.php tests/Feature/CfdiGeneratorControllerTest.php
git commit -m "feat(tools): add CfdiGeneratorController"
```

---

### Task 5: Vista del formulario

**Files:**
- Create: `resources/views/tools/cfdi-generator.blade.php`

- [ ] **Step 1: Crear la vista** `resources/views/tools/cfdi-generator.blade.php`:

```blade
@extends('layouts.zircos')

@section('title', 'Generador CFDI de Prueba')
@section('page.title', 'Herramientas — Generador CFDI de Prueba')

@section('content')
<div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
    <i class="ti ti-alert-triangle me-2 fs-5"></i>
    <div><strong>Solo para pruebas.</strong> Los archivos generados no son válidos fiscalmente y no se guardan en el sistema.</div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="cfdi-form" method="POST" action="">
    @csrf

    {{-- ── BLOQUE 1: EMISOR ─────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">1. Emisor (Proveedor)</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">RFC Emisor <span class="text-danger">*</span></label>
                    <select name="rfcEmisor" id="rfcEmisor" class="form-select" required>
                        <option value="">Seleccionar proveedor...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->rfc }}" data-nombre="{{ $s->company_name }}">
                                {{ $s->rfc }} — {{ $s->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
                    <input type="text" name="nombreEmisor" id="nombreEmisor" class="form-control" required maxlength="300" value="{{ old('nombreEmisor') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Régimen Fiscal <span class="text-danger">*</span></label>
                    <select name="regimenFiscal" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($regimenes as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('regimenFiscal') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 2: RECEPTOR ───────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">2. Receptor (Empresa)</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">RFC Receptor <span class="text-danger">*</span></label>
                    <select name="rfcReceptor" id="rfcReceptor" class="form-select" required>
                        <option value="">Seleccionar empresa...</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->rfc }}" data-nombre="{{ $c->name }}">
                                {{ $c->rfc }} — {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
                    <input type="text" name="nombreReceptor" id="nombreReceptor" class="form-control" required maxlength="300" value="{{ old('nombreReceptor') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                    <input type="text" name="domicilioFiscalReceptor" class="form-control" required maxlength="5" pattern="\d{5}" value="{{ old('domicilioFiscalReceptor') }}" placeholder="64000">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Régimen Fiscal Receptor <span class="text-danger">*</span></label>
                    <select name="regimenFiscalReceptor" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($regimenes as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('regimenFiscalReceptor') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Uso CFDI <span class="text-danger">*</span></label>
                    <select name="usoCFDI" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($usosCfdi as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('usoCFDI') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 3: COMPROBANTE ────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">3. Datos del Comprobante</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Serie</label>
                    <input type="text" name="serie" class="form-control" maxlength="25" value="{{ old('serie', 'A') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Folio</label>
                    <input type="text" name="folio" class="form-control" maxlength="40" value="{{ old('folio', '1') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="fecha" class="form-control" required value="{{ old('fecha', now()->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Forma de Pago <span class="text-danger">*</span></label>
                    <select name="formaPago" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($formasPago as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('formaPago', '03') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                    <select name="metodoPago" class="form-select" required>
                        <option value="PUE" {{ old('metodoPago', 'PUE') == 'PUE' ? 'selected' : '' }}>PUE - Pago en una sola exhibición</option>
                        <option value="PPD" {{ old('metodoPago') == 'PPD' ? 'selected' : '' }}>PPD - Pago en parcialidades o diferido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Moneda <span class="text-danger">*</span></label>
                    <select name="moneda" class="form-select" required>
                        <option value="MXN" {{ old('moneda', 'MXN') == 'MXN' ? 'selected' : '' }}>MXN</option>
                        <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 4: CONCEPTO, IMPUESTOS Y RETENCIONES ─────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">4. Concepto, Traslados y Retenciones</h6></div>
        <div class="card-body">

            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Clave Prod/Serv <span class="text-danger">*</span></label>
                    <input type="text" name="claveProdServ" class="form-control" required maxlength="8" value="{{ old('claveProdServ', '84111506') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Clave Unidad <span class="text-danger">*</span></label>
                    <input type="text" name="claveUnidad" class="form-control" required maxlength="3" value="{{ old('claveUnidad', 'E48') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control" required maxlength="1000" value="{{ old('descripcion', 'Servicios profesionales') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" required min="0.001" step="0.001" value="{{ old('cantidad', 1) }}" oninput="recalculate()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor Unitario <span class="text-danger">*</span></label>
                    <input type="number" name="valorUnitario" id="valorUnitario" class="form-control" required min="0" step="0.01" value="{{ old('valorUnitario', 0) }}" oninput="recalculate()">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tasa IVA <span class="text-danger">*</span></label>
                    <select name="tasaIVA" id="tasaIVA" class="form-select" required onchange="recalculate()">
                        <option value="0.16" {{ old('tasaIVA', '0.16') == '0.16' ? 'selected' : '' }}>16%</option>
                        <option value="0.08" {{ old('tasaIVA') == '0.08' ? 'selected' : '' }}>8%</option>
                        <option value="0"    {{ old('tasaIVA') == '0'    ? 'selected' : '' }}>0%</option>
                    </select>
                </div>
            </div>

            <h6 class="mb-2">Retenciones</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Impuesto SAT</th>
                            <th style="width:150px;">Porcentaje (%)</th>
                            <th style="width:110px;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retencionCatalog as $clave => $ret)
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox"
                                    class="form-check-input ret-checkbox"
                                    name="ret_enabled[]"
                                    id="ret_{{ $clave }}"
                                    value="{{ $clave }}"
                                    data-impuesto="{{ $ret['impuesto'] }}"
                                    onchange="toggleRetencion('{{ $clave }}')">
                                <input type="hidden" name="ret_impuesto[{{ $clave }}]" value="{{ $ret['impuesto'] }}">
                            </td>
                            <td class="align-middle">
                                <label for="ret_{{ $clave }}" class="mb-0 fw-bold">{{ $clave }}</label>
                            </td>
                            <td class="align-middle">
                                {{ $ret['nombre'] }}
                                @if($ret['no_cfdi'])
                                    <span class="badge bg-warning text-dark ms-1" title="No genera CFDI de Retenciones según el SAT">No CFDI</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $ret['impuesto'] === '001' ? 'ISR (001)' : 'IVA (002)' }}</td>
                            <td>
                                <input type="number"
                                    name="ret_tasa[{{ $clave }}]"
                                    id="ret_tasa_{{ $clave }}"
                                    class="form-control form-control-sm"
                                    value="{{ number_format($ret['tasa'] * 100, 4) }}"
                                    min="0" max="100" step="0.0001"
                                    {{ $ret['variable'] ? '' : 'readonly' }}
                                    disabled
                                    oninput="recalculate()">
                            </td>
                            <td class="align-middle text-end text-muted" id="ret_importe_{{ $clave }}">$0.00</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totales --}}
            <div class="row g-3 justify-content-end">
                <div class="col-md-4">
                    <table class="table table-sm table-bordered mb-0">
                        <tr>
                            <th class="bg-light">Subtotal</th>
                            <td><input type="number" name="subtotal" id="subtotal" class="form-control form-control-sm" step="0.01" required value="{{ old('subtotal', 0) }}" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">IVA</th>
                            <td><input type="number" name="iva" id="iva" class="form-control form-control-sm" step="0.01" required value="{{ old('iva', 0) }}" readonly></td>
                        </tr>
                        <tr id="ret-summary-row" style="display:none;">
                            <th class="bg-light">Total Retenciones</th>
                            <td class="text-end"><span id="total-retenciones-display">$0.00</span></td>
                        </tr>
                        <tr class="table-dark">
                            <th>TOTAL</th>
                            <td><input type="number" name="total" id="total" class="form-control form-control-sm" step="0.01" required value="{{ old('total', 0) }}" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BOTONES ──────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <button type="button" class="btn btn-outline-primary" onclick="submitCfdi('{{ route('tools.cfdi.xml') }}')">
            <i class="ti ti-file-code me-1"></i> Descargar XML
        </button>
        <button type="button" class="btn btn-danger" onclick="submitCfdi('{{ route('tools.cfdi.pdf') }}')">
            <i class="ti ti-file-text me-1"></i> Descargar PDF
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const suppliersMap = @json($suppliers->keyBy('rfc')->map(fn($s) => $s->company_name));
    const companiesMap = @json($companies->keyBy('rfc')->map(fn($c) => $c->name));

    document.getElementById('rfcEmisor').addEventListener('change', function () {
        document.getElementById('nombreEmisor').value = suppliersMap[this.value] ?? '';
    });

    document.getElementById('rfcReceptor').addEventListener('change', function () {
        document.getElementById('nombreReceptor').value = companiesMap[this.value] ?? '';
    });

    window.toggleRetencion = function (clave) {
        const cb   = document.getElementById('ret_' + clave);
        const tasa = document.getElementById('ret_tasa_' + clave);
        tasa.disabled = !cb.checked;
        recalculate();
    };

    window.recalculate = function () {
        const cantidad      = parseFloat(document.getElementById('cantidad').value)      || 0;
        const valorUnitario = parseFloat(document.getElementById('valorUnitario').value) || 0;
        const tasaIVA       = parseFloat(document.getElementById('tasaIVA').value)       || 0;
        const subtotal      = cantidad * valorUnitario;
        const iva           = subtotal * tasaIVA;

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('iva').value      = iva.toFixed(2);

        let totalRet = 0;
        document.querySelectorAll('.ret-checkbox').forEach(function (cb) {
            const key     = cb.value;
            const tasaInp = document.getElementById('ret_tasa_' + key);
            const impEl   = document.getElementById('ret_importe_' + key);
            if (cb.checked) {
                const tasa    = (parseFloat(tasaInp.value) || 0) / 100;
                const importe = subtotal * tasa;
                totalRet     += importe;
                impEl.textContent = '$' + importe.toFixed(2);
            } else {
                impEl.textContent = '$0.00';
            }
        });

        document.getElementById('total').value = (subtotal + iva - totalRet).toFixed(2);
        document.getElementById('total-retenciones-display').textContent = '$' + totalRet.toFixed(2);
        document.getElementById('ret-summary-row').style.display = totalRet > 0 ? '' : 'none';
    };

    window.submitCfdi = function (action) {
        // Convert ret_tasa from % to decimal (0–1) before submitting
        document.querySelectorAll('[name^="ret_tasa["]').forEach(function (inp) {
            if (!inp.disabled) {
                inp.value = (parseFloat(inp.value) / 100).toFixed(6);
            }
        });
        const form = document.getElementById('cfdi-form');
        form.action = action;
        form.submit();
    };

    recalculate();
})();
</script>
@endpush
```

- [ ] **Step 2: Verificar visualmente** abriendo `http://localhost/tools/cfdi-generator` como superadmin. Confirmar:
  - Los 4 bloques de card se renderizan
  - Al seleccionar un proveedor en el select, el campo `nombreEmisor` se auto-rellena
  - Al seleccionar una empresa en el select, el campo `nombreReceptor` se auto-rellena
  - Al cambiar cantidad o valorUnitario, los campos subtotal/IVA/total se actualizan en tiempo real
  - Las retenciones con `variable: false` tienen el campo de porcentaje en readonly (gris)
  - Las retenciones con `variable: true` tienen el campo de porcentaje editable
  - ISR-SUE muestra el badge "No CFDI"
  - Al marcar una retención, el campo de tasa se habilita y el importe se calcula
  - Al marcar retenciones, aparece la fila "Total Retenciones" en la tabla de totales
  - El botón "Descargar XML" dispara una descarga de `.xml`
  - El botón "Descargar PDF" dispara una descarga de `.pdf`

- [ ] **Step 3: Commit**

```bash
git add resources/views/tools/cfdi-generator.blade.php
git commit -m "feat(tools): add CFDI generator form view"
```

---

### Task 6: Navegación

**Files:**
- Modify: `resources/views/layouts/navigation.blade.php`

- [ ] **Step 1: Agregar enlace desktop** dentro del div `Navigation Links` (después del enlace Dashboard, línea ~15-18):

```blade
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    @role('superadmin')
    <x-nav-link :href="route('tools.cfdi.form')" :active="request()->routeIs('tools.*')">
        Herramientas
    </x-nav-link>
    @endrole
</div>
```

- [ ] **Step 2: Agregar enlace responsive** dentro del div `pt-2 pb-3 space-y-1` (después del enlace responsive de Dashboard, línea ~70-73):

```blade
<div class="pt-2 pb-3 space-y-1">
    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-responsive-nav-link>
    @role('superadmin')
    <x-responsive-nav-link :href="route('tools.cfdi.form')" :active="request()->routeIs('tools.*')">
        Herramientas
    </x-responsive-nav-link>
    @endrole
</div>
```

- [ ] **Step 3: Verificar que el enlace aparece solo para superadmin** — loguearse como superadmin y confirmar que "Herramientas" aparece en la barra de navegación. Loguearse con otro rol y confirmar que no aparece.

- [ ] **Step 4: Correr la suite de tests completa**

```bash
php artisan test tests/Unit/CfdiGeneratorServiceXmlTest.php tests/Unit/CfdiGeneratorServicePdfTest.php tests/Feature/CfdiGeneratorControllerTest.php
```

Expected: 15 tests pass, 0 failures.

- [ ] **Step 5: Commit final**

```bash
git add resources/views/layouts/navigation.blade.php
git commit -m "feat(tools): add Herramientas nav link for superadmin"
```
