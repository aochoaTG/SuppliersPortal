<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderInactivityWarningNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly PurchaseOrder $po) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->po->getAutoCloseDeadline();
        $url = route('purchase-orders.show', $this->po->id);

        return (new MailMessage)
            ->subject('⚠️ ALERTA: OC Estándar ' . $this->po->folio . ' se cerrará en 3 días')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('**ALERTA:** La siguiente Orden de Compra Estándar será cerrada automáticamente por inactividad en **3 días** si no es aprobada.')
            ->line('')
            ->line('**Detalles de la OC:**')
            ->line('• **Folio:** ' . $this->po->folio)
            ->line('• **Proveedor:** ' . ($this->po->supplier->company_name ?? 'N/A'))
            ->line('• **Monto Total:** $' . number_format($this->po->total, 2) . ' ' . $this->po->currency)
            ->line('• **Generada el:** ' . $this->po->created_at->format('d/m/Y H:i'))
            ->line('• **Fecha límite de aprobación:** ' . $deadline->format('d/m/Y'))
            ->line('')
            ->line('Si la OC no es aprobada antes del ' . $deadline->format('d/m/Y') . ', será **cerrada automáticamente** y el presupuesto comprometido será liberado.')
            ->action('Revisar Orden de Compra', $url)
            ->line('Por favor, tome acción antes de que venza el plazo.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'po_inactivity_warning',
            'po_id' => $this->po->id,
            'po_folio' => $this->po->folio,
            'total' => $this->po->total,
            'deadline' => $this->po->getAutoCloseDeadline()->toDateString(),
            'url' => route('purchase-orders.show', $this->po->id),
            'message' => 'ALERTA: La OC Estándar ' . $this->po->folio . ' será cerrada automáticamente en 3 días si no es aprobada.',
        ];
    }
}
