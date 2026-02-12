<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SupplierDocument;

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
