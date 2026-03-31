<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Alerta crítica (Día 3): Venció el plazo sin que la estación capturara la recepción.
 * Destinatarios: Director de Administración y Finanzas + Departamento de Compras.
 */
class DeliveryAlertDay3Mail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $order,
        public $supplierName,
        public $deliveryDate,
        public $receivingLocationName,
    ) {}

    public function build()
    {
        return $this
            ->subject("⚠️ ALERTA CRÍTICA: Venció el plazo de captura — OC {$this->order->folio}")
            ->view('emails.deliveries.alert-day3');
    }
}
