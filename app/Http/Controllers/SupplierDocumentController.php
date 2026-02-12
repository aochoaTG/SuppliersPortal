<?php

// app/Http/Controllers/SupplierDocumentController.php
namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupplierFeedbackMail;

class SupplierDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $supplier = Supplier::where('user_id', $user->id)->firstOrFail();

        // Mapa “requeridos” vs “lo que ya subió”
        $docs = $supplier->documents()
            ->latest('uploaded_at')
            ->get()
            ->groupBy('doc_type');

        return view('documents.suppliers.index', [
            'supplier' => $supplier,
            'requiredTypes' => SupplierDocument::REQUIRED_TYPES,
            'docsByType' => $docs,
        ]);
    }

    public function store(Request $request, Supplier $supplier)
    {
        // Determinar el límite según tipo de documento
        $docType = $request->input('doc_type');
        $maxKb   = in_array($docType, ['acta_constitutiva', 'poder_legal'])
            ? 51200   // 50 MB (en KB)
            : 10240;  // 10 MB (en KB)

        // Validación
        $request->validate([
            'doc_type' => ['required', Rule::in(SupplierDocument::REQUIRED_TYPES)],
            'file'     => [
                'required',
                'file',
                "max:$maxKb", // límite dinámico
                'mimes:jpg,jpeg,png,pdf', // ✅ solo JPG/JPEG, PNG y PDF
            ],
        ]);

        // Guardar archivo
        $file = $request->file('file');
        $path = $file->store("suppliers/{$supplier->id}/documents", 'public');

        // Crear registro
        $doc = SupplierDocument::create([
            'supplier_id' => $supplier->id,
            'uploaded_by' => $request->user()->id ?? null,
            'doc_type'    => $docType,
            'path_file'   => $path,
            'size_bytes'  => $file->getSize(),
            'mime_type'   => $file->getClientMimeType(),
            'status'      => 'pending_review',
            'uploaded_at' => now(),
        ]);

        // URL pública del archivo
        $url = Storage::disk('public')->url($doc->path_file);

        return response()->json([
            'id'          => $doc->id,
            'doc_type'    => $doc->doc_type,
            'status'      => $doc->status,
            'uploaded_at' => $doc->uploaded_at?->format('Y-m-d H:i'),
            'url'         => $url,
            'destroy_url' => route('documents.suppliers.destroy', [$supplier, $doc->id]),
        ]);
    }

    public function review(Request $request, Supplier $supplier, SupplierDocument $document)
    {
        $this->authorize('review', $document); // opcional si usas policies

        $request->validate([
            'action' => ['required', Rule::in(['accept', 'reject'])],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->action === 'accept') {
            $document->update([
                'status' => 'accepted',
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id ?? null,
                'reviewed_at' => now(),
            ]);
        } else {
            $document->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason ?: 'Documento no aceptado.',
                'reviewed_by' => $request->user()->id ?? null,
                'reviewed_at' => now(),
            ]);
        }

        return back()->with('success', 'Revisión registrada.');
    }

    // app/Http/Controllers/SupplierDocumentController.php
    public function destroy(Supplier $supplier, $documentId)
    {
        // Buscar el doc SOLO entre los del supplier. Si no existe, 404.
        $document = $supplier->documents()->whereKey($documentId)->firstOrFail();

        // Borrar archivo físico (si existe)
        if ($document->path_file && Storage::disk('public')->exists($document->path_file)) {
            Storage::disk('public')->delete($document->path_file);
        }

        // Borrar registro
        $document->delete();

        return response()->json(['ok' => true]);
    }


    // Métodos para revisión por admin
    public function adminIndex(Request $request)
    {
        $required = SupplierDocument::REQUIRED_TYPES;

        $pendingDocs = SupplierDocument::with(['supplier:id,company_name,rfc', 'uploader:id,name'])
            ->where('status', 'pending_review')
            ->orderByDesc('uploaded_at')
            ->limit(50) // simple
            ->get();

        // Resumen muy simple por proveedor (display)
        $suppliers = Supplier::select('id', 'company_name', 'rfc')->get();
        $suppliersSummary = $suppliers->map(function ($s) use ($required) {
            $docs = $s->documents()->select('status', 'uploaded_at')->get();
            return [
                'supplier'         => $s,
                'total_required'   => count($required),
                'uploaded'         => $docs->pluck('doc_type')->unique()->count(),
                'accepted'         => $docs->where('status', 'accepted')->count(),
                'rejected'         => $docs->where('status', 'rejected')->count(),
                'last_activity_at' => optional($docs->max('uploaded_at'))?->toDateTimeString(),
            ];
        });

        return view('documents.admin.index', [
            'pendingDocs'     => $pendingDocs,
            'suppliersSummary' => $suppliersSummary,
            'requiredTypes'   => $required,
        ]);
    }

    public function feedback(Request $request)
    {
        $data = $request->validate([
            'type'     => ['required', 'string', 'max:100'],
            'doc_id'   => ['nullable', 'integer'],
            'feedback' => ['required', 'string', 'min:5'],
            'supplier' => ['nullable', 'integer'],
        ]);

        // Resolver proveedor
        $supplier = null;
        $doc = null;

        if (!empty($data['doc_id'])) {
            $doc = SupplierDocument::with('supplier')->find($data['doc_id']);
            $supplier = $doc?->supplier;
        }
        if (!$supplier && $request->filled('supplier')) {
            $supplier = Supplier::find($request->input('supplier'));
        }
        if (!$supplier) {
            return response()->json(['message' => 'Proveedor no encontrado.'], 404);
        }

        $toEmail = $supplier->email ?? null;
        if (!$toEmail) {
            return response()->json(['message' => 'El proveedor no tiene correo configurado.'], 422);
        }

        // Usuario que manda la retro
        $sender = $request->user();

        // ⚠️ Instancia POSICIONAL (no named args)
        $mailable = new SupplierFeedbackMail(
            $supplier,             // supplier
            $data['type'],         // type
            $data['feedback'],     // feedback
            $sender,               // sender
            $doc                   // document (puede ser null)
        );

        // Reply-To al revisor (por si no lo pusiste en el Mailable)
        if ($sender && filled($sender->email)) {
            $mailable->replyTo($sender->email, $sender->name ?? null);
        }

        // Envío con CC al revisor y (opcional) auditoría
        $mail = Mail::to($toEmail);

        if ($sender && filled($sender->email) && strcasecmp($sender->email, $toEmail) !== 0) {
            $mail->cc($sender->email);
        }
        if (filled(config('mail.feedback_cc'))) {
            $mail->cc(config('mail.feedback_cc'));
        }

        $mail->send($mailable);

        return response()->json(['ok' => true]);
    }
}
