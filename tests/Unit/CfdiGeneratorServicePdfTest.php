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
