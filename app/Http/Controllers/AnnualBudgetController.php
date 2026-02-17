<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAnnualBudgetRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ApproveBudgetRequest;
use App\Models\AnnualBudget;
use App\Models\CostCenter;
use App\Models\ExpenseCategory;
use App\Models\BudgetMonthlyDistribution;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;


class AnnualBudgetController extends Controller
{
    /**
     * Vista principal con DataTable.
     */
    public function index(): View
    {
        return view('annual_budgets.index');
    }

    /**
     * Endpoint DataTable: lista presupuestos anuales.
     */
    public function datatable(Request $request)
    {
        $query = AnnualBudget::query()
            ->with([
                'costCenter:id,code,name,company_id',
                'costCenter.company:id,name',
                'approvedBy:id,name',
                'createdBy:id,name',
            ])
            ->withCount('monthlyDistributions')
            ->notDeleted();

        // ===== FILTROS OPCIONALES =====
        if ($request->filled('fiscal_year')) {
            $query->where('fiscal_year', (int) $request->input('fiscal_year'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', (int) $request->input('cost_center_id'));
        }

        return DataTables::of($query)
            // ===== COLUMNA: EMPRESA =====
            ->addColumn('company_name', function (AnnualBudget $row) {
                return e($row->costCenter?->company?->name ?? '—');
            })

            // ===== COLUMNA: CENTRO DE COSTO =====
            ->addColumn('cost_center_label', function (AnnualBudget $row) {
                if (!$row->costCenter) {
                    return '—';
                }
                $code = $row->costCenter->code ? '[' . $row->costCenter->code . '] ' : '';
                $name = $row->costCenter->name ?? '—';
                return e($code . $name);
            })

            // ===== COLUMNA: AÑO FISCAL =====
            ->editColumn('fiscal_year', fn(AnnualBudget $row) => (int) $row->fiscal_year)

            // ===== COLUMNA: MONTO ANUAL =====
            ->editColumn('total_annual_amount', function (AnnualBudget $row) {
                return '$' . number_format((float) $row->total_annual_amount, 2);
            })

            // ===== COLUMNA: ESTADO =====
            ->addColumn('status_label', function (AnnualBudget $row) {
                $badges = [
                    'PLANIFICACION' => '<span class="badge bg-info">En Planificación</span>',
                    'APROBADO' => '<span class="badge bg-success">Aprobado</span>',
                    'CERRADO' => '<span class="badge bg-secondary">Cerrado</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge bg-secondary">' . e($row->status) . '</span>';
            })

            // ===== COLUMNA: APROBADO POR =====
            ->addColumn('approved_by_name', function (AnnualBudget $row) {
                return e($row->approvedBy?->name ?? '—');
            })

            // ===== COLUMNA: ACCIONES =====
            ->addColumn('actions', function (AnnualBudget $row) {
                $editUrl = route('annual_budgets.edit', $row->id);
                $deleteUrl = route('annual_budgets.destroy', $row->id);
                $showUrl = route('annual_budgets.show', $row->id);

                // Iniciar dropdown
                $dropdown = '
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Acciones
                        </button>
                        <ul class="dropdown-menu">
                ';

                // ===== OPCIÓN: VER DETALLE =====
                $dropdown .= '
                            <li>
                                <a class="dropdown-item" href="' . $showUrl . '">
                                    <i class="ti ti-eye me-2"></i> Ver detalle
                                </a>
                            </li>
                ';

                // ===== OPCIÓN: DISTRIBUCIONES MENSUALES =====
                if ($row->monthly_distributions_count > 0) {
                    // Si ya tiene distribuciones, mostrar opción para ver/editar
                    $distributionsUrl = route('budget_monthly_distributions.edit', $row->id);
                    $dropdown .= '
                            <li>
                                <a class="dropdown-item" href="' . $distributionsUrl . '">
                                    <i class="ti ti-calendar-stats me-2"></i> Ver/Editar Distribuciones
                                </a>
                            </li>
                    ';
                } elseif ($row->status === 'PLANIFICACION') {
                    // Si no tiene distribuciones y está en PLANIFICACION, mostrar opción para crear
                    $distributionsUrl = route('budget_monthly_distributions.create', $row->id);
                    $dropdown .= '
                            <li>
                                <a class="dropdown-item" href="' . $distributionsUrl . '">
                                    <i class="ti ti-calendar-plus me-2"></i> Crear Distribuciones
                                </a>
                            </li>
                    ';
                }

                // ===== SEPARADOR (si hay opciones de edición/eliminación) =====
                if ($row->status === 'PLANIFICACION') {
                    $dropdown .= '<li><hr class="dropdown-divider"></li>';
                }

                // ===== OPCIÓN: EDITAR =====
                if ($row->status === 'PLANIFICACION') {
                    $dropdown .= '
                            <li>
                                <a class="dropdown-item" href="' . $editUrl . '">
                                    <i class="ti ti-edit me-2"></i> Editar
                                </a>
                            </li>
                    ';
                }

                // ===== OPCIÓN: APROBAR =====
                if ($row->status === 'PLANIFICACION') {
                    $approveUrl = route('annual_budgets.approve', $row->id);
                    $dropdown .= '
                            <li>
                                <a class="dropdown-item text-success" href="' . $approveUrl . '">
                                    <i class="ti ti-check me-2"></i> Aprobar
                                </a>
                            </li>
                    ';
                }

                // ===== OPCIÓN: ELIMINAR =====
                $dropdown .= '
                            <li>
                                <form action="' . $deleteUrl . '" method="POST" class="js-delete-form">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="button" class="dropdown-item text-danger js-delete-btn"
                                            data-entity="Presupuesto ' . $row->fiscal_year . '">
                                        <i class="ti ti-trash me-2"></i> Eliminar
                                    </button>
                                </form>
                            </li>
                    ';

                // Cerrar dropdown
                $dropdown .= '
                        </ul>
                    </div>
                ';

                return $dropdown;
            })


            ->rawColumns(['status_label', 'actions'])
            ->make(true);
    }

    /**
     * Ver detalle de un presupuesto anual.
     */
    public function show(AnnualBudget $annual_budget): View
    {
        $annual_budget->load([
            'costCenter.company',
            'monthlyDistributions.expenseCategory',
            'approvedBy',
            'createdBy',
            'updatedBy',
        ]);

        // Calcular totales
        $totalAssigned = $annual_budget->monthlyDistributions()->sum('assigned_amount');
        $totalConsumed = $annual_budget->monthlyDistributions()->sum('consumed_amount');
        $totalCommitted = $annual_budget->monthlyDistributions()->sum('committed_amount');
        $totalAvailable = $totalAssigned - $totalConsumed - $totalCommitted;

        $summary = [
            'total_assigned' => $totalAssigned,
            'total_consumed' => $totalConsumed,
            'total_committed' => $totalCommitted,
            'total_available' => $totalAvailable,
            'usage_percentage' => ($totalAssigned > 0)
                ? (($totalConsumed + $totalCommitted) / $totalAssigned) * 100
                : 0,
        ];

        return view('annual_budgets.show', compact('annual_budget', 'summary'));
    }

    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        $annualBudget = new AnnualBudget([
            'fiscal_year' => (int) date('Y'),
            'total_annual_amount' => 0,
            'status' => 'PLANIFICACION',
        ]);

        $costCenters = CostCenter::where('budget_type', 'ANNUAL')
            ->where('status', 'ACTIVO')
            ->with('company', 'category')
            ->orderBy('name')
            ->get();

        return view('annual_budgets.create', compact('annualBudget', 'costCenters'));
    }

    /**
     * Guardar nuevo presupuesto.
     */
    public function store(SaveAnnualBudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['status'] = 'PLANIFICACION';

        AnnualBudget::create($data);

        return redirect()
            ->route('annual_budgets.index')
            ->with('success', 'Presupuesto anual creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(AnnualBudget $annual_budget): View|RedirectResponse
    {
        // Solo permitir editar si está en PLANIFICACION
        if ($annual_budget->status !== 'PLANIFICACION') {
            return redirect()
                ->route('annual_budgets.index')
                ->with('warning', 'Solo se pueden editar presupuestos en estado "En Planificación".');
        }

        $costCenters = CostCenter::where('budget_type', 'ANNUAL')
            ->where('status', 'ACTIVO')
            ->whereDoesntHave('annualBudgets', function ($query) use ($annual_budget) {
                $query->where('fiscal_year', $annual_budget->fiscal_year)
                    ->where('id', '!=', $annual_budget->id); // Excluir el presupuesto actual
            })
            ->with('company', 'category')
            ->orderBy('name')
            ->get();

        return view('annual_budgets.edit', compact('annual_budget', 'costCenters'));
    }

    /**
     * Actualizar presupuesto.
     */
    public function update(SaveAnnualBudgetRequest $request, AnnualBudget $annual_budget): RedirectResponse
    {
        // Solo permitir editar si está en PLANIFICACION
        if ($annual_budget->status !== 'PLANIFICACION') {
            return redirect()
                ->route('annual_budgets.index')
                ->with('warning', 'Solo se pueden editar presupuestos en estado "En Planificación".');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $annual_budget->update($data);

        return redirect()
            ->route('annual_budgets.index')
            ->with('success', 'Presupuesto anual actualizado correctamente.');
    }

    /**
     * Formulario de aprobación.
     */
    public function approve(AnnualBudget $annual_budget): View|\Illuminate\Http\RedirectResponse
    {
        // Solo permitir aprobar si está en PLANIFICACION
        if ($annual_budget->status !== 'PLANIFICACION') {
            return redirect()
                ->route('annual_budgets.index')
                ->with('warning', 'Solo se pueden aprobar presupuestos en estado "En Planificación".');
        }

        $annual_budget->load('costCenter.company');

        return view('annual_budgets.approve', compact('annual_budget'));
    }

    /**
     * Procesar aprobación de presupuesto.
     */
    public function approveStore(ApproveBudgetRequest $request, AnnualBudget $annual_budget): RedirectResponse
    {
        // Solo permitir aprobar si está en PLANIFICACION
        if ($annual_budget->status !== 'PLANIFICACION') {
            return redirect()
                ->route('annual_budgets.index')
                ->with('warning', 'Solo se pueden aprobar presupuestos en estado "En Planificación".');
        }

        // Verificar que tenga distribuciones mensuales antes de aprobar
        if (!$annual_budget->monthlyDistributions()->exists()) {
            return redirect()
                ->back()
                ->with('danger', 'No se puede aprobar un presupuesto sin distribuciones mensuales. Por favor, crea primero las distribuciones.');
        }

        $annual_budget->update([
            'status' => 'APROBADO',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('annual_budgets.index')
            ->with('success', 'Presupuesto anual aprobado correctamente. Está listo para usar en requisiciones.');
    }

    /**
     * Eliminar presupuesto.
     */
    public function destroy(AnnualBudget $annual_budget): RedirectResponse
    {
        // Seguridad: si hay distribuciones, evitar borrado accidental
        if ($annual_budget->monthlyDistributions()->exists()) {
            $annual_budget->update(['deleted_by' => Auth::id()]);
        }

        $annual_budget->delete();

        return redirect()
            ->route('annual_budgets.index')
            ->with('success', 'Presupuesto anual eliminado correctamente.');
    }


    /**
     * Consultar disponibilidad de presupuesto para una categoría y mes específicos
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $costCenterId = $request->input('cost_center_id');
        $categoryId = $request->input('expense_category_id');
        $month = $request->input('application_month');
        $fiscalYear = $request->input('fiscal_year');

        // Validar parámetros requeridos
        if (!$costCenterId || !$categoryId || !$month || !$fiscalYear) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros requeridos'
            ], 400);
        }

        // 1. Buscar el presupuesto anual del centro de costos
        $annualBudget = AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $fiscalYear)
            ->first();

        if (!$annualBudget) {
            return response()->json([
                'success' => false,
                'message' => 'No hay presupuesto asignado para este centro de costos.',
                'has_budget' => false
            ]);
        }

        // 2. Verificar si es centro de consumo libre
        if ($annualBudget->is_free_consumption) {
            return response()->json([
                'success' => true,
                'message' => 'Centro de consumo libre - Sin límite presupuestal',
                'has_budget' => true,
                'is_free_consumption' => true,
                'budget_type' => 'free'
            ]);
        }

        // 3. Buscar el detalle de la categoría de gasto dentro del presupuesto anual
        $budgetDetail = $annualBudget->monthlyDistributions()
            ->where('expense_category_id', $categoryId)
            ->first();

        if (!$budgetDetail) {
            return response()->json([
                'success' => false,
                'message' => 'No hay presupuesto asignado para esta categoría de gasto.',
                'has_budget' => false
            ]);
        }

        // 4. Obtener la distribución mensual
        $monthDetail = $budgetDetail->monthlyDistributions()
            ->where('month', $month)
            ->first();

        if (!$monthDetail) {
            return response()->json([
                'success' => false,
                'message' => 'No hay presupuesto asignado para el mes seleccionado.',
                'has_budget' => false
            ]);
        }

        // 5. Calcular disponible
        $assigned = $monthDetail->assigned_amount;
        $consumed = $monthDetail->consumed_amount;
        $committed = $monthDetail->committed_amount;
        $available = $assigned - $consumed - $committed;

        $availablePercentage = $assigned > 0 ? ($available / $assigned) * 100 : 0;

        return response()->json([
            'success' => true,
            'has_budget' => true,
            'is_free_consumption' => false,
            'budget_type' => 'normal',
            'data' => [
                'assigned' => number_format($assigned, 2),
                'consumed' => number_format($consumed, 2),
                'committed' => number_format($committed, 2),
                'available' => number_format($available, 2),
                'available_raw' => $available,
                'percentage' => round($availablePercentage, 2),
                'is_sufficient' => $available > 0,
                'currency' => 'MXN'
            ],
            'message' => $available > 0
                ? 'Presupuesto disponible'
                : 'Presupuesto agotado para este mes'
        ]);
    }

    /**
     * Obtener categorías de gasto con presupuesto disponible para un centro de costo y mes específico
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableCategoriesForMonth(Request $request)
    {
        try {
            $costCenterId = $request->input('cost_center_id');
            $applicationMonth = $request->input('application_month'); // Formato: YYYY-MM

            // Validar parámetros requeridos
            if (!$costCenterId || !$applicationMonth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan parámetros requeridos (cost_center_id, application_month)',
                    'has_budget' => false,
                    'categories' => []
                ], 400);
            }

            // Obtener el centro de costo con su tipo de presupuesto
            $costCenter = CostCenter::find($costCenterId);

            if (!$costCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Centro de costo no encontrado',
                    'has_budget' => false,
                    'categories' => []
                ], 404);
            }

            // Extraer año y mes del formato YYYY-MM
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $applicationMonth);
                $fiscalYear = $date->year;
                $month = $date->month;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de mes inválido. Use YYYY-MM',
                    'has_budget' => false,
                    'categories' => []
                ], 400);
            }

            // ✅ CASO 1: Centro de costo de CONSUMO LIBRE
            if ($costCenter->budget_type === 'FREE_CONSUMPTION') {
                $allCategories = ExpenseCategory::where('is_active', true)
                    ->orderBy('code')
                    ->get()
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'code' => $category->code,
                            'name' => $category->name,
                            'is_free_consumption' => true,
                            'assigned' => 0,
                            'consumed' => 0,
                            'committed' => 0,
                            'available' => 999999999, // Sin límite
                            'available_formatted' => 'Sin límite'
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'message' => 'Centro de consumo libre - Sin límite presupuestal',
                    'has_budget' => true,
                    'is_free_consumption' => true,
                    'budget_type' => 'FREE_CONSUMPTION',
                    'categories' => $allCategories,
                    'budget_info' => [
                        'cost_center' => $costCenter->name,
                        'year' => $fiscalYear,
                        'month' => $month,
                        'month_name' => $date->locale('es')->isoFormat('MMMM')
                    ]
                ]);
            }

            // ✅ CASO 2: Centro de costo ANUAL (con presupuesto)

            // Buscar el presupuesto anual del centro de costos
            $annualBudget = AnnualBudget::where('cost_center_id', $costCenterId)
                ->where('fiscal_year', $fiscalYear)
                ->whereIn('status', ['APROBADO', 'PLANIFICACION'])
                ->first();

            if (!$annualBudget) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe presupuesto configurado para este centro de costo en el año ' . $fiscalYear,
                    'error_type' => 'NO_BUDGET',
                    'has_budget' => false,
                    'categories' => []
                ]);
            }

            // Obtener distribuciones mensuales con presupuesto disponible
            $distributions = BudgetMonthlyDistribution::where('annual_budget_id', $annualBudget->id)
                ->where('month', $month)
                ->with('expenseCategory')
                ->get();

            if ($distributions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay presupuesto asignado para el mes seleccionado',
                    'error_type' => 'NO_MONTHLY_DISTRIBUTION',
                    'has_budget' => false,
                    'categories' => []
                ]);
            }

            // Filtrar categorías con presupuesto disponible (available > 0)
            $availableCategories = $distributions->filter(function ($distribution) {
                $available = $distribution->assigned_amount - $distribution->consumed_amount - $distribution->committed_amount;
                return $available > 0;
            })->map(function ($distribution) {
                $available = $distribution->assigned_amount - $distribution->consumed_amount - $distribution->committed_amount;
                return [
                    'id' => $distribution->expense_category_id,
                    'code' => $distribution->expenseCategory->code,
                    'name' => $distribution->expenseCategory->name,
                    'is_free_consumption' => false,
                    'assigned' => $distribution->assigned_amount,
                    'consumed' => $distribution->consumed_amount,
                    'committed' => $distribution->committed_amount,
                    'available' => $available,
                    'available_formatted' => '$' . number_format($available, 2, '.', ',')
                ];
            })->values();

            if ($availableCategories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay presupuesto disponible en ninguna categoría para este mes',
                    'error_type' => 'NO_AVAILABLE_BUDGET',
                    'has_budget' => false,
                    'categories' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto disponible',
                'has_budget' => true,
                'is_free_consumption' => false,
                'budget_type' => 'ANNUAL',
                'categories' => $availableCategories,
                'budget_info' => [
                    'cost_center' => $costCenter->name,
                    'year' => $fiscalYear,
                    'month' => $month,
                    'month_name' => $date->locale('es')->isoFormat('MMMM'),
                    'budget_status' => $annualBudget->status
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener categorías disponibles para el mes', [
                'error' => $e->getMessage(),
                'cost_center_id' => $request->input('cost_center_id'),
                'application_month' => $request->input('application_month')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el presupuesto disponible',
                'has_budget' => false,
                'categories' => []
            ], 500);
        }
    }
}
