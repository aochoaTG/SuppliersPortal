<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLockScreen
{
    /**
     * If the session is locked, redirect to lock screen
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Limpieza: si el usuario autenticado cambió, borra el lock
            if (session()->has('lock.user_id') && session('lock.user_id') !== Auth::id()) {
                session()->forget('lock');
            }

            $isLocked = session('lock.is_locked', false);
            $lockedUserId = session('lock.user_id');

            $isUnlockRoute = $request->routeIs([
                'lockscreen.show',
                'lockscreen.unlock',
                'lockscreen.lock',
                'logout',
            ]);

            if ($isLocked && $lockedUserId === Auth::id() && !$isUnlockRoute) {
                return redirect()->route('lockscreen.show');
            }
        }

        return $next($request);
    }
}
