<?php

namespace App\Notifications;

use App\Models\ProductService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación que se envía al Administrador del Catálogo
 * cuando un usuario solicita un nuevo producto desde una requisición
 *
 * PASO 3D - Crear en: app/Notifications/NewProductRequestedNotification.php
 */
class NewProductRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ProductService $productService;
    public User $requestedBy;

    /**
     * Constructor
     */
    public function __construct(ProductService $productService, User $requestedBy)
    {
        $this->productService = $productService;
        $this->requestedBy = $requestedBy;
    }

    /**
     * Canales de notificación
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Construye el mensaje de email
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('products-services.show', $this->productService->id);

        return (new MailMessage)
            ->subject('🆕 Nuevo Producto Solicitado - Requiere Aprobación')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Se ha solicitado el alta de un nuevo producto en el catálogo.')
            ->line('**Solicitado por:** ' . $this->requestedBy->name)
            ->line('**Código:** ' . $this->productService->code)
            ->line('**Descripción:** ' . \Str::limit($this->productService->technical_description, 100))
            ->line('**Categoría:** ' . $this->productService->category?->name)
            ->line('**Centro de Costo:** ' . $this->productService->costCenter?->name)
            ->line('**Precio Estimado:** $' . number_format($this->productService->estimated_price, 2) . ' ' . $this->productService->currency_code)
            ->line('')
            ->line('⚠️ **Acción requerida:** Este producto fue solicitado desde una requisición y **NO tiene estructura contable completa**.')
            ->line('Por favor, revisa el producto y completa la estructura contable (Cuenta Mayor, Subcuenta, Subsubcuenta) antes de aprobarlo.')
            ->action('Ver Producto y Completar Datos', $url)
            ->line('')
            ->line('Una vez aprobado, el producto estará disponible para requisiciones y la requisición pausada se reactivará automáticamente.')
            ->line('¡Gracias por mantener actualizado nuestro catálogo!')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Datos para la notificación en base de datos
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_product_requested',
            'product_service_id' => $this->productService->id,
            'product_code' => $this->productService->code,
            'product_description' => $this->productService->technical_description,
            'requested_by_id' => $this->requestedBy->id,
            'requested_by_name' => $this->requestedBy->name,
            'url' => route('products-services.show', $this->productService->id),
            'message' => 'Nuevo producto solicitado: ' . $this->productService->code,
        ];
    }
}
