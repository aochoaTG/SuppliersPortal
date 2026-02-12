<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLevel;
use Illuminate\Http\Request;
use App\Services\ApprovalService;

class ApprovalLevelController extends Controller
{
    /**
     * Muestra el listado de rangos de autorización.
     */
    public function index()
    {
        $levels = ApprovalLevel::orderBy('level_number', 'asc')->get();
        return view('approval_levels.index', compact('levels'));
    }

    /**
     * Muestra el formulario para editar un rango específico.
     */
    public function edit(ApprovalLevel $approvalLevel)
    {
        $approvalService = new ApprovalService();
        $approvalService->clearCache();
        return view('approval_levels.edit', compact('approvalLevel'));
    }

    /**
     * Actualiza los montos y etiquetas en SQL Server.
     */
    public function update(Request $request, ApprovalLevel $approvalLevel)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'color_tag' => 'required|string',
        ]);

        $approvalLevel->update($request->all());

        $approvalService = new ApprovalService();
        $approvalService->clearCache();

        return redirect()->route('approval-levels.index')
            ->with('success', "Nivel {$approvalLevel->level_number} actualizado correctamente.");
    }
}
