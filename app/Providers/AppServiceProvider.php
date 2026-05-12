<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate; // 👈 AGREGAR ESTA LÍNEA
use App\Models\ExchangeRate;
use App\Models\SupplierDocument;
use App\Models\ReceivingLocation; // 👈 AGREGAR ESTA LÍNEA
use App\Policies\ReceivingLocationPolicy; // 👈 AGREGAR ESTA LÍNEA

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 👇 REGISTRAR LA POLICY PARA RECEIVINGLOCATION
        Gate::policy(ReceivingLocation::class, ReceivingLocationPolicy::class);

        // Inyectar el número de documentos pendientes en el sidebar
        View::composer('layouts.partials.sidebar', function ($view) {
            try {
                $pendingCount = SupplierDocument::where('status', 'pending_review')->count();
            } catch (\Throwable $e) {
                // Si aún no se han ejecutado migraciones, evita que truene
                $pendingCount = 0;
            }

            $view->with('pendingReviewCount', $pendingCount);
        });

        // Compartir siempre la variable para evitar excepciones en vistas que la esperan.
        View::share('exchangeRate', null);

        // Tipo de cambio USD/MXN solo para pantallas autenticadas que muestran navbar.
        if (! $this->app->runningInConsole() && request()->user()) {
            View::share(
                'exchangeRate',
                rescue(
                    fn () => Cache::remember('exchange_rate_usd_mxn_current', 300, fn () => ExchangeRate::current('USD', 'MXN')),
                    null
                )
            );
        }
    }
}
