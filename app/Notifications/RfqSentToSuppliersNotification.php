<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RfqSentToSuppliersNotification extends Notification
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
        $requisitionUrl = route('requisitions.show', $this->rfq->requisition_id);
        $suppliersCount = $this->rfq->suppliers->count();
        $suppliersList = $this->rfq->suppliers->pluck('name')->join(', ');

        return (new MailMessage)
            ->subject('📨 Solicitud de Cotización Enviada - ' . $this->rfq->folio)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Se ha enviado una solicitud de cotización a **' . $suppliersCount . ' proveedor(es)** para tu requisición **' . $this->rfq->requisition->folio . '**.')
            ->line('')
            ->line('**Detalles de la RFQ:**')
            ->line('• **Folio RFQ:** ' . $this->rfq->folio)
            ->line('• **Grupo de cotización:** ' . $this->rfq->quotationGroup->name)
            ->line('• **Proveedores invitados:** ' . $suppliersList)
            ->line('• **Fecha límite de respuesta:** ' . $this->rfq->response_deadline->format('d/m/Y'))
            ->line('• **Fecha de envío:** ' . $this->rfq->sent_at->format('d/m/Y H:i'))
            ->line('')
            ->action('Ver Requisición', $requisitionUrl)
            ->line('Te notificaremos cuando los proveedores envíen sus cotizaciones.')
            ->salutation('Atentamente, El Sistema de ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rfq_sent_to_suppliers',
            'rfq_id' => $this->rfq->id,
            'rfq_folio' => $this->rfq->folio,
            'requisition_id' => $this->rfq->requisition_id,
            'requisition_folio' => $this->rfq->requisition->folio,
            'suppliers_count' => $this->rfq->suppliers->count(),
            'sent_by_name' => Auth::user()->name,
            'url' => route('requisitions.show', $this->rfq->requisition_id),
            'message' => 'RFQ ' . $this->rfq->folio . ' enviada a ' . $this->rfq->suppliers->count() . ' proveedor(es) para tu requisición ' . $this->rfq->requisition->folio,
        ];
    }
}
