<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveBudgetMonthlyDistributionRequest;
use App\Models\AnnualBudget;
use App\Models\BudgetCedula;
use App\Models\BudgetMonthlyDistribution;
use App\Services\BudgetCategorySummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BudgetMonthlyDistributionController extends Controller
{
    public function __construct(
        private readonly BudgetCategorySummaryService $categorySummaryService
    ) {
    }

    public function index(Request $request): View
    {
        $budgetId = $request->query('annual_budget_id');

        $budget = null;
        if ($budgetId) {
            $budget = AnnualBudget::with(['costCenter.company', 'monthlyDistributions'])
                ->findOrFail($budgetId);
        }

        $budgets = AnnualBudget::withDetails()->get();

        return view('budget_monthly_distributions.index', compact('budget', 'budgets'));
    }

    public function datatable(Request $request)
    {
        $budgetId = $request->query('annual_budget_id');

        if (! $budgetId) {
            return response()->json(['error' => 'Budget ID requerido'], 400);
        }

        $budget = $this->loadBudgetWithDistributions((int) $budgetId);
        $rows = $this->buildMonthlyCategoryRows($budget);

        return DataTables::of($rows)
            ->addColumn('month_label', fn (array $row) => $row['month_label'])
            ->addColumn('category_code', fn (array $row) => e("[{$row['category_code']}] {$row['category_name']}"))
            ->addColumn('assigned_amount', fn (array $row) => '$' . number_format($row['assigned_amount'], 2))
            ->addColumn('consumed_amount', fn (array $row) => '$' . number_format($row['consumed_amount'], 2))
            ->addColumn('committed_amount', fn (array $row) => '$' . number_format($row['committed_amount'], 2))
            ->addColumn('available_amount', function (array $row) {
                $value = number_format($row['available_amount'], 2);
                $class = $row['available_amount'] > 0
                    ? 'badge bg-success'
                    : 'badge bg-warning text-dark';

                return '<span class="' . $class . '">$' . $value . '</span>';
            })
            ->addColumn('usage_percentage', function (array $row) {
                $pct = number_format($row['usage_percentage'], 1);
                $class = match (true) {
                    $row['usage_percentage'] > 90 => 'badge bg-danger',
                    $row['usage_percentage'] > 70 => 'badge bg-warning text-dark',
                    default => 'badge bg-success',
                };

                return '<span class="' . $class . '">' . $pct . '%</span>';
            })
            ->addColumn('status_label', fn (array $row) => $this->statusBadge($row['status']))
            ->rawColumns(['available_amount', 'usage_percentage', 'status_label'])
            ->make(true);
    }

    public function create(AnnualBudget $annualBudget): View|RedirectResponse
    {
        $annualBudget->loadMissing(['costCenter.company']);

        if ($annualBudget->status !== 'PLANIFICACION') {
            return redirect()
                ->route('annual_budgets.show', $annualBudget->id)
                ->with('error', 'Solo se pueden crear distribuciones para presupuestos en estado PLANIFICACION.');
        }

        if ($annualBudget->monthlyDistributions()->exists()) {
            return redirect()
                ->route('budget_monthly_distributions.edit', $annualBudget->id)
                ->with('info', 'Este presupuesto ya tiene distribuciones. Redirigiendo a edición.');
        }

        [$allCedulas, $cedulasByCategory] = $this->getCedulaCatalog();

        return view('budget_monthly_distributions.create', [
            'annualBudget' => $annualBudget,
            'allCedulas' => $allCedulas,
            'cedulasByCategory' => $cedulasByCategory,
            'selectedCategoryIds' => [],
            'distributions' => [],
            'isEdit' => false,
        ]);
    }

    public function store(SaveBudgetMonthlyDistributionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $budget = AnnualBudget::findOrFail($validated['annual_budget_id']);
        $cedulas = BudgetCedula::with('expenseCategory')
            ->whereIn('id', array_keys($validated['distributions']))
            ->get()
            ->keyBy('id');

        try {
            DB::beginTransaction();

            $rows = [];
            foreach ($validated['distributions'] as $cedulaId => $months) {
                $cedula = $cedulas->get((int) $cedulaId);
                if (! $cedula) {
                    continue;
                }

                foreach ($months as $month => $amount) {
                    $rows[] = [
                        'annual_budget_id' => $budget->id,
                        'budget_cedula_id' => (int) $cedulaId,
                        'expense_category_id' => $cedula->expense_category_id,
                        'month' => (int) $month,
                        'assigned_amount' => $amount,
                        'consumed_amount' => 0,
                        'committed_amount' => 0,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            BudgetMonthlyDistribution::insert($rows);

            DB::commit();

            return redirect()
                ->route('annual_budgets.show', $budget->id)
                ->with('success', 'Distribuciones mensuales por cédula creadas exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Error al guardar las distribuciones: ' . $e->getMessage());
        }
    }

    public function edit(AnnualBudget $annual_budget): View
    {
        $annualBudget = $this->loadBudgetWithDistributions($annual_budget->id);
        [$allCedulas, $cedulasByCategory] = $this->getCedulaCatalog();

        $distributions = [];
        foreach ($annualBudget->monthlyDistributions as $dist) {
            $distributions[$dist->budget_cedula_id][$dist->month] = [
                'id' => $dist->id,
                'assigned_amount' => (float) $dist->assigned_amount,
                'consumed_amount' => (float) $dist->consumed_amount,
                'committed_amount' => (float) $dist->committed_amount,
                'available_amount' => $dist->getAvailableAmount(),
            ];
        }

        return view('budget_monthly_distributions.edit', [
            'annualBudget' => $annualBudget,
            'allCedulas' => $allCedulas,
            'cedulasByCategory' => $cedulasByCategory,
            'selectedCategoryIds' => $annualBudget->monthlyDistributions
                ->pluck('expense_category_id')
                ->unique()
                ->values()
                ->all(),
            'distributions' => $distributions,
            'isEdit' => true,
        ]);
    }

    public function update(SaveBudgetMonthlyDistributionRequest $request, AnnualBudget $annual_budget): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            foreach ($validated['distributions'] as $distData) {
                $distribution = BudgetMonthlyDistribution::findOrFail($distData['id']);
                $distribution->update([
                    'assigned_amount' => $distData['assigned_amount'],
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('annual_budgets.show', $annual_budget->id)
                ->with('success', 'Distribuciones mensuales actualizadas exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar las distribuciones: ' . $e->getMessage());
        }
    }

    public function show(BudgetMonthlyDistribution $budget_monthly_distribution): View
    {
        $distribution = $budget_monthly_distribution->load([
            'annualBudget.costCenter.company',
            'budgetCedula.expenseCategory',
            'createdBy',
            'updatedBy',
        ]);

        return view('budget_monthly_distributions.show', compact('distribution'));
    }

    public function getByBudget(AnnualBudget $annual_budget)
    {
        $budget = $this->loadBudgetWithDistributions($annual_budget->id);

        return response()->json($this->buildMonthlyCategoryRows($budget)->values());
    }

    public function checkAvailability(AnnualBudget $annual_budget, int $month, int $categoryId)
    {
        $budget = $this->loadBudgetWithDistributions($annual_budget->id);
        $summary = $this->categorySummaryService->forBudgetMonthAndCategory($budget, $month, $categoryId);

        if (! $summary) {
            return response()->json([
                'available' => false,
                'message' => 'No existe distribución para este mes y categoría.',
            ], 404);
        }

        return response()->json([
            'available' => true,
            'assigned_amount' => $summary['assigned_amount'],
            'consumed_amount' => $summary['consumed_amount'],
            'committed_amount' => $summary['committed_amount'],
            'available_amount' => $summary['available_amount'],
        ]);
    }

    public function matrix(AnnualBudget $annual_budget): View
    {
        $budget = $this->loadBudgetWithDistributions($annual_budget->id);
        $categories = $budget->monthlyDistributions
            ->pluck('expenseCategory')
            ->filter()
            ->unique('id')
            ->sortBy('code')
            ->values();

        $matrix = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthSummary = $this->categorySummaryService->forBudgetMonth($budget, $month)->keyBy('category_id');
            foreach ($categories as $category) {
                $matrix[$month][$category->id] = $monthSummary->get($category->id);
            }
        }

        return view('budget_monthly_distributions.matrix', [
            'budget' => $budget,
            'matrix' => $matrix,
            'categories' => $categories,
        ]);
    }

    private function loadBudgetWithDistributions(int $budgetId): AnnualBudget
    {
        return AnnualBudget::with([
            'costCenter.company',
            'monthlyDistributions' => fn ($query) => $query
                ->with(['budgetCedula.expenseCategory', 'expenseCategory'])
                ->orderBy('month')
                ->orderBy('expense_category_id')
                ->orderBy('budget_cedula_id'),
        ])->findOrFail($budgetId);
    }

    private function getCedulaCatalog(): array
    {
        $allCedulas = BudgetCedula::with('expenseCategory')
            ->active()
            ->notDeleted()
            ->orderBy('expense_category_id')
            ->orderBy('name')
            ->get();

        return [$allCedulas, $allCedulas->groupBy('expense_category_id')];
    }

    private function buildMonthlyCategoryRows(AnnualBudget $budget): Collection
    {
        return $budget->monthlyDistributions
            ->groupBy(fn (BudgetMonthlyDistribution $distribution) => $distribution->month . ':' . $distribution->expense_category_id)
            ->map(function (Collection $items) {
                $first = $items->first();
                $assigned = (float) $items->sum('assigned_amount');
                $consumed = (float) $items->sum('consumed_amount');
                $committed = (float) $items->sum('committed_amount');
                $available = $assigned - $consumed - $committed;

                return [
                    'month' => $first->month,
                    'month_label' => $first->month_label,
                    'category_id' => $first->expense_category_id,
                    'category_code' => $first->expenseCategory?->code ?? '—',
                    'category_name' => $first->expenseCategory?->name ?? '—',
                    'assigned_amount' => $assigned,
                    'consumed_amount' => $consumed,
                    'committed_amount' => $committed,
                    'available_amount' => $available,
                    'usage_percentage' => $assigned > 0 ? (($consumed + $committed) / $assigned) * 100 : 0,
                    'status' => $available <= 0 ? 'AGOTADO' : ($assigned > 0 && (($consumed + $committed) / $assigned) > 0.7 ? 'CRITICO' : 'NORMAL'),
                ];
            })
            ->sortBy(fn (array $row) => sprintf('%02d-%s', $row['month'], $row['category_code']))
            ->values();
    }

    private function statusBadge(string $status): string
    {
        return match ($status) {
            'NORMAL' => '<span class="badge bg-success">Normal</span>',
            'CRITICO' => '<span class="badge bg-danger">Crítico</span>',
            'AGOTADO' => '<span class="badge bg-dark">Agotado</span>',
            default => '<span class="badge bg-warning text-dark">Alerta</span>',
        };
    }
}
