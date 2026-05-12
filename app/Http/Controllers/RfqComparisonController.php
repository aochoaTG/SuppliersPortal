<?php

namespace App\Http\Controllers;

use App\Enum\RequisitionStatus;
use App\Models\QuotationSummary;
use App\Models\Rfq;
use App\Notifications\QuotationApprovalRequestNotification;
use App\Services\ApprovalService;
use App\Services\AuthorizerResolutionService;
use App\Services\BudgetAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfqComparisonController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService,
        protected BudgetAllocationService $budgetAllocationService,
        protected AuthorizerResolutionService $authorizerResolutionService
    ) {}

    public function index(Rfq $rfq)
    {
        $rfq->load([
            'requisition.costCenter',
            'suppliers',
            'rfqResponses' => fn ($query) => $query->whereIn('status', ['SUBMITTED', 'SELECTED', 'REJECTED']),
            'quotationSummary.rejector',
            'activities',
        ]);

        $items = $rfq->getItemsToQuote();
        $approvalLevels = $this->approvalService->getAllLevels();

        return view('rfq.comparison.index', [
            'rfq' => $rfq,
            'items' => $items,
            'presupuestoDisponible' => null,
            'approvalLevels' => $approvalLevels,
        ]);
    }

    public function select(Request $request, Rfq $rfq)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'justification' => 'required|string|min:15',
        ]);

        $rfq->loadMissing('requisition.requester', 'requisition.costCenter', 'rfqResponses.requisitionItem');

        $totals = $rfq->rfqResponses()
            ->where('supplier_id', $request->integer('supplier_id'))
            ->where('status', 'SUBMITTED')
            ->selectRaw('SUM(subtotal) as subtotal, SUM(iva_amount) as iva, SUM(total) as total')
            ->first();

        if (! $totals || (float) ($totals->total ?? 0) <= 0) {
            return back()->with('error', 'El proveedor seleccionado no tiene cotizaciones enviadas para esta RFQ.');
        }

        try {
            $summary = DB::transaction(function () use ($request, $rfq, $totals) {
                $summary = QuotationSummary::updateOrCreate(
                    ['rfq_id' => $rfq->id],
                    [
                        'requisition_id' => $rfq->requisition_id,
                        'subtotal' => (float) $totals->subtotal,
                        'iva_amount' => (float) $totals->iva,
                        'total' => (float) $totals->total,
                        'selected_supplier_id' => $request->integer('supplier_id'),
                        'requested_by_user_id' => $rfq->requisition->requested_by,
                        'selected_by_user_id' => Auth::id(),
                        'approval_status' => 'pending',
                        'justification' => $request->string('justification')->toString(),
                        'notes' => $request->input('notes'),
                        'approved_by' => null,
                        'approved_at' => null,
                        'rejected_by' => null,
                        'rejected_at' => null,
                        'rejection_reason' => null,
                    ]
                );

                $summary->loadMissing('requester', 'requisition.requester');

                $resolution = $this->authorizerResolutionService->resolveForSummary($summary);

                $summary->update([
                    'current_approver_user_id' => $resolution['approver_user']->id,
                    'authorizer_role_id' => $resolution['authorizer_role']->id,
                    'effective_authorization_limit' => $resolution['effective_limit'],
                    'approval_chain_snapshot' => $resolution['chain'],
                    'resolution_notes' => $resolution['resolution_notes'],
                ]);

                $this->budgetAllocationService->reserveQuotationSummary($summary);

                $rfq->update(['status' => 'EVALUATED']);
                $rfq->requisition->update(['status' => RequisitionStatus::QUOTED->value]);

                return $summary->fresh(['currentApprover', 'selectedSupplier', 'rfq', 'requisition']);
            });

            $escalated = collect($summary->approval_chain_snapshot)->contains(fn ($step) => ($step['status'] ?? null) !== 'eligible');
            $summary->currentApprover?->notify(new QuotationApprovalRequestNotification($summary, $escalated));

            return redirect()
                ->route('rfq.index')
                ->with('status', 'Adjudicación registrada y enviada a aprobación de '.($summary->currentApprover?->name ?? 'aprobador asignado').'.');
        } catch (\Throwable $exception) {
            Log::error("Error en adjudicación RFQ {$rfq->id}: {$exception->getMessage()}");

            return back()->with('error', 'No fue posible registrar la adjudicación: '.$exception->getMessage());
        }
    }
}
