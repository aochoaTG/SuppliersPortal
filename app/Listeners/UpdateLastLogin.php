<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastLogin
{
    /**
     * Maneja el evento de Login:
     * - Actualiza el campo last_login a "ahora"
     * - (Opcional) last_login_ip y last_login_agent si existen en tu tabla
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Actualizamos la última fecha/hora de acceso
        $user->last_login = now();

        // Si añadiste estas columnas en la tabla users, descomenta:
        // $user->last_login_ip    = request()->ip();
        // $user->last_login_agent = substr((string) request()->header('User-Agent'), 0, 255);

        // Guardamos sin disparar eventos adicionales (opcional)
        $user->save();
    }
}
