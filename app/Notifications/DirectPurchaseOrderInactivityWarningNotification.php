<?php

namespace App\Notifications;

use App\Models\DirectPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectPurchaseOrderInactivityWarningNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly DirectPurchaseOrder $ocd) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->ocd->getAutoCloseDeadline();
        $url = route('direct-purchase-orders.show', $this->ocd->id);

        return (new MailMessage)
            ->subject('⚠️ ALERTA: OC Directa ' . $this->ocd->folio . ' se cerrará en 3 días')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('**ALERTA:** La siguiente Orden de Compra Directa será cerrada automáticamente por inactividad en **3 días** si no es aprobada.')
            ->line('')
            ->line('**Detalles de la OCD:**')
            ->line('• **Folio:** ' . $this->ocd->folio)
            ->line('• **Proveedor:** ' . ($this->ocd->supplier->company_name ?? 'N/A'))
            ->line('• **Centro de Costo:** ' . ($this->ocd->costCenter->name ?? 'N/A'))
            ->line('• **Monto Total:** $' . number_format($this->ocd->total, 2) . ' ' . $this->ocd->currency)
            ->line('• **Solicitante:** ' . ($this->ocd->creator->name ?? 'N/A'))
            ->line('• **Enviada a aprobación:** ' . ($this->ocd->submitted_at?->format('d/m/Y H:i') ?? 'N/A'))
            ->line('• **Fecha límite de aprobación:** ' . ($deadline?->format('d/m/Y') ?? 'N/A'))
            ->line('')
            ->line('Si la OCD no es aprobada antes del ' . ($deadline?->format('d/m/Y') ?? 'N/A') . ', será **cerrada automáticamente** y el presupuesto no será comprometido.')
            ->action('Revisar Orden de Compra', $url)
            ->line('Por favor, tome acción antes de que venza el plazo.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ocd_inactivity_warning',
            'ocd_id' => $this->ocd->id,
            'ocd_folio' => $this->ocd->folio,
            'total' => $this->ocd->total,
            'deadline' => $this->ocd->getAutoCloseDeadline()?->toDateString(),
            'url' => route('direct-purchase-orders.show', $this->ocd->id),
            'message' => 'ALERTA: La OC Directa ' . $this->ocd->folio . ' será cerrada automáticamente en 3 días si no es aprobada.',
        ];
    }
}
