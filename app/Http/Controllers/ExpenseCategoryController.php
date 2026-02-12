<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\AnnualBudget;
use App\Models\CostCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetMonthlyDistribution;
use Illuminate\Support\Facades\Log;

class ExpenseCategoryController extends Controller
{
    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    // Error messages
    protected const ERROR_MESSAGES = [
        'create_error' => 'Error al crear la categoría: ',
        'cost_center_not_found' => 'Centro de costo no encontrado.',
        'cost_center_inactive' => 'El centro de costo no está activo.',
        'invalid_cost_center' => 'Centro de costo no válido o no es anual.',
        'no_annual_budget' => 'No hay presupuesto aprobado para el año :year en este centro de costo.',
        'no_distribution' => 'No existe distribución presupuestal para este mes y categoría.',
    ];

    // Success messages
    protected const SUCCESS_MESSAGES = [
        'created' => 'Categoría creada exitosamente.',
    ];
    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:3|unique:expense_categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'code.required' => 'El código es obligatorio',
            'code.max' => 'El código no debe superar los 3 caracteres',
            'code.unique' => 'Este código ya está en uso',
            'name.required' => 'El nombre es obligatorio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = ExpenseCategory::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'description' => $request->description,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => self::SUCCESS_MESSAGES['created'],
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => self::ERROR_MESSAGES['create_error'] . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active categories for select2
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForSelect(): JsonResponse
    {
        try {
            $categories = ExpenseCategory::select('id', 'code', 'name')
                ->where('status', self::STATUS_ACTIVE)
                ->orderBy('name')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'text' => "[{$category->code}] {$category->name}"
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categorías con presupuesto. 
     * Si no tiene presupuesto, no me interesa verla.
     */
    public function byBudget(Request $request): JsonResponse
    {
        // Validar parámetros. El 'fiscal_year' es obligatorio, no somos adivinos.
        $validated = $request->validate([
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'fiscal_year'    => ['required', 'integer', 'min:2024', 'max:2030'],
        ]);

        // Intentar encontrar el centro de costo.
        $costCenter = CostCenter::where('id', $validated['cost_center_id'])
            ->where('status', 'ACTIVO')
            ->first();

        // Si no está activo, el usuario está intentando hacer algo ilegal.
        if (!$costCenter || !$costCenter->isAnnual()) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de costo inválido o no es de tipo ANUAL. Intenta de nuevo cuando leas el manual.'
            ], 400);
        }

        // Buscar el presupuesto. SQL Server 2019 lo encontrará rápido gracias a tus índices.
        $annualBudget = AnnualBudget::where('cost_center_id', $costCenter->id)
            ->where('fiscal_year', $validated['fiscal_year'])
            ->where('status', 'APROBADO')
            ->first();

        if (!$annualBudget) {
            return response()->json([
                'success' => false,
                'message' => "No existe un presupuesto aprobado para el año {$validated['fiscal_year']}."
            ], 404);
        }

        /**
         * LÓGICA DE AGREGACIÓN: 
         * Aquí es donde tu CPU descansa y SQL Server trabaja. 
         * Sumamos todo en la base de datos para no traer basura al servidor de aplicaciones.
         */
        $categories = DB::table('expense_categories as ec')
            ->join('budget_monthly_distributions as md', 'ec.id', '=', 'md.expense_category_id')
            ->select(
                'ec.id',
                'ec.code',
                'ec.name',
                DB::raw('CAST(SUM(md.assigned_amount) AS FLOAT) as total_assigned'),
                DB::raw('CAST(SUM(md.consumed_amount) AS FLOAT) as total_consumed'),
                DB::raw('CAST(SUM(md.committed_amount) AS FLOAT) as total_committed'),
                // La resta que intentabas hacer en PHP, ahora hecha por profesionales (el motor de DB)
                DB::raw('CAST(SUM(md.assigned_amount - md.consumed_amount - md.committed_amount) AS FLOAT) as total_available')
            )
            ->where('md.annual_budget_id', $annualBudget->id)
            ->whereNull('md.deleted_at') // Por si usas SoftDeletes y no quieres datos fantasma
            ->groupBy('ec.id', 'ec.code', 'ec.name')
            // Filtrar solo las que tienen dinero. El que no tiene dinero no compra.
            ->having(DB::raw('SUM(md.assigned_amount - md.consumed_amount - md.committed_amount)'), '>', 0)
            ->get();

