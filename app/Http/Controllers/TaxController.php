<?php

// app/Http/Controllers/TaxController.php
namespace App\Http\Controllers;

use App\Models\Tax;
use App\Http\Requests\TaxRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(): View
    {
        $taxes = Tax::orderBy('name')->paginate(10); // simple y paginado
        return view('taxes.index', compact('taxes'));
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
