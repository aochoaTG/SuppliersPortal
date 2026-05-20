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
