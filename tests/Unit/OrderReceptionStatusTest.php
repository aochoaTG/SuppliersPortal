<?php

namespace Tests\Unit;

use App\Models\DirectPurchaseOrder;
use App\Models\PurchaseOrder;
use PHPUnit\Framework\TestCase;

class OrderReceptionStatusTest extends TestCase
{
    public function test_purchase_orders_delivered_by_supplier_can_be_received(): void
    {
        $order = new PurchaseOrder(['status' => 'DELIVERED_PENDING_RECEPTION']);

        $this->assertTrue($order->canBeReceived());
    }

    public function test_direct_purchase_orders_delivered_by_supplier_can_be_received(): void
    {
        $order = new DirectPurchaseOrder(['status' => 'DELIVERED_PENDING_RECEPTION']);

        $this->assertTrue($order->canBeReceived());
    }
}
