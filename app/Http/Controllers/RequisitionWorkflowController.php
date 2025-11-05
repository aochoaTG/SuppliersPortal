<?php

namespace App\Http\Controllers;

use App\Enum\RequisitionStatus;
use App\Models\AnnualBudget;
use App\Models\BudgetMovement;
use App\Models\Requisition;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RequisitionWorkflowController extends Controller
{
    public function __construct(private BudgetService $budgetService)
    {
    }

    /**
     * Bandeja: pendientes de revisión (incluye on_hold).
     */
    public function reviewInbox(Request $request)
    {
        $rows = Requisition::with([
            'company:id,name',
            'costCenter:id,code,name',
            'department:id,name',
            'requester:id,name',
            'onHoldUser:id,name',
        ])
            ->whereNull('reviewed_by')
            ->whereIn('status', ['in_review', 'on_hold'])
            ->orderByRaw("CASE WHEN status = 'on_hold' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate(20);



        return view('requisitions.inbox.review', compact('rows'));
    }

    /**
     * Bandeja: pendientes de aprobación.
     */
    public function approvalInbox(Request $request)
    {
        $rows = Requisition::with(['company:id,name', 'costCenter:id,code,name', 'department:id,name', 'reviewer:id,name'])
            ->where('status', 'in_review')
            ->whereNotNull('reviewed_by')
            ->whereNull('approved_by')
            ->orderByDesc('id')
            ->paginate(20);

        return view('requisitions.inbox.approval', compact('rows'));
    }

    /**
     * Bandeja: rechazadas.
     */
    public function rejectedInbox(Request $request)
    {
        $rows = Requisition::with(['company:id,name', 'costCenter:id,code,name', 'department:id,name', 'rejecter:id,name'])
            ->where('status', 'rejected')
            ->orderByDesc('id')
            ->paginate(20);

        return view('requisitions.inbox.rejected', compact('rows'));
    }

    /**
     * Enviar a revisión (desde draft/on_hold).
     */
    public function submit(Request $request, Requisition $requisition)
    {
        if (!in_array($requisition->status, ['draft', 'on_hold'], true)) {
            return back()->with('warning', 'Esta requisición no puede enviarse a revisión desde su estado actual.');
        }

        $requisition->update([
            'status' => RequisitionStatus::IN_REVIEW->value,
            // limpiar marcas on_hold si venía de pausa
            'on_hold_reason' => null,
            'on_hold_by' => null,
            'on_hold_at' => null,
            // limpieza de revisión/aprobación (por seguridad)
            'reviewed_by' => null,
            'reviewed_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            // limpiar rechazos/cancelaciones si es reactivación
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'cancellation_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
        ]);

        return redirect()->route('requisitions.inbox.review')
            ->with('success', '📤 Enviada a revisión.');
    }

    /**
     * Marcar como revisada (acepta in_review u on_hold).
     */
    public function markReviewed(Request $request, Requisition $requisition)
    {
        if (!in_array($requisition->status, ['in_review', 'on_hold'], true)) {
            return back()->with('warning', 'La requisición no está en revisión ni en espera.');
        }

        $requisition->update([
            'status' => RequisitionStatus::IN_REVIEW->value,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            // limpia on_hold previos
            'on_hold_reason' => null,
            'on_hold_by' => null,
            'on_hold_at' => null,
        ]);

        return back()->with('success', '✅ Revisión completada. Enviada a bandeja de aprobación.');
    }

    /**
     * Poner en espera con motivo.
     */
    public function hold(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (!in_array($requisition->status, ['in_review', 'on_hold'], true)) {
            return $this->respond($request, false, 'La requisición no está en revisión.');
        }

        $requisition->update([
            'status' => RequisitionStatus::ON_HOLD->value,
            'on_hold_reason' => $data['reason'],
            'on_hold_by' => Auth::id(),
            'on_hold_at' => now(),
            // mantener reviewed_by NULL para que siga en bandeja de revisión
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        return $this->respond($request, true, '⏸️ Requisición puesta en espera.');
    }

    /**
     * Aprobar (COMMIT si aplica).
     */
    public function approve(Request $request, Requisition $requisition)
    {
        // Idempotencia: si ya está aprobada, no dupliques COMMIT
        if ($requisition->status === RequisitionStatus::APPROVED->value) {
            return back()->with('info', 'Esta requisición ya está aprobada.');
        }

        // Solo permitir aprobar desde in_review (ya revisada)
        if ($requisition->status !== RequisitionStatus::IN_REVIEW->value || !$requisition->reviewed_by) {
            return back()->with('warning', 'Primero debe ser revisada antes de aprobar.');
        }

        $cc = (int) $requisition->cost_center_id;
        $fy = (int) $requisition->fiscal_year;
        $amt = (float) $requisition->amount_requested;

        // Validar disponibilidad (salvo si viene de budget_exception, ajusta si manejas roles-excepción)
        if ($requisition->status !== 'budget_exception') {
            $can = method_exists($this->budgetService, 'canCommit')
                ? $this->budgetService->canCommit($cc, $fy, $amt)
                : true; // si no tienes canCommit, asume true
            if (!$can) {
                return back()->with('danger', 'Disponible insuficiente. Solicita excepción de presupuesto.');
            }
        }

        // Resolver presupuesto aplicable
        $budget = $this->budgetService->resolveApplicableBudget($cc, $fy);
        if (!$budget instanceof AnnualBudget) {
            return back()->with('danger', 'No existe un presupuesto anual aplicable para este centro/año.');
        }

        // Idempotencia: ¿ya hay COMPROMISO para esta requisición?
        $alreadyCommitted = BudgetMovement::where('annual_budget_id', $budget->id)
            ->where('requisition_id', $requisition->id)
            ->where('movement_type', 'COMPROMISO')
            ->exists();

        if (!$alreadyCommitted) {
            // Crear COMMIT
            $this->budgetService->commit($budget->id, $amt, $requisition->id, 'Aprobación de requisición');
        }

        // Marcar aprobada
        $requisition->update([
            'status' => RequisitionStatus::APPROVED->value,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            // limpiar on_hold si existía
            'on_hold_reason' => null,
            'on_hold_by' => null,
            'on_hold_at' => null,
        ]);

        return back()->with('success', '🟢 Requisición aprobada. Compromiso registrado.');
    }

    /**
     * Rechazar (RELEASE remanente si estaba comprometida).
     */
    public function reject(Request $request, Requisition $requisition)
    {
        // 1️⃣ Validar motivo de rechazo
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // 2️⃣ Verificar que la requisición sea rechazada en un estado válido
        if (!in_array($requisition->status, ['in_review', 'on_hold', 'approved'], true)) {
            return response()->json([
                'message' => 'Esta requisición no puede ser rechazada en su estado actual.',
            ], 422);
        }

        // 3️⃣ Liberar presupuesto si ya estaba comprometido
        try {
            $this->releaseRemainderIfAny($requisition, 'Rechazo de requisición');
        } catch (\Throwable $e) {
            // Registrar pero no detener el rechazo
            \Log::warning("Error liberando presupuesto de req {$requisition->id}: {$e->getMessage()}");
        }

        // 4️⃣ Actualizar campos de rechazo
        $requisition->update([
            'status' => RequisitionStatus::REJECTED->value,
            'rejection_reason' => $data['reason'],
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
        ]);

        // 5️⃣ Responder con JSON
        return response()->json([
            'message' => 'Requisición rechazada correctamente.',
            'requisition_id' => $requisition->id,
            'status' => $requisition->status,
        ]);
    }

    /**
     * Cancelar (RELEASE remanente si estaba comprometida).
     */
    public function cancel(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->releaseRemainderIfAny($requisition, 'Cancelación de requisición');

        $requisition->update([
            'status' => RequisitionStatus::CANCELLED->value,
            'cancellation_reason' => $data['reason'],
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('warning', '🛑 Requisición cancelada.');
    }

    /**
     * Excepción de presupuesto (flujo alterno).
     */
    public function budgetException(Request $request, Requisition $requisition)
    {
        // Puedes exigir una nota opcional:
        $note = $request->input('note');

        $requisition->update([
            'status' => RequisitionStatus::BUDGET_EXCEPTION->value,
            // podrías reutilizar on_hold_reason para guardar nota de excepción si gustas:
            // 'on_hold_reason' => $note,
        ]);

        return back()->with('warning', '⚠️ Requisición marcada como Excepción de Presupuesto.');
    }

    /**
     * Consumo (desde Compras/AF): reduce disponible definitivo (parcial o total).
     * POST requisitions/{requisition}/consume  amount, source, note?
     */
    public function consume(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'source' => ['required', 'string', 'max:50'], // 'PO' | 'INVOICE' | 'GR'
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $budget = $this->budgetService->resolveApplicableBudget($requisition->cost_center_id, $requisition->fiscal_year);
        if (!$budget instanceof AnnualBudget) {
            return $this->respond($request, false, 'No existe presupuesto aplicable.');
        }

        // Idempotencia “soft”: el consumo suele venir por evento externo con su propio idempotency-key.
        $this->budgetService->consume($budget->id, (float) $data['amount'], $requisition->id, ($data['source'] . ($data['note'] ? " - {$data['note']}" : '')));

        return $this->respond($request, true, 'Consumo registrado.');
    }

    /* =========================================================================================
     * Helpers
     * ========================================================================================= */

    /**
     * Libera el remanente comprometido de la requisición (commit - release - consume).
     * Evita liberar de más; si no hay remanente, no hace nada.
     */
    private function releaseRemainderIfAny(Requisition $requisition, string $reason): void
    {
        $budget = $this->budgetService->resolveApplicableBudget($requisition->cost_center_id, $requisition->fiscal_year);
        if (!$budget instanceof AnnualBudget) {
            return;
        }

        // Totales por requisición
        $byType = BudgetMovement::select('movement_type')
            ->selectRaw('SUM(amount) as total')
            ->where('annual_budget_id', $budget->id)
            ->where('requisition_id', $requisition->id)
            ->groupBy('movement_type')
            ->pluck('total', 'movement_type');

        $sum = fn($t) => (float) ($byType[$t] ?? 0.0);

        $committed = $sum('COMPROMISO');   // compromisos registrados
        $released = $sum('LIBERACION');   // liberaciones previas
        $consumed = $sum('CONSUMO');      // consumos ya realizados (no se “des-consumen”)

        // Remanente comprometido liberable = comprometido - consumido - ya liberado
        $remainder = max(0.0, $committed - $consumed - $released);

        if ($remainder > 0.00001) {
            $this->budgetService->release($budget->id, $remainder, $requisition->id, $reason);
        }
    }

    /**
     * Respuesta unificada para fetch/HTML.
     */
    private function respond(Request $request, bool $ok, string $msg)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $msg], $ok ? 200 : 422);
        }
        $level = $ok ? 'success' : 'danger';
        return back()->with($level, $msg);
    }
}
