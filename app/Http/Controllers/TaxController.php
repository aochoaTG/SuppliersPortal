<?php

// app/Http/Controllers/TaxController.php
namespace App\Http\Controllers;

use App\Models\Tax;
use App\Http\Requests\TaxRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TaxController extends Controller
{
    public function index(): View
    {
        return view('taxes.index');
    }

    public function datatable()
    {
        $query = Tax::query();

        return DataTables::of($query)
            ->editColumn('rate_percent', fn($row) => number_format($row->rate_percent, 2))
            ->editColumn('is_active', fn($row) => $row->is_active
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>')
            ->addColumn('actions', function ($row) {
                $editUrl   = route('taxes.edit', $row->id);
                $deleteUrl = route('taxes.destroy', $row->id);

                return '
                <div class="d-flex justify-content-end gap-1">
                    <a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="ti ti-edit"></i>
                    </a>
                    <form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                                data-entity="' . e($row->name) . '" title="Eliminar">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </div>';
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        $tax = new Tax();
        return view('taxes.create', compact('tax'));
    }

    public function store(TaxRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        Tax::create($data);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Impuesto creado correctamente.');
    }

    public function edit(Tax $tax): View
    {
        return view('taxes.edit', compact('tax'));
    }

    public function update(TaxRequest $request, Tax $tax): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $tax->update($data);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Impuesto actualizado correctamente.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $tax->delete();

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Impuesto eliminado correctamente.');
    }
}
