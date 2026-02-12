<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LockScreenController extends Controller
{
    /**
     * Set lock state in session and redirect to lock screen
     */
    public function lock(Request $request)
    {
        // Guarda a dónde queríamos ir (para "intended" post-desbloqueo)
        if (!$request->session()->has('url.intended')) {
            $request->session()->put('url.intended', url()->previous());
        }

        $request->session()->put('lock.is_locked', true);
        $request->session()->put('lock.user_id', Auth::id());

        return redirect()->route('lockscreen.show');
    }

    /**
     * Show lock screen view
     */
    public function show(Request $request)
    {
        // Si no está bloqueado, manda al dashboard
        if (!Auth::check() || !session('lock.is_locked', false)) {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();

        return view('auth.lock-screen', [
            'user' => $user,
        ]);
    }

    /**
     * Validate password and unlock
     */
    public function unlock(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (!$user) {
            // Si por algún motivo ya no hay sesión, redirige a login
            return redirect()->route('login');
        }

        if (Hash::check($request->input('password'), $user->password)) {
            // Desbloquear
            $request->session()->forget('lock');

            // Redirigir a donde quería ir (o dashboard por defecto)
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'password' => 'La contraseña es incorrecta.',
        ])->withInput();
    }
}
