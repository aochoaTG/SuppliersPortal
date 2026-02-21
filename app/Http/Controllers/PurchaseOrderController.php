<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\DirectPurchaseOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    /**
     * Vista principal con tabs para OC Regulares y OCD
     */
    public function index()
    {
        // Obtenemos contadores para mostrar en los tabs
        $regularCount = PurchaseOrder::count();
        $directCount = DirectPurchaseOrder::count();

        return view('purchase-orders.index', compact('regularCount', 'directCount'));
    }

    /**
     * DataTable para Órdenes de Compra REGULARES (Requisición → Cotizaciones → OC)
     */
    public function datatableRegular(Request $request)
    {
        if ($request->ajax()) {
            $purchaseOrders = PurchaseOrder::with(['supplier', 'requisition', 'creator'])
                ->select('purchase_orders.*');

            return DataTables::of($purchaseOrders)
                ->addIndexColumn()
                ->addColumn('folio', function ($po) {
                    return '<span class="fw-bold text-dark">' . $po->folio . '</span>';
                })
                ->addColumn('fecha_emision', function ($po) {
                    return $po->created_at->format('d/m/Y H:i');
                })
                ->addColumn('proveedor', function ($po) {
                    return $po->supplier->company_name ?? 'N/A';
                })
                ->addColumn('requisicion', function ($po) {
                    return '<span class="badge bg-soft-secondary text-secondary">' .
                        ($po->requisition->folio ?? 'N/A') .
                        '</span>';
                })
                ->addColumn('total', function ($po) {
                    return '<span class="fw-bold text-primary">$' . number_format($po->total, 2) . '</span>';
                })
                ->addColumn('status', function ($po) {
                    $statusColors = [
                        'OPEN' => 'info',
                        'RECEIVED' => 'success',
                        'CANCELLED' => 'danger',
                        'PAID' => 'dark'
                    ];
                    $color = $statusColors[$po->status] ?? 'secondary';
                    $statusLabels = [
                        'OPEN' => 'Abierta',
                        'RECEIVED' => 'Recibida',
                        'CANCELLED' => 'Cancelada',
                        'PAID' => 'Pagada'
                    ];
                    $label = $statusLabels[$po->status] ?? $po->status;

                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('actions', function ($po) {
                    $showUrl = route('purchase-orders.show', $po->id);

                    return '
                        <a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                            <i class="ti ti-eye"></i>
                        </a>
                    ';
                })
                ->rawColumns(['folio', 'requisicion', 'total', 'status', 'actions'])
                ->make(true);
        }
    }

    /**
     * DataTable para Órdenes de Compra DIRECTAS (sin proceso de cotización)
     */
    public function datatableDirect(Request $request)
    {
        if ($request->ajax()) {
            $directOrders = DirectPurchaseOrder::with(['supplier', 'creator', 'costCenter'])
                ->select('odc_direct_purchase_orders.*');

            return DataTables::of($directOrders)
                ->addIndexColumn()
                ->addColumn('folio', function ($ocd) {
                    return '<span class="fw-bold text-dark">' . ($ocd->folio ?? 'DRAFT') . '</span>';
                })
                ->addColumn('fecha_solicitud', function ($ocd) {
                    return $ocd->created_at->format('d/m/Y H:i');
                })
                ->addColumn('proveedor', function ($ocd) {
                    return $ocd->supplier->company_name ?? 'N/A';
                })
                ->addColumn('solicitante', function ($ocd) {
                    return '<span class="badge bg-soft-info text-info">' .
                        $ocd->creator->name .
                        '</span>';
                })
                ->addColumn('centro_costo', function ($ocd) {
                    return $ocd->costCenter->name ?? 'N/A';
                })
                ->addColumn('total', function ($ocd) {
                    return '<span class="fw-bold text-primary">$' . number_format($ocd->total, 2) . '</span>';
                })
                ->addColumn('status', function ($ocd) {
                    $statusColors = [
                        'DRAFT' => 'secondary',
                        'PENDING_APPROVAL' => 'warning',
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        'RETURNED' => 'info',
                        'ISSUED' => 'primary',
                        'RECEIVED' => 'success',
                        'CANCELLED' => 'dark'
                    ];
                    $color = $statusColors[$ocd->status] ?? 'secondary';

                    $statusLabels = [
                        'DRAFT' => 'Borrador',
                        'PENDING_APPROVAL' => 'Pendiente Aprobación',
                        'APPROVED' => 'Aprobada',
                        'REJECTED' => 'Rechazada',
                        'RETURNED' => 'Devuelta',
                        'ISSUED' => 'Emitida',
                        'RECEIVED' => 'Recibida',
                        'CANCELLED' => 'Cancelada'
                    ];
                    $label = $statusLabels[$ocd->status] ?? $ocd->status;

                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('actions', function ($ocd) {
                    $showUrl = route('direct-purchase-orders.show', $ocd->id);

                    $buttons = '
                        <a href="' . $showUrl . '" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                            <i class="ti ti-eye"></i>
                        </a>
                    ';

                    $canEdit = $ocd->status === 'RETURNED' && (int) $ocd->created_by === (int) Auth::id();

                    if ($canEdit) {
                        $editUrl = route('direct-purchase-orders.edit', $ocd->id);
                        $buttons .= '
                            <a href="' . $editUrl . '" class="btn btn-sm btn-outline-warning ms-1" title="Editar">
                                <i class="ti ti-edit"></i>
                            </a>
                        ';
                    } else {
                        $buttons .= '
                            <a class="btn btn-sm btn-outline-warning ms-1 disabled" aria-disabled="true" title="Editar (solo disponible cuando la OCD está Devuelta)">
                                <i class="ti ti-edit"></i>
                            </a>
                        ';
                    }

                    return $buttons;
                })
                ->rawColumns(['folio', 'solicitante', 'total', 'status', 'actions'])
                ->make(true);
        }
    }

    /**
     * Ver detalle de OC Regular
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'supplier', 'requisition.department']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Ver detalle de OCD
     */
    public function showDirect(DirectPurchaseOrder $directPurchaseOrder)
    {
        $directPurchaseOrder->load([
            'items',
            'supplier',
            'creator',
            'costCenter',
            'approvals.approver',
            'documents'
        ]);

        return view('purchase-orders.show-direct', compact('directPurchaseOrder'));
    }
}
