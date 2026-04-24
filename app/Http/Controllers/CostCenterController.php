<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCostCenterRequest;
use App\Models\Category;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CostCenterController extends Controller
{
    public function index(): View
    {
        return view('cost_centers.index');
    }

    public function datatable(Request $request)
    {
        $query = CostCenter::query()
            ->with([
                'category',
                'company',
                'responsible',
                'createdBy',
            ])
            ->notDeleted();

        return DataTables::of($query)
            ->editColumn('code', function ($row) {
                return '<strong>' . e($row->code) . '</strong>';
            })
            ->editColumn('name', function ($row) {
                return e($row->name);
            })
            ->addColumn('category_name', function ($row) {
                return e($row->category?->name ?? '-');
            })
            ->addColumn('company_name', function ($row) {
                return e($row->company?->name ?? '-');
            })
            ->addColumn('purchase_type_label', function ($row) {
                return e($row->purchase_type?->value ?? $row->purchase_type ?? '-');
            })
            ->addColumn('budget_type_label', function ($row) {
                return $row->budget_type === 'ANNUAL'
                    ? '<span class="badge bg-info">Presupuesto Anual</span>'
                    : '<span class="badge bg-warning">Consumo Libre</span>';
            })
            ->editColumn('status', function ($row) {
                return $row->status === 'ACTIVO'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>';
            })
            ->addColumn('responsible_name', function ($row) {
                return e($row->responsible?->name ?? '-');
            })
            ->addColumn('created_by_name', function ($row) {
                return e($row->createdBy?->name ?? '-');
            })
            ->addColumn('actions', function ($row) {
                $editUrl = route('cost-centers.edit', $row->id);
                $deleteUrl = route('cost-centers.destroy', $row->id);

                return '<div class="d-flex justify-content-end gap-1">'
                    . '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ti ti-pencil"></i></a>'
                    . '<form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-btn" data-entity="' . e($row->name) . '" title="Eliminar"><i class="ti ti-trash"></i></button>'
                    . '</form>'
                    . '</div>';
            })
            ->rawColumns(['code', 'budget_type_label', 'status', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        $costCenter = new CostCenter([
            'status' => 'ACTIVO',
            'budget_type' => 'ANNUAL',
        ]);

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $companies = Company::orderBy('name')
            ->get(['id', 'name']);

        $users = User::orderBy('name')
            ->get(['id', 'name']);

        return view('cost_centers.create', compact(
            'costCenter',
            'categories',
            'companies',
            'users'
        ));
    }

    public function store(SaveCostCenterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = null;

        CostCenter::create($data);

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo creado correctamente.');
    }

    public function edit(CostCenter $cost_center): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $companies = Company::orderBy('name')
            ->get(['id', 'name']);

        $users = User::orderBy('name')
            ->get(['id', 'name']);

        return view('cost_centers.edit', [
            'cost_center' => $cost_center,
            'costCenter' => $cost_center,
            'categories' => $categories,
            'companies' => $companies,
            'users' => $users,
        ]);
    }

    public function update(SaveCostCenterRequest $request, CostCenter $cost_center): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $cost_center->update($data);

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo actualizado correctamente.');
    }

    public function destroy(CostCenter $cost_center): RedirectResponse
    {
        $cost_center->update(['deleted_by' => auth()->id()]);
        $cost_center->delete();

        return redirect()
            ->route('cost-centers.index')
            ->with('success', 'Centro de costo eliminado correctamente.');
    }

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
