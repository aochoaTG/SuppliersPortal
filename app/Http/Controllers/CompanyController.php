<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    /**
     * Mostrar la vista principal de empresas.
     */
    public function index()
    {
        return view('companies.index');
    }

    /**
     * Endpoint AJAX para DataTable.
     */
    public function datatable(Request $request)
    {
        $user = $request->user();

        // Empresas visibles = todas las empresas
        $query = Company::all();

        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
                return view('companies.partials.actions', compact('row'))->render();
            })
            ->editColumn('is_active', fn($row) => $row->is_active ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>')
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        // Instancia vacía con algunos defaults razonables
        $company = new Company([
            'locale' => app()->getLocale() ?? 'es_MX',
            'timezone' => config('app.timezone', 'America/Mexico_City'),
            'currency_code' => 'MXN',
            'is_active' => true,
        ]);

        return view('companies.partials.form', compact('company'));
    }

    /**
     * Guardar una nueva empresa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:companies,code',
            'name' => 'required|string|max:150',
            'legal_name' => 'nullable|string|max:200',
            'rfc' => 'nullable|string|max:13',
            'locale' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'currency_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'domain' => 'nullable|string|max:150',
            'website' => 'nullable|string|max:150',
            'logo_path' => 'nullable|string|max:255',
            'is_active' => 'required|boolean', // con el hidden siempre viene
        ]);

        // Normalizar a boolean real
        $validated['is_active'] = $request->boolean('is_active');

        Company::create($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Company $company)
    {
        // Devuelve solo el HTML del formulario (partial)
        return view('companies.partials.form', compact('company'));
    }

    /**
     * Actualizar una empresa existente.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:companies,code,' . $company->id,
            'name' => 'required|string|max:150',
            'legal_name' => 'nullable|string|max:200',
            'rfc' => 'nullable|string|max:13',
            'locale' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'currency_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'domain' => 'nullable|string|max:150',
            'website' => 'nullable|string|max:150',
            'logo_path' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $company->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar empresa.
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(['success' => true]);
    }
}
