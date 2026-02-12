<?php

namespace App\Notifications;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación que se envía al solicitante cuando su requisición
 * es enviada a Compras para aprobación y cotización
 */
class RequisitionSubmittedNotification extends Notification
{
    use Queueable;

    public Requisition $requisition;

    /**
     * Constructor
     */
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
     * Construye el mensaje de email
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('requisitions.show', $this->requisition->id);

        return (new MailMessage)
            ->subject('📤 Tu Requisición ha sido enviada a Compras - ' . $this->requisition->folio)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu requisición ha sido  **enviada exitosamente** al departamento de Compras.')
            ->line('**Folio:** ' . $this->requisition->folio)
            ->line('**Fecha de envío:** ' . now()->format('d/m/Y H:i'))
            ->line('')
            ->line('**Detalles de la requisición:**')
            ->line('• Centro de costo: ' . $this->requisition->costCenter->name)
            ->line('• Departamento: ' . $this->requisition->department->name)
            ->line('• Número de partidas: ' . $this->requisition->items()->count())
            ->line('• Fecha requerida: ' . ($this->requisition->required_date ? $this->requisition->required_date->format('d/m/Y') : 'No especificada'))
            ->line('')
            ->line('**Estado actual:** Pendiente de Cotización')
            ->line('')
            ->line('**Próximos pasos:**')
            ->line('1. Revisión por el departamento de Compras')
            ->line('2. Proceso de cotización con proveedores')
            ->line('3. Aprobación final')
            ->line('')
            ->action('Ver Requisición', $url)
            ->line('Te mantendremos informado sobre el progreso de tu requisición.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Datos para la notificación en base de datos
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'requisition_submitted',
            'requisition_id' => $this->requisition->id,
            'requisition_folio' => $this->requisition->folio,
            'url' => route('requisitions.show', $this->requisition->id),
            'message' => 'Tu requisición ' . $this->requisition->folio . ' ha sido enviada a Compras.',
        ];
    }
}
