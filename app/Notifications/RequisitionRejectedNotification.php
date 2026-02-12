<?php

namespace App\Notifications;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RequisitionRejectedNotification extends Notification
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
            ->subject('❌ Requisición RECHAZADA - ' . $this->requisition->folio)
            ->greeting('Atención, ' . $notifiable->name)
            ->line('Tu requisición con folio **' . $this->requisition->folio . '** ha sido rechazada por el departamento de Compras.')
            ->line('')
            ->line('**Motivo del rechazo:**')
            ->line('> ' . $this->requisition->rejection_reason)
            ->line('')
            ->line('**Detalles de la unidad:**')
            ->line('• **Departamento:** ' . $this->requisition->department->name)
            ->line('• **Centro de costo:** ' . $this->requisition->costCenter->name)
            ->line('• **Fecha del rechazo:** ' . $this->requisition->rejected_at->format('d/m/Y H:i'))
            ->line('')
            ->action('Revisar y Corregir', $url)
            ->line('Si consideras que esto es un error, contacta a tu superior. No me busques a mí, yo solo soy el mensajero.')
            ->salutation('Atentamente, El Sistema de ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'requisition_rejected',
            'requisition_id' => $this->requisition->id,
            'requisition_folio' => $this->requisition->folio,
            'rejected_by_name' => Auth::user()->name,
            'url' => route('requisitions.show', $this->requisition->id),
            'message' => 'Tu requisición ' . $this->requisition->folio . ' fue rechazada: ' . $this->requisition->rejection_reason,
        ];
    }
}
