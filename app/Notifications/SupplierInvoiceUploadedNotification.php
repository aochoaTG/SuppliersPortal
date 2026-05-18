<?php

namespace App\Notifications;

use App\Models\SupplierInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierInvoiceUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(public SupplierInvoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Factura cargada por proveedor')
            ->line("El proveedor {$this->invoice->supplier?->company_name} cargó una factura.")
            ->line("UUID: {$this->invoice->uuid}")
            ->line('Total: $' . number_format((float) $this->invoice->total, 2) . ' ' . $this->invoice->currency)
            ->action('Ver facturas', route('invoices.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'supplier_invoice_id' => $this->invoice->id,
            'supplier_id' => $this->invoice->supplier_id,
            'uuid' => $this->invoice->uuid,
            'total' => (float) $this->invoice->total,
        ];
    }
}
