<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\Supplier;
use App\Models\SupplierRfq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupplierPortalController extends Controller
{
    /**
     * Dashboard del proveedor - Lista de RFQs asignadas
     * 
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Obtener el proveedor del usuario autenticado
        $supplier = Auth::user()->supplier;

        if (!$supplier) {
            abort(403, 'No tienes un perfil de proveedor asociado');
        }

        // Obtener RFQs donde este proveedor está invitado
        $rfqs = Rfq::whereHas('suppliers', function ($query) use ($supplier) {
            $query->where('supplier_id', $supplier->id);
        })
            ->with([
                'requisition',
                'quotationGroup.items',
                'suppliers',
                'rfqResponses' => function ($query) use ($supplier) {
                    $query->where('supplier_id', $supplier->id);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Contar respuestas por estado
        $stats = [
            'pending' => $rfqs->where('status', 'SENT')->count(),
            'draft' => RfqResponse::where('supplier_id', $supplier->id)
                ->where('status', 'DRAFT')
                ->count(),
            'submitted' => RfqResponse::where('supplier_id', $supplier->id)
                ->where('status', 'SUBMITTED')
                ->count(),
            'approved' => RfqResponse::where('supplier_id', $supplier->id)
                ->where('status', 'APPROVED')
                ->count(),
        ];

        return view('supplier.dashboard', compact('rfqs', 'supplier', 'stats'));
    }

    /**
     * Ver detalle de una RFQ específica
     * 
     * @param Rfq $rfq
     * @return \Illuminate\View\View
     */
    public function showRfq(Rfq $rfq)
    {
        $supplier = Auth::user()->supplier;

        // Verificar que el proveedor esté invitado a esta RFQ
        if (!$rfq->suppliers->contains($supplier->id)) {
            abort(403, 'No tienes acceso a esta RFQ');
        }

        // Cargar relaciones
        $rfq->load([
            'requisition',
            'quotationGroup.items.expenseCategory',
            'requisitionItem',
        ]);

        // Obtener respuestas existentes de este proveedor
        $responses = RfqResponse::where('rfq_id', $rfq->id)
            ->where('supplier_id', $supplier->id)
            ->with('requisitionItem')
            ->get()
            ->keyBy('requisition_item_id');

        // Obtener partidas a cotizar
        if ($rfq->quotation_group_id) {
            $items = $rfq->quotationGroup->items;
        } elseif ($rfq->requisition_item_id) {
            $items = collect([$rfq->requisitionItem]);
        } else {
            $items = collect([]);
        }

        return view('supplier.rfq-detail', compact('rfq', 'supplier', 'items', 'responses'));
    }

    /**
     * Guardar cotización (borrador o envío final)
     * 
     * Esta función maneja AMBAS acciones:
     * 1. Guardar como BORRADOR (sin enviar)
     * 2. Enviar cotización FINAL (ya no se puede editar)
     * 
     * La diferencia está en el campo 'action' que viene del formulario:
     * - action='save_draft' → Guarda como BORRADOR (status='DRAFT', submitted_at=null)
     * - action='submit' → Envía cotización (status='SUBMITTED', submitted_at=ahora)
     * 
     * @param Request $request - Datos del formulario
     * @param Rfq $rfq - RFQ a la que se está respondiendo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveQuotation(Request $request, Rfq $rfq)
    {
        // =========================================================================
        // PASO 1: VERIFICAR ACCESO DEL PROVEEDOR
        // =========================================================================
        $supplier = Auth::user()->supplier;

        if (!$rfq->suppliers->contains($supplier->id)) {
            abort(403, 'No tienes acceso a esta RFQ');
        }

        // =========================================================================
        // PASO 2: VALIDAR DATOS DEL FORMULARIO
        // =========================================================================
        $validated = $request->validate([
            // Cambiamos a nullable porque si todas las partidas están bloqueadas (disabled), 
            // el array 'items' no se enviará en el POST.
            'items' => 'nullable|array',

            // Reglas para las partidas que SÍ vienen en el request
            'items.*.item_id' => 'required|exists:requisition_items,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.iva_rate' => 'required|numeric|in:0,8,16',
            'items.*.currency' => 'nullable|string|in:MXN,USD,EUR',

            // Campos opcionales
            'items.*.delivery_days' => 'nullable|integer|min:0',
            'items.*.payment_terms' => 'nullable|string|max:255',
            'items.*.warranty_terms' => 'nullable|string|max:500',
            'items.*.brand' => 'nullable|string|max:100',
            'items.*.model' => 'nullable|string|max:100',
            'items.*.specifications' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'items.*.attachment' => 'nullable|file|mimes:pdf|max:5120',

            // Datos generales
            'supplier_quotation_number' => 'nullable|string|max:100',
            'validity_days' => 'nullable|integer|min:1|max:365',

            // 📄 PDF de cotización global del proveedor
            'quotation_pdf_file' => 'nullable|file|mimes:pdf|max:5120', // 5MB

            'action' => 'required|in:save_draft,submit',
        ]);

        // Si no hay nada que procesar y es un envío, avisamos al usuario
        if (empty($validated['items']) && $validated['action'] === 'submit') {
            return back()->with('error', 'No hay partidas nuevas o editables para enviar.');
        }

        // =========================================================================
        // PASO 3: GUARDAR/ACTUALIZAR CADA PARTIDA
        // =========================================================================
        DB::beginTransaction();
        try {
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $itemId = $itemData['item_id'];

                    // -------------------------------------------------------------
                    // 🛡️ FILTRO DE SEGURIDAD: Verificar estatus actual en BD
                    // -------------------------------------------------------------
                    $existingResponse = RfqResponse::where([
                        'rfq_id' => $rfq->id,
                        'supplier_id' => $supplier->id,
                        'requisition_item_id' => $itemId,
                    ])->first();

                    // Si ya existe y NO es borrador, saltamos esta partida (está bloqueada)
                    if ($existingResponse && $existingResponse->status !== 'DRAFT') {
                        continue;
                    }

                    // -------------------------------------------------------------
                    // CÁLCULOS FINANCIEROS
                    // -------------------------------------------------------------
                    $unitPrice = $itemData['unit_price'];
                    $quantity = $itemData['quantity'];
                    $ivaRate = $itemData['iva_rate'];

                    $subtotal = $unitPrice * $quantity;
                    $ivaAmount = $subtotal * ($ivaRate / 100);
                    $total = $subtotal + $ivaAmount;

                    // -------------------------------------------------------------
                    // GUARDAR O ACTUALIZAR (Solo para partidas DRAFT o nuevas)
                    // -------------------------------------------------------------
                    $response = RfqResponse::updateOrCreate(
                        [
                            'rfq_id' => $rfq->id,
                            'supplier_id' => $supplier->id,
                            'requisition_item_id' => $itemId,
                        ],
                        [
                            'unit_price' => $unitPrice,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'iva_rate' => $ivaRate,
                            'iva_amount' => $ivaAmount,
                            'total' => $total,
                            'currency' => $itemData['currency'] ?? 'MXN',
                            'delivery_days' => $itemData['delivery_days'] ?? null,
                            'payment_terms' => $itemData['payment_terms'] ?? null,
                            'warranty_terms' => $itemData['warranty_terms'] ?? null,
                            'brand' => $itemData['brand'] ?? null,
                            'model' => $itemData['model'] ?? null,
                            'specifications' => $itemData['specifications'] ?? null,
                            'notes' => $itemData['notes'] ?? null,
                            'supplier_quotation_number' => $validated['supplier_quotation_number'] ?? null,
                            'validity_days' => $validated['validity_days'] ?? 30,

                            // 🎯 Actualizar estatus solo si se presionó enviar
                            'status' => $validated['action'] === 'submit' ? 'SUBMITTED' : 'DRAFT',
                            'submitted_at' => $validated['action'] === 'submit' ? now() : null,
                            'quotation_date' => $validated['action'] === 'submit' ? now() : null,
                        ]
                    );

                    // Manejo de adjuntos por partida
                    if (isset($itemData['attachment'])) {
                        if ($response->attachment_path && Storage::disk('public')->exists($response->attachment_path)) {
                            Storage::disk('public')->delete($response->attachment_path);
                        }
                        $path = $itemData['attachment']->store("suppliers/{$supplier->id}/quotations", 'public');
                        $response->update(['attachment_path' => $path]);
                    }
                }
            }

            // =========================================================================
            // 📄 PASO 3.5: MANEJAR PDF GLOBAL DE COTIZACIÓN
            // =========================================================================

            // Obtener el pivote
            $pivot = DB::table('rfq_suppliers')
                ->where('rfq_id', $rfq->id)
                ->where('supplier_id', $supplier->id)
                ->first();

            if ($pivot) {
                // Verificar si se marcó para eliminación
                if ($request->input('delete_pdf_flag') == '1') {
                    if ($pivot->quotation_pdf_path && Storage::disk('public')->exists($pivot->quotation_pdf_path)) {
                        Storage::disk('public')->delete($pivot->quotation_pdf_path);
                    }

                    DB::table('rfq_suppliers')
                        ->where('rfq_id', $rfq->id)
                        ->where('supplier_id', $supplier->id)
                        ->update([
                            'quotation_pdf_path' => null,
                            'updated_at' => now()
                        ]);

                    Log::info("PDF de cotización eliminado para Proveedor {$supplier->id} en RFQ {$rfq->id}");
                }
                // Si hay un nuevo archivo
                elseif ($request->hasFile('quotation_pdf_file')) {
                    // Eliminar PDF anterior si existe
                    if ($pivot->quotation_pdf_path && Storage::disk('public')->exists($pivot->quotation_pdf_path)) {
                        Storage::disk('public')->delete($pivot->quotation_pdf_path);
                    }

                    // Guardar nuevo PDF
                    $pdfPath = $request->file('quotation_pdf_file')->store(
                        "suppliers/{$supplier->id}/rfq_{$rfq->id}",
                        'public'
                    );

                    DB::table('rfq_suppliers')
                        ->where('rfq_id', $rfq->id)
                        ->where('supplier_id', $supplier->id)
                        ->update([
                            'quotation_pdf_path' => $pdfPath,
                            'updated_at' => now()
                        ]);

                    Log::info("PDF de cotización guardado para Proveedor {$supplier->id} en RFQ {$rfq->id}: {$pdfPath}");
                }
            }

            // =========================================================================
            // 🎯 PASO 4: ACTUALIZAR EL PIVOTE rfq_suppliers
            // =========================================================================
            if ($validated['action'] === 'submit') {
                // Sincronizamos la fecha de respuesta en la tabla pivote
                // Esto es vital para saber quién ya cumplió y quién no
                $rfq->suppliers()->updateExistingPivot($supplier->id, [
                    'responded_at' => now(),
                    'updated_at' => now()
                ]);

                // =========================================================================
                // 🎯 PASO 5: VERIFICAR SI LA RFQ ESTÁ COMPLETADA POR TODOS
                // =========================================================================

                // Contamos cuántos proveedores fueron invitados
                $totalInvited = $rfq->suppliers()->count();

                // Contamos cuántos han respondido (tienen responded_at)
                $totalResponded = $rfq->suppliers()->whereNotNull('responded_at')->count();

                // Si el conteo coincide, la RFQ cambia de estado global
                if ($totalInvited > 0 && $totalResponded >= $totalInvited) {
                    $rfq->update([
                        'status' => 'RECEIVED', // O 'COMPLETED_BY_SUPPLIERS' según tu nomenclatura
                        'updated_at' => now()
                    ]);
                    Log::info("RFQ Folio {$rfq->folio}: Todos los proveedores respondieron. Estado actualizado a RECEIVED.");
                } else {
                    Log::info("RFQ Folio {$rfq->folio}: Respuesta recibida de Proveedor ID {$supplier->id}. ({$totalResponded}/{$totalInvited})");
                }
            }

            DB::commit();

            $message = $validated['action'] === 'submit'
                ? '<i class="ti ti-circle-check-filled text-success me-2"></i>La cotización de las partidas pendientes ha sido enviada.'
                : '<i class="ti ti-device-floppy text-info me-2"></i>Cambios guardados en borrador correctamente.';

            return redirect()
                ->route('supplier.rfq.show', $rfq)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en saveQuotation', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Error en la operación: ' . $e->getMessage());
        }
    }

    /**
     * Ver historial de cotizaciones del proveedor
     * 
     * @return \Illuminate\View\View
     */
    public function quotationHistory()
    {
        $supplier = Auth::user()->supplier;

        $responses = RfqResponse::where('supplier_id', $supplier->id)
            ->with([
                'rfq.requisition',
                'requisitionItem',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supplier.quotation-history', compact('responses', 'supplier'));
    }

    /**
     * Descargar archivo adjunto de cotización
     * 
     * @param RfqResponse $response
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadAttachment(RfqResponse $response)
    {
        $supplier = Auth::user()->supplier;

        // Verificar que la respuesta pertenezca al proveedor
        if ($response->supplier_id !== $supplier->id) {
            abort(403, 'No tienes acceso a este archivo');
        }

        if (!$response->attachment_path || !Storage::disk('public')->exists($response->attachment_path)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::disk('public')->download($response->attachment_path);
    }

    /**
     * Eliminar borrador de cotización
     * 
     * @param RfqResponse $response
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteDraft(RfqResponse $response)
    {
        $supplier = Auth::user()->supplier;

        // Verificar acceso y que sea borrador
        if ($response->supplier_id !== $supplier->id) {
            abort(403, 'No tienes acceso a esta cotización');
        }

        if ($response->status !== 'DRAFT') {
            return back()->with('error', 'Solo puedes eliminar borradores');
        }

        // ✅ Eliminar archivo adjunto si existe (usando el helper del modelo)
        if ($response->attachment_path) {
            Storage::disk('public')->delete($response->attachment_path);
        }

        $response->delete();

        return back()->with('success', 'Borrador eliminado correctamente');
    }
}
