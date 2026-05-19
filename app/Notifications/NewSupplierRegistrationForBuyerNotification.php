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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.review.suppliers.show', $this->supplier->id);

        return (new MailMessage)
            ->subject('Nuevo proveedor registrado para revision - '.$this->supplier->company_name)
            ->greeting('Hola '.$notifiable->name.'!')
            ->line('Se registró un nuevo proveedor en el portal y su alta quedó lista para revisión por Compras.')
            ->line('')
            ->line('Razon social: '.($this->supplier->company_name ?: '-'))
            ->line('RFC: '.($this->supplier->rfc ?: '-'))
            ->line('Contacto: '.($this->supplier->contact_person ?: '-'))
            ->line('Correo: '.($this->supplier->email ?: '-'))
            ->line('Tipo de proveedor: '.$this->formatSupplierType($this->supplier->supplier_type))
            ->line('')
            ->action('Revisar proveedor', $url)
            ->line('Ingresa al portal para revisar su expediente y continuar con el proceso de alta.')
            ->salutation('Saludos, '.config('mail.from.name', 'Portal de Proveedores'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_supplier_registration',
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->company_name,
            'supplier_rfc' => $this->supplier->rfc,
            'url' => route('admin.review.suppliers.show', $this->supplier->id),
            'message' => 'Se registró el proveedor '.$this->supplier->company_name.' y está pendiente de revisión.',
        ];
    }

    private function formatSupplierType(?string $supplierType): string
    {
        return match ($supplierType) {
            'product' => 'Productos',
            'service' => 'Servicios',
            'product_service' => 'Productos y Servicios',
            default => $supplierType ?: '-',
        };
    }
}
