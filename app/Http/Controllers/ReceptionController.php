<?php

namespace App\Http\Controllers;

use App\Models\DirectPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\Reception;
use App\Models\ReceivingLocation;
use App\Services\ReceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceptionController extends Controller
{
    public function __construct(private ReceptionService $receptionService)
    {
    }

    /**
     * Bandeja de órdenes pendientes de recepción.
     * Receptores ven sólo sus locaciones; buyer/authorizer ven todas.
     */
    public function pending()
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['buyer', 'authorizer', 'superadmin'])) {
            $locationIds = ReceivingLocation::active()->pluck('id');
        } else {
            // receiver: sólo sus locaciones asignadas
            $locationIds = $user->receivingLocations()->pluck('receiving_locations.id');
        }

        $pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];

        $purchaseOrders = PurchaseOrder::with(['supplier', 'receivingLocation', 'creator'])
            ->whereIn('status', $pendingStatuses)
            ->whereIn('receiving_location_id', $locationIds)
            ->orderBy('created_at', 'desc')
            ->get();

        $directOrders = DirectPurchaseOrder::with(['supplier', 'receivingLocation', 'creator'])
            ->whereIn('status', $pendingStatuses)
            ->whereIn('receiving_location_id', $locationIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('receptions.pending', compact('purchaseOrders', 'directOrders'));
    }

    /**
     * Formulario de recepción para una OC estándar.
     */
    public function create(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.requisitionItem', 'supplier', 'receivingLocation']);

        $this->authorize('useMassReception', $purchaseOrder->receivingLocation);

        $repseWarning = $this->receptionService->validateRepseIfService($purchaseOrder);

        return view('receptions.create', [
            'order'        => $purchaseOrder,
            'orderType'    => 'purchase_order',
            'storeRoute'   => route('receptions.store', $purchaseOrder),
            'repseWarning' => $repseWarning,
        ]);
    }

    /**
     * Guardar recepción de una OC estándar.
     */
    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'supplier', 'receivingLocation']);

        $this->authorize('useMassReception', $purchaseOrder->receivingLocation);

        $validated = $request->validate([
            'delivery_reference'             => 'nullable|string|max:100',
            'notes'                          => 'nullable|string|max:1000',
            'received_at'                    => 'required|date',
            'items'                          => 'required|array|min:1',
            'items.*.item_id'                => 'required|integer|exists:purchase_order_items,id',
            'items.*.quantity_received'      => 'required|numeric|min:0',
            'items.*.quantity_rejected'      => 'nullable|numeric|min:0',
            'items.*.rejection_reason'       => 'nullable|string|max:255',
        ]);

        try {
            $reception = $this->receptionService->receive(
                order:     $purchaseOrder,
                itemsData: $validated['items'],
                receiver:  Auth::user(),
                data:      $validated,
            );

            return redirect()
                ->route('receptions.show', $reception)
                ->with('success', "Recepción {$reception->folio} registrada correctamente.");

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Formulario de recepción para una OCD.
     */
    public function createDirect(DirectPurchaseOrder $directPurchaseOrder)
    {
        $directPurchaseOrder->load(['items.expenseCategory', 'supplier', 'receivingLocation']);

        $this->authorize('useMassReception', $directPurchaseOrder->receivingLocation);

        $repseWarning = $this->receptionService->validateRepseIfService($directPurchaseOrder);

        return view('receptions.create', [
            'order'        => $directPurchaseOrder,
            'orderType'    => 'direct_purchase_order',
            'storeRoute'   => route('receptions.store-direct', $directPurchaseOrder),
            'repseWarning' => $repseWarning,
        ]);
    }

    /**
     * Guardar recepción de una OCD.
     */
    public function storeDirect(Request $request, DirectPurchaseOrder $directPurchaseOrder)
    {
        $directPurchaseOrder->load(['items', 'supplier', 'receivingLocation']);

        $this->authorize('useMassReception', $directPurchaseOrder->receivingLocation);

        $validated = $request->validate([
            'delivery_reference'             => 'nullable|string|max:100',
            'notes'                          => 'nullable|string|max:1000',
            'received_at'                    => 'required|date',
            'items'                          => 'required|array|min:1',
            'items.*.item_id'                => 'required|integer|exists:odc_direct_purchase_order_items,id',
            'items.*.quantity_received'      => 'required|numeric|min:0',
            'items.*.quantity_rejected'      => 'nullable|numeric|min:0',
            'items.*.rejection_reason'       => 'nullable|string|max:255',
        ]);

        try {
            $reception = $this->receptionService->receive(
                order:     $directPurchaseOrder,
                itemsData: $validated['items'],
                receiver:  Auth::user(),
                data:      $validated,
            );

            return redirect()
                ->route('receptions.show', $reception)
                ->with('success', "Recepción {$reception->folio} registrada correctamente.");

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Detalle de una recepción registrada.
     */
    public function show(Reception $reception)
    {
        $reception->load([
            'receivable',
            'receivingLocation',
            'receiver',
            'items.receivableItem',
        ]);

        return view('receptions.show', compact('reception'));
    }
}
