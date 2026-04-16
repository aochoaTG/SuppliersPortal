<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $plainPassword) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido al Portal de Proveedores TotalGas')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu cuenta de acceso al **Portal de Proveedores TotalGas** ha sido creada.')
            ->line('')
            ->line('**Tus credenciales de acceso:**')
            ->line('• **Usuario:** ' . $notifiable->email)
            ->line('• **Contraseña:** ' . $this->plainPassword)
            ->line('')
            ->action('Iniciar sesión', route('login'))
            ->line('Por seguridad, te recomendamos cambiar tu contraseña después de tu primer acceso.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
