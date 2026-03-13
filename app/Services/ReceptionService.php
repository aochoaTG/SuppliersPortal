<?php

namespace App\Services;

use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Reception;
use App\Models\ReceptionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceptionService
{
    /**
     * Orquesta la recepción completa de una orden dentro de una transacción.
     * Este es el único punto de entrada que el controlador debe usar.
     *
     * @param  PurchaseOrder|DirectPurchaseOrder  $order
     * @param  array  $itemsData  Formato esperado por elemento:
     *                            ['item_id' => int, 'quantity_received' => float,
     *                             'quantity_rejected' => float, 'rejection_reason' => ?string]
     * @param  User   $receiver
     * @param  array  $data       ['delivery_reference' => ?string, 'notes' => ?string, 'received_at' => ?Carbon]
     *
     * @throws \RuntimeException  Si la orden no puede recibirse (ver validateCanReceive).
     */
    public function receive(Model $order, array $itemsData, User $receiver, array $data): Reception
    {
        $this->validateCanReceive($order);

        return DB::transaction(function () use ($order, $itemsData, $receiver, $data) {

            // 1. Crear cabecera de recepción
            $reception = Reception::create([
                'folio'                 => Reception::generateNextFolio(),
                'receivable_type'       => get_class($order),
                'receivable_id'         => $order->id,
                'receiving_location_id' => $order->receiving_location_id,
                'received_by'           => $receiver->id,
                'status'                => Reception::STATUS_PENDING,
                'delivery_reference'    => $data['delivery_reference'] ?? null,
                'notes'                 => $data['notes'] ?? null,
                'received_at'           => $data['received_at'] ?? now(),
            ]);

            // 2. Procesar cada línea recibida
            $itemClass = $this->resolveItemClass($order);

            foreach ($itemsData as $lineData) {
                $item             = $itemClass::findOrFail($lineData['item_id']);
                $quantityReceived = max(0, (float) ($lineData['quantity_received'] ?? 0));
                $quantityRejected = max(0, (float) ($lineData['quantity_rejected'] ?? 0));
                $accepted         = $quantityReceived - $quantityRejected;

                ReceptionItem::create([
                    'reception_id'         => $reception->id,
                    'receivable_item_type' => get_class($item),
                    'receivable_item_id'   => $item->id,
                    'quantity_received'    => $quantityReceived,
                    'quantity_rejected'    => $quantityRejected,
                    'rejection_reason'     => $lineData['rejection_reason'] ?? null,
                ]);

                // Acumular en el ítem de la orden. Se usa increment() para evitar
                // disparar los eventos 'saving/saved' del modelo (no recalcula montos).
                if ($accepted > 0) {
                    $item->increment('quantity_received', $accepted);
                }
            }

            // 3. Recalcular el estado agregado de la orden
            $newOrderStatus = $this->calculateOrderReceptionStatus($order);

            // 4. Actualizar estado de la recepción en función del resultado
            $reception->update([
                'status' => $newOrderStatus === 'RECEIVED'
                    ? Reception::STATUS_COMPLETED
                    : Reception::STATUS_PARTIAL,
            ]);

            // 5. Persistir el nuevo estado en la orden
            $this->updateOrderStatus($order, $newOrderStatus, $receiver);

            // 6. Si la orden quedó totalmente recibida, marcar el compromiso presupuestal
            if ($newOrderStatus === 'RECEIVED') {
                $this->markBudgetAsReceived($order);
            }

            Log::info("Recepción {$reception->folio} registrada para la orden {$order->folio}.");

            return $reception->load('items.receivableItem');
        });
    }

    /**
     * Verifica que una orden puede pasar al flujo de recepción.
     * Lanza RuntimeException con mensaje legible si hay algún impedimento.
     *
     * @throws \RuntimeException
     */
    public function validateCanReceive(Model $order): void
    {
        if (! $order->canBeReceived()) {
            throw new \RuntimeException(
                "La orden {$order->folio} no puede recibirse en su estado actual ({$order->getStatusLabel()})."
            );
        }

        $location = $order->receivingLocation;

        if (! $location) {
            throw new \RuntimeException(
                "La orden {$order->folio} no tiene una ubicación de recepción asignada."
            );
        }

        if (! $location->is_active) {
            throw new \RuntimeException(
                "La ubicación '{$location->name}' está inactiva y no puede recibir mercancía."
            );
        }

        if ($location->portal_blocked) {
            throw new \RuntimeException(
                "La ubicación '{$location->name}' tiene el portal bloqueado. Contacta al Administrador."
            );
        }
    }

    /**
     * Verifica el estado del REPSE del proveedor cuando la orden involucra servicios.
     *
     * Para OCD: solo aplica si al menos un ítem tiene categoría "Servicio" (código SER).
     * Para OC estándar: aplica si el proveedor está marcado como prestador de servicios
     *                   especializados (los ítems de OC no tienen categoría de gasto).
     *
     * No lanza excepción — devuelve un mensaje de advertencia o null.
     * El controlador decide cómo mostrarlo al usuario.
     */
    public function validateRepseIfService(Model $order): ?string
    {
        $supplier = $order->supplier;

        if (! $supplier || ! $supplier->requiresRepseRegistration()) {
            return null;
        }

        // Para OCD: solo alertar si la orden contiene al menos un ítem de Servicios
        if ($order instanceof DirectPurchaseOrder) {
            $order->loadMissing('items.expenseCategory');
            $hasServiceItems = $order->items->contains(
                fn($item) => $item->expenseCategory?->isService() ?? false
            );

            if (! $hasServiceItems) {
                return null;
            }
        }

        if (! $supplier->hasValidRepseRegistration()) {
            return "El proveedor '{$supplier->company_name}' tiene el registro REPSE vencido o sin número de registro. "
                . "Consulta con el área de Compras antes de continuar.";
        }

        $daysLeft = $supplier->repseExpiresIn();
        if ($daysLeft !== null && $daysLeft <= 30) {
            return "El REPSE del proveedor '{$supplier->company_name}' vence en {$daysLeft} día(s). "
                . "Notifica a Compras para su renovación.";
        }

        return null;
    }

    /**
     * Determina el nuevo estado de la orden examinando todos sus ítems.
     * Recarga los ítems desde BD para obtener los valores ya actualizados por increment().
     *
     * @return 'RECEIVED'|'PARTIALLY_RECEIVED'
     */
    public function calculateOrderReceptionStatus(Model $order): string
    {
        $items = $order->items()->get();

        if ($items->isEmpty()) {
            return 'RECEIVED';
        }

        return $items->every(fn($item) => $item->isFullyReceived())
            ? 'RECEIVED'
            : 'PARTIALLY_RECEIVED';
    }

    /**
     * Actualiza el estado de la orden y los timestamps de auditoría correspondientes.
     */
    public function updateOrderStatus(Model $order, string $newStatus, User $receiver): void
    {
        $updates = ['status' => $newStatus];

        if ($newStatus === 'RECEIVED') {
            $updates['received_by'] = $receiver->id;
            $updates['received_at'] = now();
        }

        $order->update($updates);
    }

    // ─── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Resuelve la clase del modelo de ítem según el tipo de orden.
     *
     * @throws \RuntimeException
     */
    private function resolveItemClass(Model $order): string
    {
        return match (true) {
            $order instanceof PurchaseOrder       => PurchaseOrderItem::class,
            $order instanceof DirectPurchaseOrder => DirectPurchaseOrderItem::class,
            default => throw new \RuntimeException(
                'Tipo de orden no soportado para recepción: ' . get_class($order)
            ),
        };
    }

    /**
     * Marca el compromiso presupuestal como recibido si la orden tiene uno asociado.
     */
    private function markBudgetAsReceived(Model $order): void
    {
        $order->budgetCommitment?->markAsReceived();
    }
}
