<?php

namespace App\Services;

class PricingService
{
    /**
     * Tasa de IVA por defecto (16%)
     */
    private const DEFAULT_IVA_RATE = 0.16;

    /**
     * Calcula subtotal, IVA y total para un conjunto de items.
     *
     * @param array $items Array de items con 'quantity' y 'unit_price'
     * @param float $ivaRate Tasa de IVA (por defecto 0.16)
     * @return array{subtotal: float, iva: float, total: float}
     */
    public function calculateTotals(array $items, float $ivaRate = self::DEFAULT_IVA_RATE): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $quantity = floatval($item['quantity']);
            $unitPrice = floatval($item['unit_price']);
            $subtotal += ($quantity * $unitPrice);
        }

        $iva = $subtotal * $ivaRate;
        $total = $subtotal + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Calcula totales con impuestos para un solo item.
     *
     * @param float $unitPrice Precio unitario
     * @param float $quantity Cantidad
     * @param float $ivaRate Tasa de IVA (por defecto 0.16)
     * @return array{subtotal: float, iva: float, total: float}
     */
    public function calculateItemTotals(
        float $unitPrice,
        float $quantity,
        float $ivaRate = self::DEFAULT_IVA_RATE
    ): array {
        $subtotal = $unitPrice * $quantity;
        $iva = $subtotal * $ivaRate;
        $total = $subtotal + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Calcula el total con descuento aplicado.
     *
     * @param float $subtotal Subtotal antes de impuestos
     * @param float $discount Descuento en porcentaje (0-100)
     * @param float $ivaRate Tasa de IVA (por defecto 0.16)
     * @return array{subtotal: float, discount: float, subtotal_after_discount: float, iva: float, total: float}
     */
    public function calculateWithDiscount(
        float $subtotal,
        float $discount = 0.0,
        float $ivaRate = self::DEFAULT_IVA_RATE
    ): array {
        $discountAmount = $subtotal * ($discount / 100);
        $subtotalAfterDiscount = $subtotal - $discountAmount;
        $iva = $subtotalAfterDiscount * $ivaRate;
        $total = $subtotalAfterDiscount + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discountAmount, 2),
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }
}
