<?php

namespace App\Notifications;

use App\Models\DirectPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDirectPurchaseOrderNotification extends Notification
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
            ->subject('🔔 Nueva OC Directa para Revisión - ' . $this->ocd->folio)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Se ha generado una **Nueva Orden de Compra Directa** que requiere tu aprobación.')
            ->line('')
            ->line('**Detalles de la OCD:**')
            ->line('• **Folio:** ' . $this->ocd->folio)
            ->line('• **Proveedor:** ' . ($this->ocd->supplier->company_name ?? 'N/A'))
            ->line('• **Centro de Costo:** ' . ($this->ocd->costCenter->name ?? 'N/A'))
            ->line('• **Monto Total:** $' . number_format($this->ocd->total, 2) . ' ' . $this->ocd->currency)
            ->line('• **Nivel de Aprobación:** ' . $this->ocd->required_approval_level)
            ->line('• **Solicitante:** ' . ($this->ocd->creator->name ?? 'N/A'))
            ->line('')
            ->line('**Justificación:** ' . ($this->ocd->justification ?: 'Sin justificación'))
            ->line('')
            ->line('⚠️ **Nota Importante:**')
            ->line('Tienes un plazo de **7 días naturales** para revisar y dictaminar esta solicitud.')
            ->line('')
            ->action('Ver Orden de Compra', $url)
            ->line('Gracias por tu gestión.')
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
            'type' => 'new_direct_purchase_order',
            'ocd_id' => $this->ocd->id,
            'ocd_folio' => $this->ocd->folio,
            'total' => $this->ocd->total,
            'url' => route('direct-purchase-orders.show', $this->ocd->id),
            'message' => 'Nueva OC Directa ' . $this->ocd->folio . ' pendiente de aprobación.',
        ];
    }
}
