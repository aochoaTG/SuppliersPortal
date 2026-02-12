<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCostCenterRequest;
use App\Models\Category;
use App\Models\CostCenter;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * CostCenterController
 *
 * CRUD + endpoint DataTable (server-side) con nueva estructura de auditoría.
 */
class CostCenterController extends Controller
{
    /**
     * Vista principal con DataTable.
     */
    public function index(): View
    {
        return view('cost_centers.index');
    }

    /**
     * Endpoint para DataTable server-side (JSON).
     * Retorna: id, code, name, category, company, budget_type, status, created_by, actions
     */
    public function datatable(Request $request)
    {
        // Eager load de relaciones necesarias
        $query = CostCenter::query()
            ->with([
                'category',
                'company',
                'responsible',
                'createdBy',
            ])
            ->notDeleted(); // 🔴 CAMBIO: Excluir soft-deleted

        return DataTables::of($query)
            // ===== COLUMNAS BÁSICAS =====
            ->editColumn('code', function ($row) {
                return '<strong>' . e($row->code) . '</strong>';
            })
            ->editColumn('name', function ($row) {
                return e($row->name);
            })

            // ===== COLUMNA: CATEGORÍA =====
            ->addColumn('category_name', function ($row) {
                return e($row->category?->name ?? '—');
            })

            // ===== COLUMNA: EMPRESA =====
            ->addColumn('company_name', function ($row) {
                return e($row->company?->name ?? '—');
            })

            // ===== COLUMNA: TIPO DE PRESUPUESTO =====
            ->addColumn('budget_type_label', function ($row) {
                $badge = $row->budget_type === 'ANNUAL'
                    ? '<span class="badge bg-info">Presupuesto Anual</span>'
                    : '<span class="badge bg-warning">Consumo Libre</span>';
                return $badge;
            })

            // ===== COLUMNA: ESTADO (reemplaza is_active) =====
            ->editColumn('status', function ($row) {
                // 🔴 CAMBIO: Usa 'status' en lugar de 'is_active'
                $badge = $row->status === 'ACTIVO'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>';
                return $badge;
            })

            // ===== COLUMNA: RESPONSABLE =====
            ->addColumn('responsible_name', function ($row) {
                return e($row->responsible?->name ?? '—');
            })

            // ===== COLUMNA: CREADO POR (AUDITORÍA) =====
            ->addColumn('created_by_name', function ($row) {
                return e($row->createdBy?->name ?? '—');
            })

            // ===== COLUMNA: ACCIONES =====
            ->addColumn('actions', function ($row) {
                $editUrl = route('cost-centers.edit', $row->id);
                $deleteUrl = route('cost-centers.destroy', $row->id);

                return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" 
                                id="dropdownActions' . $row->id . '" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            Acciones
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownActions' . $row->id . '">
                            <li>
                                <a class="dropdown-item" href="' . $editUrl . '">
                                    <i class="ti ti-edit me-2"></i>Editar
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="button" class="dropdown-item text-danger js-delete-btn"
                                            data-entity="' . e($row->name) . '">
                                        <i class="ti ti-trash me-2"></i>Eliminar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>';
            })

            ->rawColumns(['code', 'budget_type_label', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        // 🔴 CAMBIO: Inicializar con nuevos valores por defecto
        $costCenter = new CostCenter([
            'status' => 'ACTIVO',
            'budget_type' => 'ANNUAL',
        ]);

        // Cargar datos para dropdowns
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $companies = Company::orderBy('name')
            ->get(['id', 'name']);

        // ✅ CAMBIO: Solo obtener usuarios activos (sin filtro de status)
        $users = User::orderBy('name')
            ->get(['id', 'name']);

        return view('cost_centers.create', compact(
            'costCenter',
            'categories',
            'companies',
            'users'
        ));
    }

    /**
     * Guardar nuevo centro de costo.
     */
    public function store(SaveCostCenterRequest $request): RedirectResponse
    {
        // 🔴 CAMBIO: Agregar auditoría (created_by, updated_by)
        $data = $request->validated();
        $data['created_by'] = auth()->id(); // Usuario autenticado
        $data['updated_by'] = null; // Aún no ha sido modificado

        CostCenter::create($data);

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(CostCenter $cost_center): View
    {
        // Cargar datos para dropdowns
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $companies = Company::orderBy('name')
            ->get(['id', 'name']);

        $users = User::orderBy('name')
            ->get(['id', 'name']);

        // ✅ SOLUCIÓN: Pasar ambas versiones del nombre
        return view('cost_centers.edit', [
            'cost_center' => $cost_center,   // Para edit.blade.php
            'costCenter' => $cost_center,    // Para el form partial
            'categories' => $categories,
            'companies' => $companies,
            'users' => $users
        ]);
    }

    /**
     * Actualizar centro de costo.
     */
    public function update(SaveCostCenterRequest $request, CostCenter $cost_center): RedirectResponse
    {
        // 🔴 CAMBIO: Agregar auditoría (updated_by)
        $data = $request->validated();
        $data['updated_by'] = auth()->id(); // Usuario que modifica

        $cost_center->update($data);

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo actualizado correctamente.');
    }

    /**
     * Eliminar centro de costo (soft delete).
     */
    public function destroy(CostCenter $cost_center): RedirectResponse
    {
        // 🔴 CAMBIO: Agregar auditoría (deleted_by) antes de soft delete
        $cost_center->update(['deleted_by' => auth()->id()]);
        $cost_center->delete(); // Soft delete

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo eliminado correctamente.');
    }

    /**
     * Obtener centros de costo por empresa (AJAX).
     * Usado en formarios dependientes: Empresa → Centros de Costo
     */
    public function byCompany(Company $company)
    {
        $centers = CostCenter::query()
            ->where('company_id', $company->id)
            ->where('status', 'ACTIVO')
            ->notDeleted()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($centers);
    }
}
