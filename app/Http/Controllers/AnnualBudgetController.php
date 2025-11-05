<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnualBudgetRequest;
use App\Http\Requests\UpdateAnnualBudgetRequest;
use App\Models\AnnualBudget;
use App\Models\Company;
use App\Models\CostCenter;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AnnualBudgetController extends Controller
{
    public function __construct(private BudgetService $budgetService)
    {
    }

    /**
     * Vista principal con DataTable.
     */
    public function index(): View
    {
        return view('annual_budgets.index');
    }

    /**
     * Endpoint DataTable: lista budgets con relaciones reales (compañía/centro).
     */
    public function datatable(Request $request)
    {
        $query = AnnualBudget::query()->with(['company:id,name', 'costCenter:id,code,name']);


        // Filtros opcionales (por si tienes selects en la vista)
        if ($request->filled('fiscal_year')) {
            $query->where('fiscal_year', (int) $request->input('fiscal_year'));
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', (int) $request->input('company_id'));
        }
        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', (int) $request->input('cost_center_id'));
        }

        return DataTables::of($query)
            ->addColumn('company_name', fn(AnnualBudget $row) => e($row->company?->name ?? '—'))
            ->addColumn('cost_center_label', function (AnnualBudget $row) {
                $code = $row->costCenter?->code ? '[' . $row->costCenter->code . '] ' : '';
                $name = $row->costCenter?->name ?? '—';
                return e($code . $name);
            })
            ->editColumn('fiscal_year', fn(AnnualBudget $row) => (int) $row->fiscal_year)
            ->editColumn('amount_assigned', fn(AnnualBudget $row) => number_format((float) $row->amount_assigned, 2))
            ->addColumn('amount_available', function (AnnualBudget $row) {
                // Si traes cache en la tabla, úsalo:
                $available = $row->amount_available ?? null;

                // Como respaldo, calcula con el servicio si está nulo:
                if ($available === null) {
                    $t = app(BudgetService::class)->totals($row->id);
                    $available = $t['available'] ?? 0.0;
                }

                // Semáforo simple (en HTML) para el DataTable
                $val = number_format((float) $available, 2);
                $class = 'badge bg-secondary';
                if ($available > 0)
                    $class = 'badge bg-success';
                if ($available == 0.0)
                    $class = 'badge bg-warning text-dark';
                if ($available < 0)
                    $class = 'badge bg-danger';

                return '<span class="' . $class . '">$' . $val . '</span>';
            })
            ->addColumn('actions', function (AnnualBudget $row) {
                $editUrl = route('annual-budgets.edit', $row->id);
                $deleteUrl = route('annual-budgets.destroy', $row->id);
                $logUrl = route('budget-movements.index', ['annual_budget_id' => $row->id]);

                return '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                        <i class="ti ti-edit"></i>
                    </a>

                    <a href="' . $logUrl . '" class="btn btn-sm btn-outline-secondary me-1" title="Bitácora">
                        <i class="ti ti-activity"></i>
                    </a>

                    <form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                                title="Eliminar" data-entity="Presupuesto ' . $row->fiscal_year . '">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['amount_available', 'actions'])
            ->make(true);
    }

    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        $annualBudget = new AnnualBudget([
            'fiscal_year' => (int) date('Y'),
            'amount_assigned' => 0,
            'amount_committed' => 0,
            'amount_consumed' => 0,
            'amount_released' => 0,
            'amount_adjusted' => 0,
            'amount_available' => 0,
            'is_closed' => false,
        ]);

        $companies = Company::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('name')->get(['id', 'name', 'company_id', 'code']);

        return view('annual_budgets.create', compact('annualBudget', 'companies', 'costCenters'));
    }

    /**
     * Guardar nuevo presupuesto.
     */
    public function store(StoreAnnualBudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Si no viene amount_available, lo dejamos igual a amount_assigned
        if (!array_key_exists('amount_available', $data)) {
            $data['amount_available'] = (float) ($data['amount_assigned'] ?? 0);
        }

        $ab = AnnualBudget::create($data);

        return redirect()
            ->route('annual-budgets.index')
            ->with('success', 'Presupuesto anual creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(AnnualBudget $annual_budget): View
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('name')->get(['id', 'name', 'company_id', 'code']);

        $totals = $this->budgetService->totals($annual_budget->id);

        return view('annual_budgets.edit', [
            'annualBudget' => $annual_budget,
            'companies' => $companies,
            'costCenters' => $costCenters,
            'totals' => $totals,
        ]);
    }

    /**
     * Actualizar presupuesto.
     * - Ajuste automático (UP/DOWN) si cambia el amount_assigned.
     * - Bloquea recortes que dejen disponible negativo.
     */
    public function update(UpdateAnnualBudgetRequest $request, AnnualBudget $annual_budget): RedirectResponse
    {
        $oldAmount = (float) $annual_budget->amount_assigned;

        $annual_budget->update($request->validated());

        $newAmount = (float) $annual_budget->amount_assigned;
        $delta = $newAmount - $oldAmount;

        if (abs($delta) > 0.00001) {
            $totalsBefore = $this->budgetService->totals($annual_budget->id);

            if ($delta < 0) {
                // recorte
                $availableBefore = (float) $totalsBefore['available'];
                $recorte = abs($delta);

                if ($recorte > $availableBefore) {
                    // Revertimos y avisamos
                    $annual_budget->update(['amount_assigned' => $oldAmount]);

                    return redirect()
                        ->back()
                        ->with('danger', 'No se puede reducir el presupuesto por encima del disponible actual. Recorte solicitado: $'
                            . number_format($recorte, 2) . ' > Disponible: $' . number_format($availableBefore, 2));
                }
            }

            $direction = $delta > 0 ? 'UP' : 'DOWN';
            $note = sprintf(
                'Ajuste automático por edición (antes: %s, nuevo: %s)',
                number_format($oldAmount, 2),
                number_format($newAmount, 2)
            );

            $this->budgetService->adjust($annual_budget->id, abs($delta), $direction, $note);
        }

        // Si llevas cache en la tabla, puedes recalcular amount_available aquí
        // en base a BudgetService->totals() (opcional).
        // $t = $this->budgetService->totals($annual_budget->id);
        // $annual_budget->update(['amount_available' => $t['available']]);

        return redirect()
            ->route('annual-budgets.index')
            ->with('success', 'Presupuesto anual actualizado correctamente.');
    }

    /**
     * Eliminar presupuesto.
     * Previene borrado si existen movimientos ligados.
     */
    public function destroy(AnnualBudget $annual_budget): RedirectResponse
    {
        // Seguridad: si hay movimientos, no permitir borrar para evitar inconsistencia
        if ($annual_budget->movements()->exists()) {
            return redirect()
                ->route('annual-budgets.index')
                ->with('warning', 'No se puede eliminar: el presupuesto tiene movimientos registrados.');
        }

        $annual_budget->delete();

        return redirect()
            ->route('annual-budgets.index')
            ->with('success', 'Presupuesto anual eliminado correctamente.');
    }
}
