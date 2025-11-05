<?php

namespace App\Http\Controllers;

use App\Models\BudgetMovement;
use Illuminate\Http\Request;
use App\Services\BudgetService;


class BudgetMovementController extends Controller
{
    public function __construct(private BudgetService $budget)
    {
    }
    /**
     * Muestra el historial de movimientos (por presupuesto).
     */
    public function index(Request $request)
    {
        $budgetId = $request->query('annual_budget_id');

        $movements = BudgetMovement::with(['annualBudget', 'requisition'])
            ->when($budgetId, fn($q) => $q->where('annual_budget_id', $budgetId))
            ->orderByDesc('moved_at')
            ->paginate(20);

        return view('budget_movements.index', compact('movements', 'budgetId'));
    }

    /**
     * Muestra el detalle de un movimiento individual.
     */
    public function show(BudgetMovement $budgetMovement)
    {
        return view('budget_movements.show', compact('budgetMovement'));
    }


    public function snapshot(Request $request, BudgetService $svc)
    {
        $request->validate([
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        return response()->json(
            $svc->snapshot((int) $request->cost_center_id, (int) $request->fiscal_year)
        );
    }
}
