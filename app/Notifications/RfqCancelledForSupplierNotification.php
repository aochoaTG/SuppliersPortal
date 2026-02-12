<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class RfqCancelledForSupplierNotification extends Notification
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
        $portalUrl = Route::has('supplier.dashboard') ? route('supplier.dashboard') : '#';

        return (new MailMessage)
            ->subject('🚫 Solicitud de Cotización Cancelada - ' . $this->rfq->folio)
            ->greeting('Estimado proveedor,')
            ->line('Le informamos que la solicitud de cotización **' . $this->rfq->folio . '** ha sido **cancelada**.')
            ->line('')
            ->line('📋 **Detalles de la RFQ cancelada:**')
            ->line('• **Folio:** ' . $this->rfq->folio)
            ->line('• **Grupo:** ' . $this->rfq->quotationGroup->name)
            ->line('• **Fecha de cancelación:** ' . now()->format('d/m/Y H:i'))
            ->when($this->reason, function ($mail) {
                return $mail
                    ->line('')
                    ->line('**Motivo de la cancelación:**')
                    ->line('> ' . $this->reason);
            })
            ->line('')
            ->line('**No es necesario que envíe una cotización para esta solicitud.**')
            ->line('')
            ->action('Ir al Portal', $portalUrl)
            ->line('Agradecemos su tiempo e interés. Esperamos seguir trabajando juntos en futuras oportunidades.')
            ->salutation('Saludos cordiales,
' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rfq_cancelled_for_supplier',
            'rfq_id' => $this->rfq->id,
            'rfq_folio' => $this->rfq->folio,
            'reason' => $this->reason,
            'url' => Route::has('supplier.dashboard') ? route('supplier.dashboard') : '#',
            'message' => 'RFQ ' . $this->rfq->folio . ' ha sido cancelada. No es necesario enviar cotización.',
        ];
    }
}