        return response()->json([
            'success'    => true,
            'categories' => $categories,
            'cost_center' => [
                'id'          => $costCenter->id,
                'name'        => $costCenter->name,
                'budget_type' => $costCenter->budget_type,
            ],
            'fiscal_year' => (int) $validated['fiscal_year']
        ]);
    }

    /**
     * API: Verificar presupuesto disponible para una categoría específica en un mes
     * 
     * GET /api/expense-categories/check-budget
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Parámetros requeridos:
     * - cost_center_id: ID del centro de costo
     * - fiscal_year: Año fiscal
     * - expense_category_id: ID de la categoría de gasto
     * - month: Mes (1-12)
     * 
     * Respuesta:
     * {
     *   "success": true,
     *   "has_budget": true,
     *   "assigned_amount": 10000.00,
     *   "consumed_amount": 2500.00,
     *   "committed_amount": 1500.00,
     *   "available_amount": 6000.00,
     *   "status": "NORMAL|ALERTA|CRÍTICO|AGOTADO",
     *   "usage_percentage": 40.00
     * }
     */
    public function checkBudget(Request $request): JsonResponse
    {
        // Validar parámetros
        $validated = $request->validate([
            'cost_center_id' => 'required|exists:cost_centers,id',
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $costCenterId = $validated['cost_center_id'];
        $fiscalYear = $validated['fiscal_year'];
        $categoryId = $validated['expense_category_id'];
        $month = $validated['month'];

        // Obtener centro de costo
        $costCenter = CostCenter::find($costCenterId);

        if (!$costCenter || !$costCenter->isAnnual()) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de costo no válido o no es anual.',
            ], 400);
        }

        // Buscar presupuesto anual
        $annualBudget = AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $fiscalYear)
            ->where('status', 'APROBADO')
            ->first();

        if (!$annualBudget) {
            return response()->json([
                'success' => false,
                'message' => 'No hay presupuesto aprobado.',
            ], 404);
        }

        // Buscar distribución mensual
        $distribution = $annualBudget->monthlyDistributions()
            ->where('month', $month)
            ->where('expense_category_id', $categoryId)
            ->first();

        if (!$distribution) {
            return response()->json([
                'success' => false,
                'message' => self::ERROR_MESSAGES['no_distribution'],
            ], 404);
        }

        $available = $distribution->getAvailableAmount();
        $assigned = $distribution->assigned_amount;
        $consumed = $distribution->consumed_amount;
        $committed = $distribution->committed_amount;

        // Calcular porcentaje de uso
        $usagePercentage = $assigned > 0
            ? (($consumed + $committed) / $assigned) * 100
            : 0;

        // Determinar estado
        $status = 'NORMAL';
        if ($available <= 0) {
            $status = 'AGOTADO';
        } elseif ($usagePercentage >= 70) {
            $status = 'CRÍTICO';
        } elseif ($usagePercentage >= 30) {
            $status = 'ALERTA';
        }

        return response()->json([
            'success' => true,
            'has_budget' => $available > 0,
            'assigned_amount' => (float) $assigned,
            'consumed_amount' => (float) $consumed,
            'committed_amount' => (float) $committed,
            'available_amount' => (float) $available,
            'status' => $status,
            'usage_percentage' => round($usagePercentage, 2),
            'category' => [
                'id' => $distribution->expenseCategory->id,
                'name' => $distribution->expenseCategory->name,
            ],
            'month' => $month,
        ]);
    }

    /**
     * Obtener categorías de gasto disponibles para un centro de costo.
     * 
     * - Si el centro es FREE_CONSUMPTION: retorna todas las categorías activas
     * - Si el centro es ANNUAL: retorna solo las categorías del presupuesto
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCostCenter(Request $request)
    {
        try {
            $costCenterId = $request->input('cost_center_id');

            if (!$costCenterId) {
                return response()->json([
                    'success' => false,
                    'message' => 'El centro de costo es requerido'
                ], 400);
            }

            // Obtener el centro de costo con su tipo de presupuesto
            $costCenter = CostCenter::find($costCenterId);

            if (!$costCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Centro de costo no encontrado'
                ], 404);
            }

            // ✅ CASO 1: Centro de costo de CONSUMO LIBRE
            if ($costCenter->budget_type === 'FREE_CONSUMPTION') {
                $categories = ExpenseCategory::active()->orderBy('code')->get(['id', 'code', 'name', 'description']);

                return response()->json([
                    'success' => true,
                    'categories' => $categories,
                    'budget_type' => 'FREE_CONSUMPTION',
                    'message' => 'Centro de costo de consumo libre - todas las categorías disponibles'
                ]);
            }

            // ✅ CASO 2: Centro de costo ANUAL (con presupuesto)
            $currentYear = now()->year;

            // Buscar el presupuesto anual activo
            $annualBudget = AnnualBudget::where('cost_center_id', $costCenterId)
                ->where('fiscal_year', $currentYear)
                ->whereIn('status', ['APROBADO', 'PLANIFICACION'])
                ->first();

            if (!$annualBudget) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'NO_BUDGET',
                    'message' => 'No hay presupuesto anual configurado para este centro de costo en ' . $currentYear,
                    'instructions' => 'Para poder crear requisiciones, es necesario que el responsable del centro de costo configure el presupuesto anual.',
                    'categories' => []
                ], 404);
            }

            // Obtener las categorías únicas de las distribuciones mensuales
            $categoryIds = \App\Models\BudgetMonthlyDistribution::where('annual_budget_id', $annualBudget->id)
                ->distinct()
                ->pluck('expense_category_id');

            if ($categoryIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'NO_CATEGORIES',
                    'message' => 'El presupuesto existe pero no tiene distribuciones mensuales configuradas',
                    'instructions' => 'Solicita al responsable del centro de costo que configure las distribuciones mensuales del presupuesto.',
                    'categories' => []
                ], 404);
            }

            // Obtener la información completa de las categorías
            $categories = \App\Models\ExpenseCategory::whereIn('id', $categoryIds)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'description']);

            return response()->json([
                'success' => true,
                'categories' => $categories,
                'budget_type' => 'ANNUAL',
                'budget_year' => $currentYear,
                'budget_status' => $annualBudget->status
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías por centro de costo', [
                'error' => $e->getMessage(),
                'cost_center_id' => $request->input('cost_center_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las categorías de gasto',
                'categories' => []
            ], 500);
        }
    }
}
