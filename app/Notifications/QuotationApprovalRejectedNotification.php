<?php

namespace App\Notifications;

use App\Models\QuotationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationApprovalRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public QuotationSummary $summary) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cotización rechazada - '.($this->summary->rfq?->folio ?? 'RFQ'))
            ->greeting('Hola '.$notifiable->name.',')
            ->line('La cotización adjudicada fue rechazada y regresó a evaluación.')
            ->line('RFQ: '.($this->summary->rfq?->folio ?? 'N/A'))
            ->line('Requisición: '.($this->summary->requisition?->folio ?? 'N/A'))
            ->line('Motivo: '.($this->summary->rejection_reason ?? 'Sin motivo registrado'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_approval_rejected',
            'summary_id' => $this->summary->id,
            'rfq_folio' => $this->summary->rfq?->folio,
            'url' => route('rfq.comparison.index', $this->summary->rfq_id),
            'message' => 'La cotización de la RFQ '.($this->summary->rfq?->folio ?? 'N/A').' fue rechazada.',
        ];
    }
}
