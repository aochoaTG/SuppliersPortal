<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate; // 👈 AGREGAR ESTA LÍNEA
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
    }
}