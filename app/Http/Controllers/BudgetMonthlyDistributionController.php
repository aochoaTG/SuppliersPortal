<?php

namespace App\Http\Controllers;

use App\Models\AnnualBudget;
use App\Models\BudgetMonthlyDistribution;
use App\Models\ExpenseCategory;
use App\Http\Requests\SaveBudgetMonthlyDistributionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class BudgetMonthlyDistributionController extends Controller
{
    /**
     * Listar distribuciones mensuales de un presupuesto anual.
     */
    public function index(Request $request): View
    {
        $budgetId = $request->query('annual_budget_id');

        $budget = null;
        if ($budgetId) {
            $budget = AnnualBudget::with('costCenter.company')
                ->findOrFail($budgetId);
        }

        // Cargar todos los presupuestos anuales para el filtro usando el scope withDetails
        $budgets = AnnualBudget::withDetails()->get();

        return view('budget_monthly_distributions.index', compact('budget', 'budgets'));
    }

    /**
     * Endpoint DataTable: distribuciones mensuales.
     */
    public function datatable(Request $request)
    {
        $budgetId = $request->query('annual_budget_id');

        if (!$budgetId) {
            return response()->json(['error' => 'Budget ID requerido'], 400);
        }

        $query = BudgetMonthlyDistribution::query()
            ->with([
                'annualBudget:id,cost_center_id,fiscal_year',
                'expenseCategory:id,code,name',
                'createdBy:id,name',
            ])
            ->where('annual_budget_id', $budgetId)
            ->notDeleted();
        // 🔴 QUITAR LOS orderBy() de aquí
        // ->orderBy('month')
        // ->orderBy('expense_category_id');

        return DataTables::of($query)
            // ===== COLUMNA: MES =====
            ->addColumn('month_label', fn(BudgetMonthlyDistribution $row) => $row->month_label)

            // ===== COLUMNA: CATEGORÍA =====
            ->addColumn('category_code', function (BudgetMonthlyDistribution $row) {
                $code = $row->expenseCategory?->code ?? '—';
                $name = $row->expenseCategory?->name ?? '—';
                return e("[$code] $name");
            })

            // ===== COLUMNA: ASIGNADO =====
            ->editColumn('assigned_amount', function (BudgetMonthlyDistribution $row) {
                return '$' . number_format((float) $row->assigned_amount, 2);
            })

            // ===== COLUMNA: CONSUMIDO =====
            ->editColumn('consumed_amount', function (BudgetMonthlyDistribution $row) {
                return '$' . number_format((float) $row->consumed_amount, 2);
            })

            // ===== COLUMNA: COMPROMETIDO =====
            ->editColumn('committed_amount', function (BudgetMonthlyDistribution $row) {
                return '$' . number_format((float) $row->committed_amount, 2);
            })

            // ===== COLUMNA: DISPONIBLE =====
            ->addColumn('available_amount', function (BudgetMonthlyDistribution $row) {
                $available = $row->getAvailableAmount();
                $val = number_format($available, 2);

                $class = 'badge bg-secondary';
                if ($available > 0)
                    $class = 'badge bg-success';
                if ($available == 0.0)
                    $class = 'badge bg-warning text-dark';
                if ($available < 0)
                    $class = 'badge bg-danger';

                return '<span class="' . $class . '">$' . $val . '</span>';
            })

            // ===== COLUMNA: % UTILIZACIÓN =====
            ->addColumn('usage_percentage', function (BudgetMonthlyDistribution $row) {
                $pct = number_format($row->getUsagePercentage(), 1);
                $class = match (true) {
                    $row->getUsagePercentage() > 90 => 'badge bg-danger',
                    $row->getUsagePercentage() > 70 => 'badge bg-warning text-dark',
                    default => 'badge bg-success',
                };
                return '<span class="' . $class . '">' . $pct . '%</span>';
            })

            // ===== COLUMNA: ESTADO =====
            ->addColumn('status_label', fn(BudgetMonthlyDistribution $row) => $row->status_label)

            // 🔴 AGREGAR EL ORDER BY AQUÍ (después de procesar columnas)
            ->orderColumn('month', function ($query, $direction) {
                $query->orderBy('month', $direction);
            })
            ->orderColumn('expense_category_id', function ($query, $direction) {
                $query->orderBy('expense_category_id', $direction);
            })

            ->rawColumns(['available_amount', 'usage_percentage', 'status_label'])
            ->make(true);
    }

    /**
     * Mostrar formulario para crear distribuciones mensuales.
     */
    public function create(AnnualBudget $annualBudget): View|RedirectResponse
    {
        // Cargar la relación si no está cargada
        $annualBudget->loadMissing(['costCenter.company']);

        // Verificar que el presupuesto esté en PLANIFICACION
        if ($annualBudget->status !== 'PLANIFICACION') {
            return redirect()
                ->route('annual_budgets.show', $annualBudget->id)
                ->with('error', 'Solo se pueden crear distribuciones para presupuestos en estado PLANIFICACION.');
        }

        // Verificar si ya existen distribuciones
        $existingCount = $annualBudget->monthlyDistributions()->count();
        if ($existingCount > 0) {
            return redirect()
                ->route('budget_monthly_distributions.edit', $annualBudget->id)
                ->with('info', 'Este presupuesto ya tiene distribuciones. Redirigiendo a edición.');
        }

        // Obtener todas las categorías de gasto activas
        $categories = ExpenseCategory::where('status', 'ACTIVO')
            ->orderBy('code')
            ->get();

        // Pasar TODAS las categorías disponibles
        $allCategories = ExpenseCategory::orderBy('code')->get();

        // Preparar datos para el formulario
        $distributions = [];
        foreach ($categories as $category) {
            for ($month = 1; $month <= 12; $month++) {
                $distributions[$category->id][$month] = 0; // Inicializar en 0
            }
        }

        return view('budget_monthly_distributions.create', compact('annualBudget', 'categories', 'distributions', 'allCategories'));
    }

    /**
     * Guardar distribuciones mensuales masivamente.
     */
    public function store(SaveBudgetMonthlyDistributionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $budget = AnnualBudget::findOrFail($validated['annual_budget_id']);

        try {
            DB::beginTransaction();

            // Crear distribuciones masivamente
            $distributionsData = [];
            foreach ($validated['distributions'] as $categoryId => $months) {
                foreach ($months as $month => $amount) {
                    $distributionsData[] = [
                        'annual_budget_id' => $budget->id,
                        'expense_category_id' => $categoryId,
                        'month' => $month,
                        'assigned_amount' => $amount,
                        'consumed_amount' => 0,
                        'committed_amount' => 0,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insertar todas las distribuciones de una vez
            BudgetMonthlyDistribution::insert($distributionsData);

            DB::commit();

            return redirect()
                ->route('annual_budgets.show', $budget->id)
                ->with('success', 'Distribuciones mensuales creadas exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al guardar las distribuciones: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar las distribuciones mensuales de un presupuesto anual.
     *
     * @param AnnualBudget $annual_budget Instancia del presupuesto anual a editar
     * @return View Vista con el formulario de edición
     */
    public function edit(AnnualBudget $annual_budget): View
    {
        // Cargar relaciones necesarias para la vista
        // - Centro de costo con su compañía asociada
        // - Distribuciones mensuales con sus categorías de gasto
        $annualBudget = $annual_budget->load([
            'costCenter.company',
            'monthlyDistributions.expenseCategory'
        ]);

        // 🔴 TODAS las categorías activas disponibles (para el selector)
        $allCategories = ExpenseCategory::where('status', 'ACTIVO')
            ->orderBy('code')
            ->get();

        // 🔴 Categorías YA seleccionadas (que tienen distribuciones)
        $selectedCategories = ExpenseCategory::where('status', 'ACTIVO')
            ->whereHas('budgetMonthlyDistributions', function ($query) use ($annualBudget) {
                $query->where('annual_budget_id', $annualBudget->id);
            })
            ->orderBy('code')
            ->get();

        // Inicializar array para organizar las distribuciones existentes
        // La estructura será: [category_id][month] = datos_distribución
        $distributions = [];

        // Procesar cada distribución mensual existente
        foreach ($annualBudget->monthlyDistributions as $dist) {
            $distributions[$dist->expense_category_id][$dist->month] = [
                'id' => $dist->id,
                'assigned_amount' => (float)$dist->assigned_amount,    // Monto asignado
                'consumed_amount' => (float)$dist->consumed_amount,    // Monto consumido
                'committed_amount' => (float)$dist->committed_amount,  // Monto comprometido
                'available_amount' => $dist->getAvailableAmount(),     // Monto disponible (calculado)
            ];
        }

        // 🔴 NO pre-llenamos todas las categorías, solo las que tienen distribuciones
        // La matriz se generará dinámicamente en JavaScript según categorías seleccionadas

        // Retornar la vista con los datos preparados
        return view('budget_monthly_distributions.edit', [
            'annualBudget' => $annualBudget,              // Datos del presupuesto anual
            'allCategories' => $allCategories,            // 🔴 Todas las categorías disponibles
            'selectedCategories' => $selectedCategories,  // 🔴 Categorías ya en uso
            'distributions' => $distributions,            // Matriz de distribuciones existentes
            'isEdit' => true                              // 🔴 Flag para modo edición
        ]);
    }

    /**
     * Actualizar distribuciones mensuales masivamente.
     */
    public function update(SaveBudgetMonthlyDistributionRequest $request, AnnualBudget $annual_budget)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Actualizar cada distribución
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
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar las distribuciones: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de una distribución mensual.
     */
    public function show(BudgetMonthlyDistribution $budget_monthly_distribution): View
    {
        $distribution = $budget_monthly_distribution->load([
            'annualBudget.costCenter.company',
            'expenseCategory',
            'createdBy',
            'updatedBy',
        ]);

        return view('budget_monthly_distributions.show', compact('distribution'));
    }

    /**
     * Obtener distribuciones de un presupuesto (JSON API).
     * Útil para AJAX y selectores dinámicos.
     */
    public function getByBudget(AnnualBudget $annual_budget)
    {
        $distributions = $annual_budget->monthlyDistributions()
            ->with('expenseCategory:id,code,name')
            ->notDeleted()
            ->orderBy('month')
            ->get()
            ->map(function ($dist) {
                return [
                    'id' => $dist->id,
                    'month' => $dist->month,
                    'month_label' => $dist->month_label,
                    'category_code' => $dist->expenseCategory->code,
                    'category_name' => $dist->expenseCategory->name,
                    'assigned_amount' => (float) $dist->assigned_amount,
                    'consumed_amount' => (float) $dist->consumed_amount,
                    'committed_amount' => (float) $dist->committed_amount,
                    'available_amount' => $dist->getAvailableAmount(),
                    'usage_percentage' => $dist->getUsagePercentage(),
                    'status' => $dist->status,
                ];
            });

        return response()->json($distributions);
    }

    /**
     * Obtener distribución por mes y categoría (JSON API).
     * Útil para validación presupuestal en requisiciones.
     */
    public function checkAvailability(AnnualBudget $annual_budget, int $month, int $categoryId)
    {
        $distribution = $annual_budget->monthlyDistributions()
            ->where('month', $month)
            ->where('expense_category_id', $categoryId)
            ->first();

        if (!$distribution) {
            return response()->json([
                'available' => false,
                'message' => 'No existe distribución para este mes y categoría.',
            ], 404);
        }

        return response()->json([
            'available' => true,
            'assigned_amount' => (float) $distribution->assigned_amount,
            'consumed_amount' => (float) $distribution->consumed_amount,
            'committed_amount' => (float) $distribution->committed_amount,
            'available_amount' => $distribution->getAvailableAmount(),
            'can_commit' => fn($amount) => $distribution->canCommit($amount),
        ]);
    }

    /**
     * Matriz mensual (mes x categoría) para un presupuesto.
     * Vista tabular para análisis rápido.
     */
    public function matrix(AnnualBudget $annual_budget): View
    {
        $annual_budget->load('costCenter.company', 'monthlyDistributions.expenseCategory');

        // Obtener todas las categorías
        $categories = $annual_budget->monthlyDistributions()
            ->distinct()
            ->pluck('expenseCategory')
            ->sortBy('code');

        // Organizar en matriz: meses x categorías
        $matrix = [];
        for ($month = 1; $month <= 12; $month++) {
            $matrix[$month] = [];
            foreach ($categories as $category) {
                $distribution = $annual_budget->monthlyDistributions()
                    ->where('month', $month)
                    ->where('expense_category_id', $category->id)
                    ->first();

                $matrix[$month][$category->id] = $distribution ?? null;
            }
        }

        return view('budget_monthly_distributions.matrix', [
            'budget' => $annual_budget,
            'matrix' => $matrix,
            'categories' => $categories,
        ]);
    }
}
