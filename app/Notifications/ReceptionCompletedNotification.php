<?php

namespace App\Notifications;

use App\Models\Reception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación enviada al Comprador (creador de la OC) y al equipo de Compras
 * cuando se registra una recepción (total o parcial) para una OC estándar u OCD.
 */
class ReceptionCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Reception $reception) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reception = $this->reception;
        $order     = $reception->receivable;
        $isComplete = $reception->isCompleted();

        $icon        = $isComplete ? '✅' : '🔶';
        $statusLabel = $isComplete ? 'COMPLETADA' : 'PARCIAL';

        $url = route('receptions.show', $reception->id);

        $mail = (new MailMessage)
            ->subject("{$icon} Recepción {$reception->folio} ({$statusLabel}) — {$order->folio}")
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line("Se ha registrado una **recepción {$statusLabel}** para la orden **{$order->folio}**.")
            ->line('')
            ->line('**Detalles de la recepción:**')
            ->line('• **Folio recepción:** ' . $reception->folio)
            ->line('• **Orden de compra:** ' . $order->folio)
            ->line('• **Proveedor:** ' . ($order->supplier->company_name ?? 'N/A'))
            ->line('• **Punto de entrega:** ' . ($reception->receivingLocation->name ?? 'N/A'))
            ->line('• **Recibió:** ' . ($reception->receiver->name ?? 'N/A'))
            ->line('• **Fecha de recepción:** ' . $reception->received_at->format('d/m/Y H:i'))
            ->line('• **Estado de la orden:** ' . $order->getStatusLabel());

        if ($reception->delivery_reference) {
            $mail->line('• **Referencia del proveedor:** ' . $reception->delivery_reference);
        }

        if (! $isComplete) {
            $mail->line('')
                 ->line('⚠️ La orden aún tiene partidas pendientes de recepción. Se esperan entregas adicionales del proveedor.');
        }

        return $mail
            ->line('')
            ->action('Ver Comprobante de Recepción', $url)
            ->salutation('Saludos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->reception->receivable;

        return [
            'type'             => 'reception_completed',
            'reception_id'     => $this->reception->id,
            'reception_folio'  => $this->reception->folio,
            'reception_status' => $this->reception->status,
            'order_id'         => $order->id,
            'order_folio'      => $order->folio,
            'url'              => route('receptions.show', $this->reception->id),
            'message'          => "Recepción {$this->reception->folio} ({$this->reception->getStatusLabel()}) registrada para la orden {$order->folio}.",
        ];
    }
}
