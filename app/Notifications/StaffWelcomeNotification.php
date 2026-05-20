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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido al Portal de Proveedores TotalGas')
            ->greeting('Hola '.$notifiable->name.'!')
            ->line('Tu cuenta de acceso al Portal de Proveedores TotalGas ha sido creada.')
            ->line('')
            ->line('Usuario: '.$notifiable->email)
            ->line('Contrasena: '.$this->plainPassword)
            ->line('')
            ->action('Iniciar sesion', route('login'))
            ->line('Por seguridad, te recomendamos cambiar tu contrasena despues de tu primer acceso.')
            ->salutation('Saludos, '.config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_welcome',
            'url' => route('dashboard'),
            'message' => 'Tu cuenta del portal fue creada y ya puedes ingresar con las credenciales enviadas por correo.',
        ];
    }
}
