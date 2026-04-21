<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewCostCenterImportRequest;
use App\Services\CostCenterImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CostCenterImportController extends Controller
{
    private const PREVIEW_SESSION_KEY = 'cost_center_import.preview';

    public function __construct(
        private readonly CostCenterImportService $importService
    ) {
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return $this->importService->downloadTemplateResponse();
    }

    public function preview(PreviewCostCenterImportRequest $request): RedirectResponse
    {
        $preview = $this->importService->buildPreviewFromUpload(
            $request->file('excel_file'),
            Auth::id()
        );

        session([
            self::PREVIEW_SESSION_KEY => $preview,
        ]);

        return redirect()
            ->route('cost-centers.import.preview.show')
            ->with($preview['can_import']
                ? ['success' => 'Archivo validado correctamente. Revisa el preview antes de confirmar.']
                : ['error' => 'El archivo tiene errores. Corrige el Excel antes de importarlo.']);
    }

    public function showPreview(Request $request): View|RedirectResponse
    {
        $preview = $request->session()->get(self::PREVIEW_SESSION_KEY);

        if (!$preview) {
            return redirect()
                ->route('cost-centers.index')
                ->with('error', 'No hay una importación en revisión. Sube un archivo primero.');
        }

        return view('cost_centers.import_preview', compact('preview'));
    }

    public function confirm(Request $request): RedirectResponse
    {
        $preview = $request->session()->get(self::PREVIEW_SESSION_KEY);

        if (!$preview) {
            return redirect()
                ->route('cost-centers.index')
                ->with('error', 'No hay una importación pendiente por confirmar.');
        }

        $result = $this->importService->confirmPreview($preview, Auth::id());

        if (!$result['success']) {
            session([self::PREVIEW_SESSION_KEY => $result['preview']]);

            return redirect()
                ->route('cost-centers.import.preview.show')
                ->with('error', $result['message']);
        }

        $request->session()->forget(self::PREVIEW_SESSION_KEY);

        return redirect()
            ->route('cost-centers.index')
            ->with('success', "Se importaron {$result['inserted']} centros de costo correctamente.");
    }
}
