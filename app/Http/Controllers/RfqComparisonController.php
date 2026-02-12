<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ApprovalLevel;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\Log;


class RfqComparisonController extends Controller
{
    protected $approvalService;

    /**
     * Constructor
     */
    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Muestra la matriz visual del cuadro comparativo (Punto 3.3.5)
     */
    public function index(Rfq $rfq)
    {
        // 🎯 CARGA COMPLETA: Quitamos el filtro 'whereNotNull' de suppliers
        $rfq->load([
            'requisition.costCenter',
            'suppliers', // 👈 Traemos a todos los invitados sin excepción
            'rfqResponses' => function ($q) {
                // Pero aquí solo traemos las respuestas que ya fueron enviadas
                $q->whereIn('status', ['SUBMITTED', 'SELECTED']);
            },
            'quotationSummary.rejectedBy', // 👈 Agrega esto
            'activities' // Para ver quién ha estado moviendo qué
        ]);

        // Usamos el método blindado que ya tenemos en el modelo
        $items = $rfq->getItemsToQuote();

        // Obtenemos el disponible real del presupuesto (Simulado por ahora)
        // En una fase posterior, esto vendrá de tu BudgetService
        $presupuestoDisponible = 200000;

        // Cargamos todos los niveles de aprobación para la matriz
        $approvalLevels = $this->approvalService->getAllLevels();
        return view('rfq.comparison.index', compact('rfq', 'items', 'presupuestoDisponible', 'approvalLevels'));
    }

    /**
     * Procesa la selección del proveedor ganador y sella el nivel de aprobación.
     */
    public function select(Request $request, Rfq $rfq)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'justification' => 'required|string|min:15',
        ]);

        // 1. INTELIGENCIA: Sumamos los valores reales de las partidas del ganador
        // Sumamos subtotal, iva y total por separado para mayor precisión fiscal
        $totals = $rfq->rfqResponses()
            ->where('supplier_id', $request->supplier_id)
            ->selectRaw('SUM(subtotal) as subtotal, SUM(iva_amount) as iva, SUM(total) as total')
            ->first();

        $totalGanador = $totals->total ?? 0;

        // 2. DETERMINACIÓN DE MANDO: El servicio busca el rango en SQL Server
        $nivelConfig = $this->approvalService->getLevelForAmount($totalGanador);

        if (!$nivelConfig) {
            return back()->with('error', '⚠️ Falla de seguridad: El monto ($' . number_format($totalGanador, 2) . ') no entra en ningún rango de aprobación configurado.');
        }

        try {
            // INICIAMOS TRANSACCIÓN: O se hace todo o nada.
            DB::transaction(function () use ($request, $rfq, $totals, $nivelConfig) {

                // 3. SELLO DE SUMARIO: Registramos la adjudicación
                $rfq->quotationSummary()->updateOrCreate(
                    ['requisition_id' => $rfq->requisition_id],
                    [
                        'subtotal'             => $totals->subtotal,
                        'iva_amount'           => $totals->iva,
                        'total'                => $totals->total,
                        'approval_level_id'    => $nivelConfig->id, // 👈 DEBE SER ESTE NOMBRE
                        'selected_supplier_id' => $request->supplier_id, // 👈 PARA QUE SALGA EL NOMBRE
                        'approval_status'      => 'pending',
                        'justification'        => $request->justification,
                        'notes'                => $request->notes,
                    ]
                );

                // 4. CIERRE DE PERÍMETRO: Bloqueamos la RFQ
                $rfq->update(['status' => 'EVALUATED']);

                // Opcional: Podrías marcar también la requisición como 'EN_APROBACION'
                // $rfq->requisition->update(['status' => 'PENDING_APPROVAL']);
            });

            return redirect()->route('rfq.index')
                ->with('status', "✅ Adjudicación registrada. Requiere firma de: {$nivelConfig->label}");
        } catch (\Exception $e) {
            Log::error("Error en Adjudicación RFQ {$rfq->id}: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error táctico al procesar la adjudicación: ' . $e->getMessage());
        }
    }

    /**
     * Procesa la selección del proveedor ganador (Punto 3.3.6)
     */
    public function selectWinner(Request $request, Rfq $rfq)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'justification' => 'required|string|min:10',
        ]);

        $supplierId = $validated['supplier_id'];

        // 1. CÁLCULO DE MONTOS (Punto 3.3.7)
        $responses = $rfq->rfqResponses()->where('supplier_id', $supplierId)->get();

        $subtotal = $responses->sum('subtotal');
        $iva = $responses->sum('iva_amount');
        $total = $subtotal + $iva;

        // 2. DETERMINACIÓN DE NIVEL DE APROBACIÓN (Punto 3.4.1)
        $approvalLevel = $this->determineApprovalLevel($total);

        // 3. VALIDACIÓN PRESUPUESTAL (Punto 3.4.4) - Operación Crítica
        $budgetStatus = $this->checkBudgetAvailability($rfq, $total);

        if (!$budgetStatus['available']) {
            return back()->with('error_budget', $budgetStatus['message']);
        }

        DB::beginTransaction();
        try {
            // Adjudicamos la RFQ
            $rfq->update([
                'status' => 'EVALUATED',
                'selected_supplier_id' => $supplierId,
                'total_amount' => $total,
                'approval_level_id' => $approvalLevel,
                'comparison_justification' => $validated['justification'],
                'evaluated_at' => now(),
                'evaluated_by' => Auth::id(),
            ]);

            // Marcamos respuestas: Ganador vs Perdedores
            $rfq->rfqResponses()->where('supplier_id', $supplierId)->update(['status' => 'SELECTED']);
            $rfq->rfqResponses()->where('supplier_id', '!=', $supplierId)->update(['status' => 'REJECTED']);

            DB::commit();
            return redirect()->route('rfq.index')->with('success', 'Proveedor seleccionado y enviado a aprobación Nivel ' . $approvalLevel);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en la adjudicación: ' . $e->getMessage());
        }
    }

    /**
     * Lógica de Rangos de Aprobación (Punto 3.4.1)
     */
    private function determineApprovalLevel($total)
    {
        if ($total <= 50000) return 1; // Jefe de Área
        if ($total <= 150000) return 2; // Gerente de Depto
        if ($total <= 500000) return 3; // Director de Área
        return 4; // Director General
    }

    /**
     * Verificación de Presupuesto Mensual (Punto 3.4.4)
     */
    private function checkBudgetAvailability($rfq, $total)
    {
        $costCenter = $rfq->requisition->costCenter;
        $mesActual = now()->month;
        $anioActual = now()->year;

        // Aquí buscarías en tu tabla de presupuestos
        // $budget = Budget::where('cost_center_id', $costCenter->id)...

        // Simulación de lógica:
        $disponible = 200000; // Esto vendría de la BD: Asignado - (Comprometido + Ejercido)

        $saldoFinal = $disponible - $total;

        return [
            'available' => $saldoFinal >= 0,
            'message' => "Presupuesto insuficiente en el Centro de Costos {$costCenter->name}. Saldo restante: " . number_format($saldoFinal, 2)
        ];
    }
}
