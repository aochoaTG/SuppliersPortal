<?php

namespace App\Notifications;

use App\Models\QuotationSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationApprovalRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public QuotationSummary $summary,
        public bool $escalated = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('approvals.quotations.index');
        $subjectPrefix = $this->escalated ? 'Escalación de aprobación' : 'Nueva aprobación de cotización';

        return (new MailMessage)
            ->subject($subjectPrefix.' - '.($this->summary->rfq?->folio ?? 'RFQ'))
            ->greeting('Hola '.$notifiable->name.',')
            ->line('Tienes una cotización adjudicada pendiente de autorización.')
            ->line('RFQ: '.($this->summary->rfq?->folio ?? 'N/A'))
            ->line('Requisición: '.($this->summary->requisition?->folio ?? 'N/A'))
            ->line('Proveedor adjudicado: '.($this->summary->selectedSupplier?->company_name ?? 'N/A'))
            ->line('Monto total con IVA: $'.number_format((float) $this->summary->total, 2))
            ->action('Revisar aprobación', $url)
            ->line('Gracias por tu revisión.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_approval_request',
            'summary_id' => $this->summary->id,
            'rfq_id' => $this->summary->rfq_id,
            'rfq_folio' => $this->summary->rfq?->folio,
            'requisition_folio' => $this->summary->requisition?->folio,
            'total' => (float) $this->summary->total,
            'escalated' => $this->escalated,
            'url' => route('approvals.quotations.index'),
            'message' => 'Cotización pendiente de aprobación para la RFQ '.($this->summary->rfq?->folio ?? 'N/A').'.',
        ];
    }
}
