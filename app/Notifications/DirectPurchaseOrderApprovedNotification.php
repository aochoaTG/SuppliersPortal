<?php

namespace App\Notifications;

use App\Models\DirectPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectPurchaseOrderApprovedNotification extends Notification
{
    use Queueable;

    public DirectPurchaseOrder $ocd;

    /**
     * Create a new notification instance.
     */
    public function __construct(DirectPurchaseOrder $ocd)
    {
        $this->ocd = $ocd;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('direct-purchase-orders.show', $this->ocd->id);

        return (new MailMessage)
            ->subject('✅ Orden de Compra Aprobada - ' . $this->ocd->folio)
            ->greeting('Estimado(a) ' . ($this->ocd->supplier->company_name ?? $notifiable->name) . ',')
            ->line('Le informamos que se ha **APROBADO** una nueva Orden de Compra.')
            ->line('')
            ->line('**Detalles de la OC:**')
            ->line('• **Folio:** ' . $this->ocd->folio)
            ->line('• **Monto Total:** $' . number_format($this->ocd->total, 2) . ' ' . $this->ocd->currency)
            ->line('• **Condiciones de Pago:** ' . ($this->ocd->payment_terms ?? 'N/A'))
            ->line('')
            ->line('Puede consultar el detalle completo y descargar el documento desde nuestro portal de proveedores.')
            ->action('Ver Orden de Compra', $url)
            ->line('Si tiene alguna duda, por favor contacte al solicitante: ' . ($this->ocd->creator->name ?? 'N/A'))
            ->line('Gracias por ser nuestro socio comercial.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'direct_purchase_order_approved',
            'ocd_id' => $this->ocd->id,
            'ocd_folio' => $this->ocd->folio,
            'total' => $this->ocd->total,
            'url' => route('direct-purchase-orders.show', $this->ocd->id),
            'message' => 'Orden de Compra ' . $this->ocd->folio . ' ha sido aprobada.',
        ];
    }
}
