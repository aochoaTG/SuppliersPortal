<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Alerta inmediata (Día 0): El proveedor acaba de registrar una entrega.
 * Destinatarios: Receptor asignado a la estación + Departamento de Compras.
 */
class DeliveryAlertDay0Mail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $order,
        public $supplierName,
        public $deliveryDate,
        public $evidenceUrl,
        public $receivingLocationName,
    ) {}

    public function build()
    {
        return $this
            ->subject("ATENCIÓN: Entrega registrada pendiente de capturar — OC {$this->order->folio}")
            ->view('emails.deliveries.alert-day0');
    }
}
