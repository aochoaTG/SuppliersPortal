<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RfqCancelledForRequesterNotification extends Notification
{
    use Queueable;

    public Rfq $rfq;
    public ?string $reason;

    public function __construct(Rfq $rfq, ?string $reason = null)
    {
        $this->rfq = $rfq;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requisitionUrl = Route::has('requisitions.show') ? route('requisitions.show', $this->rfq->requisition_id) : '#';

        return (new MailMessage)
            ->subject('🚫 Solicitud de Cotización Cancelada - ' . $this->rfq->folio)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('La solicitud de cotización **' . $this->rfq->folio . '** relacionada con tu requisición **' . $this->rfq->requisition->folio . '** ha sido cancelada.')
            ->line('')
            ->line('**Detalles:**')
            ->line('• **Folio RFQ:** ' . $this->rfq->folio)
            ->line('• **Grupo:** ' . $this->rfq->quotationGroup->name)
            ->line('• **Cancelada por:** ' . (Auth::user()->name ?? 'Sistema'))
            ->line('• **Fecha de cancelación:** ' . now()->format('d/m/Y H:i'))
            ->when($this->reason, function ($mail) {
                return $mail
                    ->line('')
                    ->line('**Motivo de la cancelación:**')
                    ->line('> ' . $this->reason);
            })
            ->line('')
            ->action('Ver Requisición', $requisitionUrl)
            ->line('El departamento de Compras tomará las acciones correspondientes.')
            ->salutation('Atentamente, El Sistema de ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rfq_cancelled_for_requester',
            'rfq_id' => $this->rfq->id,
            'rfq_folio' => $this->rfq->folio,
            'requisition_id' => $this->rfq->requisition_id,
            'requisition_folio' => $this->rfq->requisition->folio,
            'cancelled_by_name' => Auth::user()->name ?? 'Sistema',
            'reason' => $this->reason,
            'url' => Route::has('requisitions.show') ? route('requisitions.show', $this->rfq->requisition_id) : '#',
            'message' => 'RFQ ' . $this->rfq->folio . ' cancelada para tu requisición ' . $this->rfq->requisition->folio,
        ];
    }
}
