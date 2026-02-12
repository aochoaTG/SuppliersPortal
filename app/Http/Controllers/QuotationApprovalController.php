<?php

namespace App\Http\Controllers;

use App\Models\QuotationSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RfqResponse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class QuotationApprovalController extends Controller
{
    public function index()
    {
        $pendingApprovals = QuotationSummary::with([
            'requisition.rfqs',
            'approvalLevel',
            'selectedSupplier'
        ])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quotations.index', compact('pendingApprovals'));
    }

    public function handle(Request $request, QuotationSummary $summary)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'required_if:status,rejected|nullable|string|min:10'
        ]);

        try {
            DB::transaction(function () use ($request, $summary) {
                $rfq = $summary->requisition->rfqs()->first();
                if ($request->status === 'approved') {
                    // A. Actualizar el sumario
                    $summary->update([
                        'approval_status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);

                    // B. Sellar estados comerciales
                    // Marcamos al ganador como APPROVED (según tu migración)
                    $rfq->rfqResponses()->where('supplier_id', $summary->selected_supplier_id)
                        ->update(['status' => 'SELECTED']);

                    // Perdedores
                    $rfq->rfqResponses()->where('supplier_id', '!=', $summary->selected_supplier_id)
                        ->update(['status' => 'REJECTED']);

                    $rfq->update(['status' => 'COMPLETED']);

                    // 🚀 3. GENERAR LA ORDEN DE COMPRA (OC)
                    $this->generatePurchaseOrder($summary, $rfq);
                } else {
                    // --- FLUJO DE RETORNO (RECHAZO) ---

                    // A. Registrar el rechazo con su motivo
                    $summary->update([
                        'approval_status' => 'rejected',
                        'rejected_by' => Auth::id(),
                        'rejected_at' => now(),
                        'rejection_reason' => $request->reason
                    ]);

                    // B. Liberar la RFQ para el comprador
                    // La regresamos a 'RECEIVED' para que vuelva a aparecer en su panel de evaluación
                    $rfq->update(['status' => 'RECEIVED']);

                    // Reseteamos los estados de las respuestas para que el comprador pueda re-evaluar
                    $rfq->rfqResponses()->update(['status' => 'SUBMITTED']);

                    // C. La Requisición vuelve a estar en fase de evaluación
                    $summary->requisition->update(['status' => 'EVALUATING']);
                }
            });

            $msg = $request->status === 'approved'
                ? '✅ Adjudicación autorizada y Orden de Compra generada.'
                : '❌ Adjudicación rechazada. Se ha notificado al comprador para su re-evaluación.';

            return redirect()->route('approvals.quotations.index')->with('status', $msg);
        } catch (\Exception $e) {
            Log::error("Error en flujo de aprobación: " . $e->getMessage());
            return back()->with('error', 'Falla en la operación: ' . $e->getMessage());
        }
    }

    private function generatePurchaseOrder(QuotationSummary $summary, $rfq)
    {
        $winningResponses = RfqResponse::with('requisitionItem')
            ->where('rfq_id', $rfq->id)
            ->where('supplier_id', $summary->selected_supplier_id)
            ->get();

        // 1. Crear Cabecera
        $po = PurchaseOrder::create([
            'folio'                => 'OC-' . now()->format('Y') . '-' . str_pad($summary->id, 5, '0', STR_PAD_LEFT),
            'requisition_id'       => $summary->requisition_id,
            'supplier_id'          => $summary->selected_supplier_id,
            'quotation_summary_id' => $summary->id,
            'subtotal'             => $summary->subtotal,
            'iva_amount'           => $summary->iva_amount,
            'total'                => $summary->total,
            'payment_terms'        => $winningResponses->first()->payment_terms ?? 'Crédito',
            'estimated_delivery_days' => $winningResponses->max('delivery_days') ?? 0,
            'status'               => 'OPEN',
            'created_by'           => Auth::id(),
        ]);

        // 2. Crear Partidas
        foreach ($winningResponses as $resp) {
            PurchaseOrderItem::create([
                'purchase_order_id'   => $po->id,
                'requisition_item_id' => $resp->requisition_item_id,
                'description'         => $resp->requisitionItem->description ?? 'Sin descripción',
                'quantity'            => $resp->quantity,
                'unit_price'          => $resp->unit_price,
                'subtotal'            => $resp->subtotal,
                'iva_amount'          => $resp->iva_amount,
                'total'               => $resp->total,
            ]);
        }

        // 3. Finalizar la Requisición
        $summary->requisition->update(['status' => 'COMPLETED']);
    }
}
