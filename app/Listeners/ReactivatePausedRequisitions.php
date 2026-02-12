<?php

namespace App\Listeners;

use App\Events\ProductServiceApproved;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Enum\RequisitionStatus;
use App\Notifications\RequisitionReactivatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Listener que reactivarequisiciones pausadas cuando se aprueba un producto
 *
 * PASO 3F - Crear en: app/Listeners/ReactivatePausedRequisitions.php
 */
class ReactivatePausedRequisitions implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event
     */
    public function handle(ProductServiceApproved $event): void
    {
        $product = $event->productService;

        // Buscar requisiciones PAUSADAS que tienen ítems pendientes de este producto
        // (ítems que tienen el product_service_id pero la requisición está PAUSADA)
        $pausedRequisitions = Requisition::where('status', RequisitionStatus::PAUSADA->value)
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_service_id', $product->id);
            })
            ->with(['items', 'user'])
            ->get();

        if ($pausedRequisitions->isEmpty()) {
            Log::info("Producto {$product->code} aprobado, pero no hay requisiciones pausadas esperándolo.");
            return;
        }

        Log::info("Producto {$product->code} aprobado. Reactivando " . $pausedRequisitions->count() . " requisiciones pausadas.");

        foreach ($pausedRequisitions as $requisition) {
            DB::transaction(function () use ($requisition, $product) {
                // Verificar que TODOS los ítems de la requisición tengan productos ACTIVOS
                $allItemsReady = $requisition->items->every(function ($item) {
                    return $item->productService && $item->productService->status === 'ACTIVE';
                });

                if ($allItemsReady) {
                    // ✅ Todos los productos están activos: REACTIVAR
                    $requisition->status = RequisitionStatus::PENDING->value;
                    $requisition->reactivated_by = $product->approved_by; // Quien aprobó el producto
                    $requisition->reactivated_at = now();

                    // Limpiar datos de pausa
                    $requisition->pause_reason = null;
                    $requisition->paused_by = null;
                    $requisition->paused_at = null;

                    $requisition->save();

                    // Log de actividad
                    activity()
                        ->causedBy(auth()->user() ?? $product->approver)
                        ->performedOn($requisition)
                        ->withProperties([
                            'old_status' => 'PAUSADA',
                            'new_status' => 'PENDING',
                            'product_approved' => $product->code,
                            'auto_reactivated' => true
                        ])
                        ->log("Requisición reactivada automáticamente (producto {$product->code} aprobado)");

                    // 📧 Notificar al solicitante
                    if ($requisition->user) {
                        $requisition->user->notify(
                            new RequisitionReactivatedNotification($requisition, $product)
                        );
                    }

                    Log::info("Requisición #{$requisition->id} reactivada automáticamente.");
                } else {
                    // ⏸️ Aún faltan productos por aprobar
                    $pendingProducts = $requisition->items
                        ->filter(fn($item) => $item->productService && $item->productService->status !== 'ACTIVE')
                        ->map(fn($item) => $item->productService->code)
                        ->implode(', ');

                    Log::info("Requisición #{$requisition->id} sigue pausada. Faltan productos: {$pendingProducts}");

                    // Actualizar el motivo de pausa
                    $requisition->pause_reason = "Esperando aprobación de productos: {$pendingProducts}";
                    $requisition->save();
                }
            });
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(ProductServiceApproved $event, \Throwable $exception): void
    {
        Log::error('Error al reactivar requisiciones pausadas: ' . $exception->getMessage());
    }
}
