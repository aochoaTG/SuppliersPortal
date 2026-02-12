<?php

namespace App\Http\Controllers;

use App\Models\SatEfos69b;
use Illuminate\Http\Request;

class SatEfos69bController extends Controller
{
    public function index()
    {
        return view('sat_efos_69b.index');
    }

    public function data()
    {
        // Trae lo necesario; puedes quitar orderBy si prefieres ordenar en el cliente
        $rows = SatEfos69b::select([
            'number',
            'rfc',
            'company_name',
            'situation',
            'sat_presumption_notice_date',
            'dof_presumption_notice_date',
            'sat_definitive_publication_date',
            'dof_definitive_publication_date',
        ])->orderBy('rfc')->get();

        return response()->json(['data' => $rows]);
    }

    public function create()
    {
        // return view('sat_efos_69b.create');
        return response()->json(['message' => 'Formulario create pendiente'], 200);
    }

    public function store(Request $request)
    {
        // Pendiente: validación/creación
        return response()->json(['message' => 'Store pendiente'], 501);
    }

    public function show(SatEfos69b $satEfos69b)
    {
        // return view('sat_efos_69b.show', compact('satEfos69b'));
        return response()->json(['message' => 'Show pendiente'], 200);
    }

    public function edit(SatEfos69b $satEfos69b)
    {
        // return view('sat_efos_69b.edit', compact('satEfos69b'));
        return response()->json(['message' => 'Edit pendiente'], 200);
    }

    public function update(Request $request, SatEfos69b $satEfos69b)
    {
        // Pendiente: validación/actualización
        return response()->json(['message' => 'Update pendiente'], 501);
    }

    public function destroy(SatEfos69b $satEfos69b)
    {
        // Pendiente: eliminación
        return response()->json(['message' => 'Destroy pendiente'], 501);
    }
}
