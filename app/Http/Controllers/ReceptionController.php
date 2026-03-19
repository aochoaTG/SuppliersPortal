<?php

namespace App\Http\Controllers;

use App\Models\DirectPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Models\Reception;
use App\Models\ReceivingLocation;
use App\Services\ReceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ReceptionController extends Controller
{
    public function __construct(private ReceptionService $receptionService)
    {
    }

    /**
     * Vista general de todas las OC y OCD pendientes de recepción (todos los usuarios).
     * Muestra contadores por tipo para los badges de los tabs.
     */
    public function overview()
    {
        $pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];

        $regularCount = PurchaseOrder::whereIn('status', $pendingStatuses)->count();
        $directCount  = DirectPurchaseOrder::whereIn('status', $pendingStatuses)->count();

        return view('receptions.overview', compact('regularCount', 'directCount'));
    }

    /**
     * DataTable server-side: OC estándar pendientes de recepción (todos los puntos de entrega).
     */
    public function datatableRegularPending(Request $request)
    {
        if (! $request->ajax()) {
            abort(403);
        }

        $pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];

        $query = PurchaseOrder::with(['supplier', 'receivingLocation', 'creator'])
            ->whereIn('status', $pendingStatuses)
            ->select('purchase_orders.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('folio', fn($po) =>
                '<span class="fw-bold text-dark">' . $po->folio . '</span>'
            )
            ->addColumn('proveedor', fn($po) =>
                $po->supplier->company_name ?? '—'
            )
            ->addColumn('punto_entrega', function ($po) {
                if (! $po->receivingLocation) {
                    return '<span class="text-danger small">Sin locación</span>';
                }
                return '<span class="badge bg-soft-info text-info me-1">'
                    . $po->receivingLocation->code
                    . '</span>'
                    . e($po->receivingLocation->name);
            })
            ->addColumn('estado', fn($po) =>
                '<span class="badge bg-' . $po->getStatusBadgeClass() . '">'
                . $po->getStatusLabel() . '</span>'
            )
            ->addColumn('emision', fn($po) =>
                $po->issued_at
                    ? '<span class="text-muted small">' . $po->issued_at->format('d/m/Y') . '</span>'
                    : '<span class="text-muted small">—</span>'
            )
            ->addColumn('dias_transcurridos', fn($po) =>
                $this->receptionService->getElapsedDaysBadge($po)
            )
            ->addColumn('actions', function ($po) {
                $showUrl    = route('purchase-orders.show', $po->id);
                $receiveUrl = route('receptions.create', $po->id);

                return '<a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="' . $receiveUrl . '" class="btn btn-sm btn-outline-success ms-1" title="Registrar Recepción">
                            <i class="ti ti-package-import"></i>
                        </a>';
            })
            ->rawColumns(['folio', 'punto_entrega', 'estado', 'emision', 'dias_transcurridos', 'actions'])
            ->make(true);
    }

    /**
     * DataTable server-side: OCD pendientes de recepción (todos los puntos de entrega).
     */
    public function datatableDirectPending(Request $request)
    {
        if (! $request->ajax()) {
            abort(403);
        }

        $pendingStatuses = ['ISSUED', 'PARTIALLY_RECEIVED'];

        $query = DirectPurchaseOrder::with(['supplier', 'receivingLocation', 'creator'])
            ->whereIn('status', $pendingStatuses)
            ->select('odc_direct_purchase_orders.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('folio', fn($ocd) =>
                '<span class="fw-bold text-dark">' . ($ocd->folio ?? 'DRAFT') . '</span>'
            )
            ->addColumn('proveedor', fn($ocd) =>
                $ocd->supplier->company_name ?? '—'
            )
            ->addColumn('punto_entrega', function ($ocd) {
                if (! $ocd->receivingLocation) {
                    return '<span class="text-danger small">Sin locación</span>';
                }
                return '<span class="badge bg-soft-info text-info me-1">'
                    . $ocd->receivingLocation->code
                    . '</span>'
                    . e($ocd->receivingLocation->name);
            })
            ->addColumn('solicitante', fn($ocd) =>
                '<span class="badge bg-soft-secondary text-secondary">'
                . e($ocd->creator->name)
                . '</span>'
            )
            ->addColumn('estado', fn($ocd) =>
                '<span class="badge bg-' . $ocd->getStatusBadgeClass() . '">'
                . $ocd->getStatusLabel() . '</span>'
            )
            ->addColumn('emision', fn($ocd) =>
                $ocd->issued_at
                    ? '<span class="text-muted small">' . $ocd->issued_at->format('d/m/Y') . '</span>'
                    : '<span class="text-muted small">—</span>'
            )
            ->addColumn('dias_transcurridos', fn($ocd) =>
                $this->receptionService->getElapsedDaysBadge($ocd)
            )
            ->addColumn('actions', function ($ocd) {
                $showUrl    = route('direct-purchase-orders.show', $ocd->id);
                $receiveUrl = route('receptions.create-direct', $ocd->id);

                return '<a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="' . $receiveUrl . '" class="btn btn-sm btn-outline-success ms-1" title="Registrar Recepción">
                            <i class="ti ti-package-import"></i>
                        </a>';
            })
            ->rawColumns(['folio', 'punto_entrega', 'solicitante', 'estado', 'emision', 'dias_transcurridos', 'actions'])
            ->make(true);
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

        $repseWarning      = $this->receptionService->validateRepseIfService($purchaseOrder);
        $receivingLocations = ReceivingLocation::active()->orderBy('name')->get();

        return view('receptions.create', [
            'order'              => $purchaseOrder,
            'orderType'          => 'purchase_order',
            'storeRoute'         => route('receptions.store', $purchaseOrder),
            'repseWarning'       => $repseWarning,
            'receivingLocations' => $receivingLocations,
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
            'receiving_location_id'          => 'required|integer|exists:receiving_locations,id',
            'delivery_reference'             => 'nullable|string|max:100',
            'notes'                          => 'nullable|string|max:1000',
            'received_at'                    => 'required|date',
            'remission_file'                 => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'items'                          => 'required|array|min:1',
            'items.*.receivable_item_id'     => 'required|integer|exists:purchase_order_items,id',
            'items.*.quantity_received'      => 'required|numeric|min:0',
            'items.*.quantity_rejected'      => 'nullable|numeric|min:0',
            'items.*.rejection_reason'       => 'nullable|string|max:255',
        ]);

        $remissionPath = $request->file('remission_file')->store('remisiones', 'public');
        $validated['remission_path'] = $remissionPath;

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
            Storage::disk('public')->delete($remissionPath);

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

        $repseWarning      = $this->receptionService->validateRepseIfService($directPurchaseOrder);
        $receivingLocations = ReceivingLocation::active()->orderBy('name')->get();

        return view('receptions.create', [
            'order'              => $directPurchaseOrder,
            'orderType'          => 'direct_purchase_order',
            'storeRoute'         => route('receptions.store-direct', $directPurchaseOrder),
            'repseWarning'       => $repseWarning,
            'receivingLocations' => $receivingLocations,
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
            'receiving_location_id'          => 'required|integer|exists:receiving_locations,id',
            'delivery_reference'             => 'nullable|string|max:100',
            'notes'                          => 'nullable|string|max:1000',
            'received_at'                    => 'required|date',
            'remission_file'                 => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'items'                          => 'required|array|min:1',
            'items.*.receivable_item_id'     => 'required|integer|exists:odc_direct_purchase_order_items,id',
            'items.*.quantity_received'      => 'required|numeric|min:0',
            'items.*.quantity_rejected'      => 'nullable|numeric|min:0',
            'items.*.rejection_reason'       => 'nullable|string|max:255',
        ]);

        $remissionPath = $request->file('remission_file')->store('remisiones', 'public');
        $validated['remission_path'] = $remissionPath;

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
            Storage::disk('public')->delete($remissionPath);

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
