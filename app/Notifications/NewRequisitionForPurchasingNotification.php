<?php

namespace App\Notifications;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación que se envía al Departamento de Compras cuando
 * reciben una nueva requisición para cotizar
 */
class NewRequisitionForPurchasingNotification extends Notification
{
    use Queueable;

    public Requisition $requisition;

    public function __construct(Requisition $requisition)
    {
        $this->requisition = $requisition;
    }

    /**
     * Canales de notificación
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Construye el mensaje de email para Compras
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('requisitions.show', $this->requisition->id);

        return (new MailMessage)
            ->subject('🔔 Nueva Requisición para Cotizar - ' . $this->requisition->folio)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Se ha recibido una **nueva requisición** que requiere tu atención.')
            ->line('')
            ->line('**Detalles de la requisición:**')
            ->line('• **Folio:** ' . $this->requisition->folio)
            ->line('• **Solicitante:** ' . ($this->requisition->requester?->name ?? '—'))
            ->line('• **Departamento:** ' . ($this->requisition->department?->name ?? '—'))
            ->line('• **Centro de costo:** ' . ($this->requisition->costCenter?->name ?? '—'))
            ->line('• **Compañía:** ' . ($this->requisition->company?->name ?? '—'))
            ->line('• **Número de partidas:** ' . $this->requisition->items()->count())
            ->line('• **Fecha requerida:** ' . ($this->requisition->required_date ? $this->requisition->required_date->format('d/m/Y') : 'No especificada'))
            ->line('')
            ->line('**Descripción:** ' . ($this->requisition->description ?: 'Sin descripción'))
            ->line('')
            ->action('Ver Requisición', $url)
            ->line('Por favor, revisa la requisición y procede con el proceso de cotización.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Datos para la notificación en base de datos
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_requisition_for_purchasing',
            'requisition_id' => $this->requisition->id,
            'requisition_folio' => $this->requisition->folio,
            'requester_name' => $this->requisition->requester?->name ?? '—',
            'url' => route('requisitions.show', $this->requisition->id),
            'message' => 'Nueva requisición ' . $this->requisition->folio . ' recibida de ' . ($this->requisition->requester?->name ?? '—'),
        ];
    }
}
