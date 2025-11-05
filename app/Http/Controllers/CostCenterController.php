<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCostCenterRequest;
use App\Http\Requests\UpdateCostCenterRequest;
use App\Models\Category;
use App\Models\CostCenter;
use App\Models\Company;     // 👈 IMPORTA EL MODELO
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * CostCenterController
 *
 * CRUD + endpoint DataTable (server-side).
 * Todos los textos para la UI en español.
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
     * Retorna columnas: id, code, name, category (nombre), is_active (badge), actions (botones).
     */
    public function datatable(Request $request)
    {
        // Eager load de category y company
        $query = CostCenter::query()->with(['category', 'company']);

        return DataTables::of($query)
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>';
            })
            ->addColumn('category', function ($row) {
                return e($row->category?->name ?? '—');
            })
            ->addColumn('company', function ($row) {        // NUEVA
                // Muestra nombre de la compañía (o código si prefieres)
                return e($row->company?->name ?? '—');
            })
            ->addColumn('actions', function ($row) {
                $editUrl = route('cost-centers.edit', $row->id);
                $deleteUrl = route('cost-centers.destroy', $row->id);

                return '
                <a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                    <i class="ti ti-edit"></i>
                </a>
                <form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                            title="Eliminar" data-entity="' . e($row->name) . '">
                        <i class="ti ti-trash"></i>
                    </button>
                </form>';
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }


    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        $costCenter = new CostCenter(['is_active' => true]);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('cost_centers.create', compact('costCenter', 'categories'));
    }

    /**
     * Guardar nuevo centro de costo.
     */
    public function store(StoreCostCenterRequest $request): RedirectResponse
    {
        CostCenter::create($request->validated());

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(CostCenter $cost_center): View
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('cost_centers.edit', [
            'costCenter' => $cost_center,
            'categories' => $categories,
        ]);
    }

    /**
     * Actualizar centro de costo.
     */
    public function update(UpdateCostCenterRequest $request, CostCenter $cost_center): RedirectResponse
    {
        $cost_center->update($request->validated());

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo actualizado correctamente.');
    }

    /**
     * Eliminar centro de costo.
     */
    public function destroy(CostCenter $cost_center): RedirectResponse
    {
        $cost_center->delete();

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo eliminado correctamente.');
    }

    public function byCompany(Company $company)
    {
        $centers = CostCenter::query()->where('company_id', $company->id)->orderBy('name')->get(['id', 'name']);

        return response()->json($centers);
    }
}
