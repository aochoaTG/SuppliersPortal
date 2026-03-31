<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Alerta de seguimiento (Día 2): Vence mañana el plazo para capturar la recepción.
 * Destinatarios: Receptor de estación + Finanzas.
 */
class DeliveryAlertDay2Mail extends Mailable
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
            ->subject("RECORDATORIO URGENTE: Vence mañana el plazo — OC {$this->order->folio}")
            ->view('emails.deliveries.alert-day2');
    }
}
