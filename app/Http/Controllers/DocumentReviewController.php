<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class DocumentReviewController extends Controller
{
    /**
     * Vista unificada con tabs: Bandeja (documentos) y Proveedores.
     * Solo display. Sin acciones.
     */
    public function index()
    {
        $requiredTypes = SupplierDocument::REQUIRED_TYPES;

        // Bandeja: solo pendientes (para mostrar)
        $pendingDocs = SupplierDocument::with(['supplier:id,company_name,rfc', 'uploader:id,name'])
            ->where('status', 'pending_review')
            ->orderByDesc('uploaded_at')
            ->limit(50)
            ->get();

        // KPIs (consultas independientes)
        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        $kpiPendientes     = SupplierDocument::where('status','pending_review')->count();
        $kpiAprobadosHoy   = SupplierDocument::where('status','accepted')
                                ->whereBetween('reviewed_at', [$start, $end])
                                ->count();
        $kpiRechazadosHoy  = SupplierDocument::where('status','rejected')
                                ->whereBetween('reviewed_at', [$start, $end])
                                ->count();

        // (Si quieres solo los revisados por el admin actual, añade ->where('reviewed_by', auth()->id()))

        // Resumen por proveedor (display simple)
        $suppliers = Supplier::select('id','company_name','rfc')->get();
        $suppliersSummary = $suppliers->map(function ($s) use ($requiredTypes) {
            $docs = $s->documents()->select('doc_type','status','uploaded_at')->get();
            return [
                'supplier'         => $s,
                'total_required'   => count($requiredTypes),
                'uploaded'         => $docs->pluck('doc_type')->unique()->count(),
                'accepted'         => $docs->where('status','accepted')->count(),
                'rejected'         => $docs->where('status','rejected')->count(),
                'last_activity_at' => optional($docs->max('uploaded_at'))?->toDateTimeString(),
            ];
        });

        return view('documents.admin.index', [
            'pendingDocs'       => $pendingDocs,
            'suppliersSummary'  => $suppliersSummary,
            'requiredTypes'     => $requiredTypes,
            'kpiPendientes'     => $kpiPendientes,
            'kpiAprobadosHoy'   => $kpiAprobadosHoy,
            'kpiRechazadosHoy'  => $kpiRechazadosHoy,
        ]);
    }

    /**
     * Ficha simple por proveedor (solo display).
     */
    public function showSupplier(Supplier $supplier)
    {
        // Documentos agrupados por tipo (simple)
        $docs = $supplier->documents()
            ->orderByDesc('uploaded_at')
            ->get()
            ->groupBy('doc_type');

        $requiredTypes = SupplierDocument::REQUIRED_TYPES;

        return view('documents.admin.show_supplier', [
            'supplier'     => $supplier,
            'docsByType'   => $docs,
            'requiredTypes'=> $requiredTypes,
        ]);
    }

    public function accept(Request $request, SupplierDocument $document)
    {
        DB::transaction(function () use ($request, $document) {
            $document->update([
                'status'       => 'accepted',
                'rejection_reason' => null,
                'reviewed_by'  => $request->user()->id,
                'reviewed_at'  => now(),
            ]);
        });

        // Respuesta JSON para AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'ok'          => true,
                'id'          => $document->id,
                'new_status'  => $document->status,
                'reviewed_by' => $request->user()->name ?? '—',
                'reviewed_at' => optional($document->reviewed_at)->format('Y-m-d H:i'),
            ]);
        }

        // Fallback a navegación tradicional
        return back()->with('success', 'Documento aprobado correctamente.');
    }

    public function reject(Request $request, SupplierDocument $document)
    {
        $data = $request->validate([
            'reason' => ['required','string','min:5','max:2000'],
        ], [], ['reason' => 'motivo de rechazo']);

        DB::transaction(function () use ($request, $document, $data) {
            $document->update([
                'status'           => 'rejected',
                'rejection_reason' => $data['reason'],
                'reviewed_by'      => $request->user()->id,
                'reviewed_at'      => now(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'ok'               => true,
                'id'               => $document->id,
                'new_status'       => $document->status,
                'rejection_reason' => $document->rejection_reason,
                'reviewed_by'      => $request->user()->name ?? '—',
                'reviewed_at'      => optional($document->reviewed_at)->format('Y-m-d H:i'),
            ]);
        }

        return back()->with('success', 'Documento rechazado correctamente.');
    }
}
