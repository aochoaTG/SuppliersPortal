<?php

namespace App\Notifications;

use App\Models\DirectPurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectPurchaseOrderClosedByInactivityNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly DirectPurchaseOrder $ocd) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔴 OC Directa ' . $this->ocd->folio . ' cerrada por inactividad')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('La siguiente Orden de Compra Directa ha sido **cerrada automáticamente por inactividad** al superar los ' . DirectPurchaseOrder::INACTIVITY_DAYS . ' días naturales sin ser aprobada.')
            ->line('')
            ->line('**Detalles de la OCD:**')
            ->line('• **Folio:** ' . $this->ocd->folio)
            ->line('• **Proveedor:** ' . ($this->ocd->supplier->company_name ?? 'N/A'))
            ->line('• **Centro de Costo:** ' . ($this->ocd->costCenter->name ?? 'N/A'))
            ->line('• **Monto Total:** $' . number_format($this->ocd->total, 2) . ' ' . $this->ocd->currency)
            ->line('• **Solicitante:** ' . ($this->ocd->creator->name ?? 'N/A'))
            ->line('• **Enviada a aprobación:** ' . ($this->ocd->submitted_at?->format('d/m/Y H:i') ?? 'N/A'))
            ->line('• **Cerrada el:** ' . ($this->ocd->closed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')))
            ->line('')
            ->line('**Efectos del cierre:**')
            ->line('• La OCD ya **NO puede ser aprobada**.')
            ->line('• El proveedor **NO puede cargar remisiones** contra esta OC.')
            ->line('• Si el producto/servicio sigue siendo necesario, debe generarse una **nueva requisición**.')
            ->line('')
            ->line('Este es un mensaje automático del sistema.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ocd_closed_by_inactivity',
            'ocd_id' => $this->ocd->id,
            'ocd_folio' => $this->ocd->folio,
            'total' => $this->ocd->total,
            'closed_at' => $this->ocd->closed_at?->toDateTimeString(),
            'url' => route('direct-purchase-orders.show', $this->ocd->id),
            'message' => 'La OC Directa ' . $this->ocd->folio . ' fue cerrada automáticamente por inactividad.',
        ];
    }
}
