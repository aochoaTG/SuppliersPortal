<?php

namespace App\Http\Controllers;

use App\Models\CatSupplier;
use Illuminate\Http\Request;

class CatSupplierController extends Controller
{
    public function index()
    {
        $suppliers = CatSupplier::orderBy('id', 'desc')->paginate(20);

        return view('cat_suppliers.index', compact('suppliers'));
    }

    public function edit(CatSupplier $catSupplier)
    {
        return view('cat_suppliers.edit', compact('catSupplier'));
    }

    public function update(Request $request, CatSupplier $catSupplier)
    {
        $data = $request->validate([
            'name'           => 'nullable|string|max:200',
            'rfc'            => 'nullable|string|max:13',
            'email'          => 'nullable|email|max:254',
            'phone'          => 'nullable|string|max:50', // si luego agregas teléfono
            'bank'           => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'clabe'          => 'nullable|string|max:18',
            'payment_method' => 'nullable|string|max:50',
            'currency'       => 'nullable|string|max:10',
            'category'       => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'active'         => 'boolean',
        ]);

        $catSupplier->update($data);

        return redirect()
            ->route('cat-suppliers.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function datatable()
    {
        // Trae lo necesario; puedes quitar orderBy si prefieres ordenar en el cliente
        $rows = CatSupplier::select([
            'id',
            'source_system',
            'source_company',
            'name',
            'rfc',
            'postal_code',
            'email',
            'bank',
            'account_number',
            'clabe',
            'currency',
        ])->orderBy('rfc')->get();

        return response()->json(['data' => $rows]);
    }
}
