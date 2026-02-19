<?php

namespace App\Notifications;

use App\Models\DirectPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectPurchaseOrderRejectedNotification extends Notification
{
    use Queueable;

    public DirectPurchaseOrder $ocd;
    public string $rejectionReason;

    public function __construct(DirectPurchaseOrder $ocd, string $rejectionReason)
    {
        $this->ocd = $ocd;
        $this->rejectionReason = $rejectionReason;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('direct-purchase-orders.show', $this->ocd->id);

        return (new MailMessage)
            ->subject('❌ Orden de Compra Rechazada - ' . $this->ocd->folio)
            ->greeting('Estimado(a) ' . $notifiable->name . ',')
            ->line('Le informamos que la siguiente Orden de Compra Directa ha sido **RECHAZADA**.')
            ->line('')
            ->line('**Detalles de la OC:**')
            ->line('• **Folio:** ' . $this->ocd->folio)
            ->line('• **Monto Total:** $' . number_format($this->ocd->total, 2) . ' ' . $this->ocd->currency)
            ->line('• **Proveedor:** ' . ($this->ocd->supplier->company_name ?? 'N/A'))
            ->line('• **Centro de Costo:** ' . ($this->ocd->costCenter->name ?? 'N/A'))
            ->line('• **Solicitado por:** ' . ($this->ocd->creator->name ?? 'N/A'))
            ->line('')
            ->line('**Motivo del Rechazo:**')
            ->line($this->rejectionReason)
            ->line('')
            ->action('Ver Orden de Compra', $url)
            ->line('Si tiene alguna duda, contacte al aprobador.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'direct_purchase_order_rejected',
            'ocd_id'           => $this->ocd->id,
            'ocd_folio'        => $this->ocd->folio,
            'total'            => $this->ocd->total,
            'rejection_reason' => $this->rejectionReason,
            'url'              => route('direct-purchase-orders.show', $this->ocd->id),
            'message'          => 'Orden de Compra ' . $this->ocd->folio . ' ha sido rechazada.',
        ];
    }
}
