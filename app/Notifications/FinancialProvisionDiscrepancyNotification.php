<?php

namespace App\Notifications;

use App\Models\FinancialProvision;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinancialProvisionDiscrepancyNotification extends Notification
{
    use Queueable;

    public function __construct(public FinancialProvision $provision)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Discrepancia entre provisión y factura')
            ->line("La provisión de la recepción {$this->provision->reception?->folio} tiene diferencia contra factura.")
            ->line('Provisión: $' . number_format((float) $this->provision->provision_amount, 2))
            ->line('Factura: $' . number_format((float) $this->provision->invoice_amount, 2))
            ->line('Diferencia: $' . number_format((float) $this->provision->difference_amount, 2))
            ->action('Revisar discrepancia', route('financial-provisions.show', $this->provision));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'financial_provision_id' => $this->provision->id,
            'supplier_invoice_id' => $this->provision->supplier_invoice_id,
            'difference_amount' => (float) $this->provision->difference_amount,
        ];
    }
}
