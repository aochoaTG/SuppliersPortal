<?php

namespace App\Notifications;

use App\Models\QuotationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationApprovalApprovedNotification extends Notification
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
            ->subject('Cotización aprobada - '.($this->summary->rfq?->folio ?? 'RFQ'))
            ->greeting('Hola '.$notifiable->name.',')
            ->line('La cotización adjudicada fue aprobada y ya puede continuar su ciclo operativo.')
            ->line('RFQ: '.($this->summary->rfq?->folio ?? 'N/A'))
            ->line('Requisición: '.($this->summary->requisition?->folio ?? 'N/A'))
            ->line('Proveedor adjudicado: '.($this->summary->selectedSupplier?->company_name ?? 'N/A'))
            ->line('Monto total con IVA: $'.number_format((float) $this->summary->total, 2));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_approval_approved',
            'summary_id' => $this->summary->id,
            'rfq_folio' => $this->summary->rfq?->folio,
            'url' => route('purchase-orders.index'),
            'message' => 'La cotización de la RFQ '.($this->summary->rfq?->folio ?? 'N/A').' fue aprobada.',
        ];
    }
}
