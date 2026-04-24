<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSatRetencionRequest;
use App\Models\SatRetencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SatRetencionController extends Controller
{
    public function index(): View
    {
        return view('sat_retenciones.index');
    }

    public function create(): View
    {
        $sat_retencion = new SatRetencion([
            'activo'                  => true,
            'requiere_cfdi_retencion' => true,
        ]);

        return view('sat_retenciones.create', compact('sat_retencion'));
    }

    public function store(SaveSatRetencionRequest $request): RedirectResponse
    {
        SatRetencion::create($request->validated());

        return redirect()
            ->route('sat-retenciones.index')
            ->with('success', 'Retención creada correctamente.');
    }

    public function edit(SatRetencion $sat_retencion): View
    {
        return view('sat_retenciones.edit', compact('sat_retencion'));
    }

    public function update(SaveSatRetencionRequest $request, SatRetencion $sat_retencion): RedirectResponse
    {
        $sat_retencion->update($request->validated());

        return redirect()
            ->route('sat-retenciones.index')
            ->with('success', 'Retención actualizada correctamente.');
    }

    public function destroy(SatRetencion $sat_retencion): RedirectResponse
    {
        $sat_retencion->delete();

        return redirect()
            ->route('sat-retenciones.index')
            ->with('success', 'Retención eliminada correctamente.');
    }

    public function datatable()
    {
        $query = SatRetencion::query();

        return DataTables::of($query)
            ->editColumn('impuesto', function ($row) {
                return $row->impuesto === 'ISR'
                    ? '<span class="badge bg-primary">ISR</span>'
                    : '<span class="badge bg-warning text-dark">IVA</span>';
            })
            ->editColumn('requiere_cfdi_retencion', function ($row) {
                return $row->requiere_cfdi_retencion
                    ? '<span class="badge bg-success">Sí</span>'
                    : '<span class="badge bg-secondary">No</span>';
            })
            ->editColumn('activo', function ($row) {
                return $row->activo
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>';
            })
            ->addColumn('actions', function ($row) {
                $editUrl   = route('sat-retenciones.edit', $row->id);
                $deleteUrl = route('sat-retenciones.destroy', $row->id);
                $entity    = e($row->clave . ' — ' . $row->nombre);

                return '<div class="d-flex justify-content-end gap-1">'
                    . '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ti ti-pencil"></i></a>'
                    . '<form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-btn" data-entity="' . $entity . '" title="Eliminar"><i class="ti ti-trash"></i></button>'
                    . '</form>'
                    . '</div>';
            })
            ->rawColumns(['impuesto', 'requiere_cfdi_retencion', 'activo', 'actions'])
            ->make(true);
    }
}
