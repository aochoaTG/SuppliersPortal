<?php

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupplierRegistrationForBuyerNotification extends Notification
{
    use Queueable;

    public function __construct(public Supplier $supplier) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.review.suppliers.show', $this->supplier->id);

        return (new MailMessage)
            ->subject('Nuevo proveedor registrado para revisión - ' . $this->supplier->company_name)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Se ha registrado un **nuevo proveedor** en el portal y su alta quedó lista para revisión por Compras.')
            ->line('')
            ->line('**Datos del proveedor:**')
            ->line('• **Razón social:** ' . ($this->supplier->company_name ?: '—'))
            ->line('• **RFC:** ' . ($this->supplier->rfc ?: '—'))
            ->line('• **Contacto:** ' . ($this->supplier->contact_person ?: '—'))
            ->line('• **Correo:** ' . ($this->supplier->email ?: '—'))
            ->line('• **Tipo de proveedor:** ' . $this->formatSupplierType($this->supplier->supplier_type))
            ->line('')
            ->action('Revisar proveedor', $url)
            ->line('Por favor, ingresa al portal para revisar su expediente y continuar con el proceso de alta.')
            ->salutation('Saludos, ' . config('mail.from.name', 'Portal de Proveedores'));
    }

    private function formatSupplierType(?string $supplierType): string
    {
        return match ($supplierType) {
            'product' => 'Productos',
            'service' => 'Servicios',
            'product_service' => 'Productos y Servicios',
            default => $supplierType ?: '—',
        };
    }
}
