<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRfqForSupplierNotification extends Notification
{
    use Queueable;

    public Rfq $rfq;

    public function __construct(Rfq $rfq)
    {
        $this->rfq = $rfq;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $portalUrl = route('supplier.rfq.show', $this->rfq->id);
        $daysUntilDeadline = now()->diffInDays($this->rfq->response_deadline, false);
        $itemsCount = $this->rfq->quotationGroup->items->count();

        return (new MailMessage)
            ->subject('🔔 Nueva Solicitud de Cotización - ' . $this->rfq->folio)
            ->greeting('Estimado proveedor,')
            ->line('**' . config('app.name') . '** te invita a participar en una nueva solicitud de cotización.')
            ->line('')
            ->line('📋 **Detalles de la solicitud:**')
            ->line('• **Folio:** ' . $this->rfq->folio)
            ->line('• **Grupo:** ' . $this->rfq->quotationGroup->name)
            ->line('• **Productos/Servicios:** ' . $itemsCount . ' partida(s)')
            ->line('• **Fecha límite:** ' . $this->rfq->response_deadline->format('d/m/Y') . ' (' . abs($daysUntilDeadline) . ' días)')
            ->line('')
            ->when($this->rfq->message, function ($mail) {
                return $mail
                    ->line('📝 **Mensaje adicional:**')
                    ->line('> ' . $this->rfq->message)
                    ->line('');
            })
            ->action('Acceder al Portal y Cotizar', $portalUrl)
            ->line('')
            ->line('**Instrucciones:**')
            ->line('1. Ingresa al Portal de Proveedores con tus credenciales')
            ->line('2. Revisa el detalle de los productos solicitados')
            ->line('3. Ingresa tus precios y condiciones comerciales')
            ->line('4. Envía tu cotización antes de la fecha límite')
            ->line('')
            ->line('⏰ **Importante:** Las cotizaciones recibidas después de la fecha límite no serán consideradas.')
            ->line('')
            ->salutation('Saludos cordiales,
' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_rfq',
            'rfq_id' => $this->rfq->id,
            'rfq_folio' => $this->rfq->folio,
            'requisition_folio' => $this->rfq->requisition->folio,
            'items_count' => $this->rfq->quotationGroup->items->count(),
            'response_deadline' => $this->rfq->response_deadline->toDateTimeString(),
            'url' => route('supplier.rfq.show', $this->rfq->id),
            'message' => 'Nueva solicitud de cotización ' . $this->rfq->folio . ' disponible. Responde antes del ' . $this->rfq->response_deadline->format('d/m/Y'),
        ];
    }
}
