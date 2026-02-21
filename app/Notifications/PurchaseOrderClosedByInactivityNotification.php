<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderClosedByInactivityNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly PurchaseOrder $po) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔴 OC Estándar ' . $this->po->folio . ' cerrada por inactividad')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('La siguiente Orden de Compra Estándar ha sido **cerrada automáticamente por inactividad** al superar los ' . PurchaseOrder::INACTIVITY_DAYS . ' días naturales sin ser aprobada.')
            ->line('')
            ->line('**Detalles de la OC:**')
            ->line('• **Folio:** ' . $this->po->folio)
            ->line('• **Proveedor:** ' . ($this->po->supplier->company_name ?? 'N/A'))
            ->line('• **Monto Total:** $' . number_format($this->po->total, 2) . ' ' . $this->po->currency)
            ->line('• **Generada el:** ' . $this->po->created_at->format('d/m/Y H:i'))
            ->line('• **Cerrada el:** ' . ($this->po->closed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')))
            ->line('')
            ->line('**Efectos del cierre:**')
            ->line('• La OC ya **NO puede ser aprobada**.')
            ->line('• El proveedor **NO puede cargar remisiones** contra esta OC.')
            ->line('• El presupuesto comprometido ha sido **liberado y devuelto a Disponible**.')
            ->line('• Si el producto/servicio sigue siendo necesario, debe generarse una **nueva requisición**.')
            ->line('')
            ->line('Este es un mensaje automático del sistema.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'po_closed_by_inactivity',
            'po_id' => $this->po->id,
            'po_folio' => $this->po->folio,
            'total' => $this->po->total,
            'closed_at' => $this->po->closed_at?->toDateTimeString(),
            'url' => route('purchase-orders.show', $this->po->id),
            'message' => 'La OC Estándar ' . $this->po->folio . ' fue cerrada automáticamente por inactividad.',
        ];
    }
}
