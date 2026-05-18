<?php

namespace App\Services;

use Carbon\Carbon;
use RuntimeException;
use SimpleXMLElement;

class CfdiXmlParser
{
    public function parse(string $xmlContents): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContents);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('El XML de factura no es válido.');
        }

        $namespaces = $xml->getNamespaces(true);
        $cfdi = $namespaces['cfdi'] ?? $namespaces[''] ?? null;
        $root = $cfdi ? $xml->children($cfdi) : $xml;

        $attributes = $xml->attributes();
        $issuer = $this->firstNodeAttributes($root, 'Emisor');
        $receiver = $this->firstNodeAttributes($root, 'Receptor');
        $taxStamp = $this->taxStampAttributes($xml, $namespaces);

        $uuid = strtoupper(trim((string) ($taxStamp['UUID'] ?? '')));
        if ($uuid === '') {
            throw new RuntimeException('El XML no contiene UUID de timbre fiscal.');
        }

        return [
            'uuid' => $uuid,
            'issuer_rfc' => strtoupper(trim((string) ($issuer['Rfc'] ?? $issuer['rfc'] ?? ''))),
            'receiver_rfc' => strtoupper(trim((string) ($receiver['Rfc'] ?? $receiver['rfc'] ?? ''))),
            'subtotal' => round((float) ($attributes['SubTotal'] ?? $attributes['subTotal'] ?? 0), 2),
            'iva_amount' => $this->sumIva($xml, $namespaces),
            'total' => round((float) ($attributes['Total'] ?? $attributes['total'] ?? 0), 2),
            'currency' => strtoupper((string) ($attributes['Moneda'] ?? $attributes['moneda'] ?? 'MXN')),
            'issued_at' => $this->parseDate((string) ($attributes['Fecha'] ?? $attributes['fecha'] ?? '')),
        ];
    }

    private function firstNodeAttributes(SimpleXMLElement $root, string $node): array
    {
        if (! isset($root->{$node}[0])) {
            return [];
        }

        $attributes = [];
        foreach ($root->{$node}[0]->attributes() as $key => $value) {
            $attributes[$key] = (string) $value;
        }

        return $attributes;
    }

    private function taxStampAttributes(SimpleXMLElement $xml, array $namespaces): array
    {
        $complement = $xml->children($namespaces['cfdi'] ?? null)->Complemento ?? $xml->Complemento ?? null;
        if (! $complement) {
            return [];
        }

        foreach ($namespaces as $uri) {
            foreach ($complement->children($uri) as $child) {
                if (str_contains(strtolower($child->getName()), 'timbrefiscaldigital')) {
                    $attributes = [];
                    foreach ($child->attributes() as $key => $value) {
                        $attributes[$key] = (string) $value;
                    }
                    return $attributes;
                }
            }
        }

        return [];
    }

    private function sumIva(SimpleXMLElement $xml, array $namespaces): float
    {
        $total = 0.0;
        foreach ($xml->xpath('//*[local-name()="Traslado"]') ?: [] as $node) {
            $attributes = $node->attributes();
            $tax = (string) ($attributes['Impuesto'] ?? '');
            if ($tax === '002') {
                $total += (float) ($attributes['Importe'] ?? 0);
            }
        }

        return round($total, 2);
    }

    private function parseDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
