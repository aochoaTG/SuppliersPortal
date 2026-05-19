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
