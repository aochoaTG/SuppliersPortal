<?php

namespace App\Notifications;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RequisitionInQuotationNotification extends Notification
{
    use Queueable;

    public Requisition $requisition;

    public function __construct(Requisition $requisition)
    {
        $this->requisition = $requisition;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('requisitions.show', $this->requisition->id);

        return (new MailMessage)
            ->subject('Requisición EN COTIZACIÓN - ' . $this->requisition->folio)
            ->greeting('¡Buenas noticias, ' . $notifiable->name . '!')
            ->line('Tu requisición con folio **' . $this->requisition->folio . '** ha sido validada por el departamento de Compras.')
            ->line('')
            ->line('El departamento de Compras procederá a solicitar cotizaciones a los proveedores para los productos y servicios solicitados.')
            ->line('')
            ->line('**Detalles de la requisición:**')
            ->line('• **Centro de costo:** ' . $this->requisition->costCenter->name)
            ->line('• **Partidas:** ' . $this->requisition->items->count() . ' producto(s)/servicio(s)')
            ->line('• **Fecha de validación:** ' . now()->format('d/m/Y H:i'))
            ->line('')
            ->action('Ver Requisición', $url)
            ->line('Te notificaremos cuando se reciban las cotizaciones y se proceda con la compra.')
            ->salutation('Atentamente, El Sistema de ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'requisition_in_quotation',
            'requisition_id' => $this->requisition->id,
            'requisition_folio' => $this->requisition->folio,
            'validated_by_name' => Auth::user()->name,
            'url' => route('requisitions.show', $this->requisition->id),
            'message' => 'Tu requisición ' . $this->requisition->folio . ' ha sido validada y se procederá con la cotización.',
        ];
    }
}
