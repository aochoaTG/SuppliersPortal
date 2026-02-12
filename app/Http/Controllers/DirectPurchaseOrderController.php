<?php

namespace App\Http\Controllers;

use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use App\Models\DirectPurchaseOrderDocument;
use App\Models\BudgetCommitment;
use App\Models\Supplier;
use App\Models\CostCenter;
use App\Models\ExpenseCategory;
use App\Http\Requests\SaveDirectPurchaseOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DirectPurchaseOrderController extends Controller
{
    /**
     * Mostrar formulario para crear nueva OCD
     */
    public function create()
    {
        // Obtener empresas a las que el usuario tiene acceso
        $companies = Auth::user()->companies()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Obtener centros de costo a los que el usuario tiene acceso (con relación a empresa)
        $costCenters = Auth::user()->costCenters()
            ->with('company')
            ->whereHas('company', function ($query) {
                $query->where('is_active', true);
            })
            ->where('cost_center_user.is_active', true)
            ->orderBy('name')
            ->get();

        // Proveedores activos
        $suppliers = Supplier::active()
            ->orderBy('company_name')
            ->get();

        // Categorías de gasto activas
        $expenseCategories = ExpenseCategory::active()
            ->orderBy('name')
            ->get();

        // Mes actual en formato YYYY-MM
        $currentMonth = now()->format('Y-m');

        return view('direct-purchase-orders.create', compact(
            'companies',
            'costCenters',
            'suppliers',
            'expenseCategories',
            'currentMonth'
        ));
    }

    /**
     * Guardar nueva OCD
     */
    public function store(SaveDirectPurchaseOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // 1. Calcular totales
            $totals = $this->calculateTotals($request->items);
            
            // 2. Validar presupuesto disponible
            $budgetValidation = $this->validateBudgetAvailability(
                $request->cost_center_id,
                $request->application_month,
                $request->expense_category_id,
                $totals['total']
            );
            
            if (!$budgetValidation['available']) {
                return back()
                ->withInput()
                ->withErrors(['budget' => $budgetValidation['message']]);
            }

            // 3. Obtener datos del proveedor para heredar condiciones
            $supplier = Supplier::active()->find($request->supplier_id);

            // 4. Crear la OCD
            $ocd = DirectPurchaseOrder::create([
                'folio' => null, // Se genera al enviar a aprobación
                'supplier_id' => $request->supplier_id,
                'cost_center_id' => $request->cost_center_id,
                'expense_category_id' => $request->expense_category_id,
                'application_month' => $request->application_month,
                'justification' => $request->justification,
                'subtotal' => $totals['subtotal'],
                'iva_amount' => $totals['iva'],
                'total' => $totals['total'],
                'currency' => 'MXN',
                'payment_terms' => $request->payment_terms ?? $supplier->default_payment_terms ?? 'Contado',
                'estimated_delivery_days' => $request->estimated_delivery_days ?? $supplier->avg_delivery_time ?? 30,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            // 5. Crear los items
            foreach ($request->items as $itemData) {
                DirectPurchaseOrderItem::create([
                    'direct_purchase_order_id' => $ocd->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    // Los montos se calculan automáticamente en el modelo
                ]);
            }

            // 6. Guardar documentos
            // Cotización (obligatoria)
            if ($request->hasFile('quotation_file')) {
                $this->uploadDocument($ocd, $request->file('quotation_file'), 'quotation');
            }

            // Documentos de soporte (opcionales)
            if ($request->hasFile('support_documents')) {
                foreach ($request->file('support_documents') as $file) {
                    $this->uploadDocument($ocd, $file, 'support_document');
                }
            }

            DB::commit();

            return redirect()
                ->route('purchase-orders.index')
                ->with('success', 'Orden de Compra Directa creada exitosamente. Ahora puede enviarla a aprobación.');
        } catch (\Exception $e) {
            dd("Entré 3");
            die();  
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear la OCD: ' . $e->getMessage()]);
        }
    }

    /**
     * Envía la OCD a aprobación
     */
    public function submit(DirectPurchaseOrder $directPurchaseOrder)
    {
        // 1. Verificar permisos y estado
        if ($directPurchaseOrder->created_by !== Auth::id()) {
            return back()->withErrors(['error' => 'Solo el creador puede enviar la OCD a aprobación.']);
        }

        if (!$directPurchaseOrder->isDraft() && !$directPurchaseOrder->isReturned()) {
            return back()->withErrors(['error' => 'Solo se pueden enviar OCD en estado Borrador o Devueltas.']);
        }

        // 2. Ejecutar envío (esto genera folio y nivel de aprobación en el modelo)
        if ($directPurchaseOrder->submit()) {
            return redirect()
                ->route('direct-purchase-orders.show', $directPurchaseOrder->id)
                ->with('success', 'OCD enviada a aprobación exitosamente bajo el folio: ' . $directPurchaseOrder->folio);
        }

        return back()->withErrors(['error' => 'No se pudo enviar la OCD. Asegúrese de que tenga al menos un item.']);
    }

    /**
     * Acción de Aprobar OCD
     */
    public function approve(Request $request, DirectPurchaseOrder $directPurchaseOrder)
    {
        try {
            DB::beginTransaction();

            // En este sistema simplificado para OCD, el primer aprobador es el final
            // TODO: Si se requieren múltiples niveles, implementar lógica de tránsito aquí
            
            $directPurchaseOrder->update([
                'status' => 'APPROVED',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Registrar en el historial
            $directPurchaseOrder->approvals()->create([
                'approval_level' => $directPurchaseOrder->required_approval_level,
                'approver_user_id' => Auth::id(),
                'action' => 'APPROVED',
                'comments' => $request->comments,
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('direct-purchase-orders.show', $directPurchaseOrder->id)
                ->with('success', 'OCD aprobada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al aprobar la OCD: ' . $e->getMessage()]);
        }
    }

    /**
     * Acción de Rechazar OCD
     */
    public function reject(Request $request, DirectPurchaseOrder $directPurchaseOrder)
    {
        $request->validate(['comments' => 'required|string|max:500']);

        try {
            DB::beginTransaction();

            $directPurchaseOrder->update([
                'status' => 'REJECTED',
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
            ]);

            $directPurchaseOrder->approvals()->create([
                'approval_level' => $directPurchaseOrder->required_approval_level,
                'approver_user_id' => Auth::id(),
                'action' => 'REJECTED',
                'comments' => $request->comments,
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('direct-purchase-orders.show', $directPurchaseOrder->id)
                ->with('warning', 'OCD rechazada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al rechazar la OCD: ' . $e->getMessage()]);
        }
    }

    /**
     * Acción de Devolver OCD para corrección
     */
    public function return(Request $request, DirectPurchaseOrder $directPurchaseOrder)
    {
        $request->validate(['comments' => 'required|string|max:500']);

        try {
            DB::beginTransaction();

            $directPurchaseOrder->update([
                'status' => 'RETURNED',
                'returned_by' => Auth::id(),
                'returned_at' => now(),
            ]);

            $directPurchaseOrder->approvals()->create([
                'approval_level' => $directPurchaseOrder->required_approval_level,
                'approver_user_id' => Auth::id(),
                'action' => 'RETURNED',
                'comments' => $request->comments,
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('direct-purchase-orders.show', $directPurchaseOrder->id)
                ->with('info', 'OCD devuelta al creador para corrección.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al devolver la OCD: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar formulario para editar OCD existente
     */
    public function edit(DirectPurchaseOrder $directPurchaseOrder)
    {
        // Verificar que se puede editar
        if (!$directPurchaseOrder->canBeEdited()) {
            return redirect()
                ->route('purchase-orders.index')
                ->withErrors(['error' => 'Solo se pueden editar OCD en estado Borrador o Devueltas.']);
        }

        // Verificar que es el creador
        if ($directPurchaseOrder->created_by !== Auth::id()) {
            return redirect()
                ->route('purchase-orders.index')
                ->withErrors(['error' => 'Solo puede editar sus propias OCD.']);
        }

        // Cargar relaciones
        $directPurchaseOrder->load(['items', 'documents']);

        // Cargar datos para el formulario
        $suppliers = Supplier::where('status', 'ACTIVE')
            ->orderBy('company_name')
            ->get();

        $costCenters = CostCenter::orderBy('name')->get();

        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('direct-purchase-orders.edit', compact(
            'directPurchaseOrder',
            'suppliers',
            'costCenters',
            'expenseCategories'
        ));
    }

    /**
     * Actualizar OCD existente
     */
    public function update(SaveDirectPurchaseOrderRequest $request, DirectPurchaseOrder $directPurchaseOrder)
    {
        try {
            DB::beginTransaction();

            // 1. Calcular nuevos totales
            $totals = $this->calculateTotals($request->items);

            // 2. Validar presupuesto disponible
            $budgetValidation = $this->validateBudgetAvailability(
                $request->cost_center_id,
                $request->application_month,
                $request->expense_category_id,
                $totals['total']
            );

            if (!$budgetValidation['available']) {
                return back()
                    ->withInput()
                    ->withErrors(['budget' => $budgetValidation['message']]);
            }

            // 3. Obtener datos del proveedor
            $supplier = Supplier::find($request->supplier_id);

            // 4. Actualizar la OCD
            $directPurchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'cost_center_id' => $request->cost_center_id,
                'expense_category_id' => $request->expense_category_id,
                'application_month' => $request->application_month,
                'justification' => $request->justification,
                'subtotal' => $totals['subtotal'],
                'iva_amount' => $totals['iva'],
                'total' => $totals['total'],
                'payment_terms' => $request->payment_terms ?? $supplier->default_payment_terms ?? 'Contado',
                'estimated_delivery_days' => $request->estimated_delivery_days ?? $supplier->avg_delivery_time ?? 30,
            ]);

            // 5. Eliminar items anteriores y crear nuevos
            $directPurchaseOrder->items()->delete();

            foreach ($request->items as $itemData) {
                DirectPurchaseOrderItem::create([
                    'direct_purchase_order_id' => $directPurchaseOrder->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);
            }

            // 6. Guardar nuevos documentos si se adjuntaron
            if ($request->hasFile('quotation_file')) {
                // Eliminar cotización anterior
                $oldQuotation = $directPurchaseOrder->documents()
                    ->where('document_type', 'quotation')
                    ->first();
                if ($oldQuotation) {
                    $oldQuotation->delete(); // El evento del modelo elimina el archivo
                }

                $this->uploadDocument($directPurchaseOrder, $request->file('quotation_file'), 'quotation');
            }

            if ($request->hasFile('support_documents')) {
                foreach ($request->file('support_documents') as $file) {
                    $this->uploadDocument($directPurchaseOrder, $file, 'support_document');
                }
            }

            DB::commit();

            return redirect()
                ->route('direct-purchase-orders.show', $directPurchaseOrder->id)
                ->with('success', 'OCD actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar la OCD: ' . $e->getMessage()]);
        }
    }

    /**
     * Calcular totales (subtotal, IVA, total)
     */
    private function calculateTotals(array $items): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = floatval($item['quantity']);
            $unitPrice = floatval($item['unit_price']);
            $subtotal += ($quantity * $unitPrice);
        }

        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Validar disponibilidad presupuestal
     */
    private function validateBudgetAvailability($costCenterId, $monthStr, $categoryId, $requiredAmount): array
    {
        try {
            // 1. Parsear el mes (YYYY-MM a Year y Month numeric)
            $date = \Carbon\Carbon::parse($monthStr . '-01');
            $year = $date->year;
            $month = $date->month;

            // 2. Buscar el presupuesto anual para este CC y Año
            $budget = \App\Models\AnnualBudget::where('cost_center_id', $costCenterId)
                ->where('fiscal_year', $year)
                ->where('status', 'APROBADO')
                ->first();

            if (!$budget) {
                return [
                    'available' => false,
                    'message' => 'No existe un presupuesto aprobado para el centro de costo y año fiscal seleccionados.',
                ];
            }

            // 3. Obtener disponible para el mes y categoría
            $available = $budget->getAvailableForMonthAndCategory($month, $categoryId);

            if ($available < $requiredAmount) {
                return [
                    'available' => false,
                    'message' => sprintf(
                        'Presupuesto insuficiente para la categoría seleccionada en el mes %d. Disponible: $%s, Requerido: $%s, Faltante: $%s',
                        $month,
                        number_format($available, 2),
                        number_format($requiredAmount, 2),
                        number_format($requiredAmount - $available, 2)
                    ),
                ];
            }

            return [
                'available' => true,
                'message' => 'Presupuesto disponible.',
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'message' => 'Error al validar presupuesto: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener categorías de gasto disponibles para un Centro de Costo y Mes
     */
    public function getAvailableCategories(Request $request)
    {
        $request->validate([
            'cost_center_id' => 'required|exists:cost_centers,id',
            'application_month' => 'required|date_format:Y-m',
        ]);

        try {
            $date = \Carbon\Carbon::parse($request->application_month . '-01');
            $year = $date->year;
            $month = $date->month;

            // 1. Buscar presupuesto anual aprobado
            $budget = \App\Models\AnnualBudget::where('cost_center_id', $request->cost_center_id)
                ->where('fiscal_year', $year)
                ->where('status', 'APROBADO')
                ->first();

            if (!$budget) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay presupuesto aprobado para este centro de costo y año.',
                    'categories' => []
                ]);
            }

            // 2. Obtener categorías con distribución en ese mes
            $categoryIds = \App\Models\BudgetMonthlyDistribution::where('annual_budget_id', $budget->id)
                ->where('month', $month)
                ->whereRaw('assigned_amount - consumed_amount - committed_amount > 0')
                ->pluck('expense_category_id');

            $categories = ExpenseCategory::whereIn('id', $categoryIds)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage(),
                'categories' => []
            ], 500);
        }
    }

    /**
     * Subir y registrar documento
     */
    private function uploadDocument(DirectPurchaseOrder $ocd, $file, $type): DirectPurchaseOrderDocument
    {
        // Generar nombre único para el archivo
        $year = now()->year;
        $month = now()->format('m');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = uniqid('ocd_' . $ocd->id . '_') . '.' . $extension;

        // Guardar en storage
        $path = $file->storeAs(
            "ocd_documents/{$year}/{$month}",
            $fileName,
            'local'
        );

        // Registrar en base de datos
        return DirectPurchaseOrderDocument::create([
            'direct_purchase_order_id' => $ocd->id,
            'document_type' => $type,
            'file_path' => $path,
            'original_filename' => $originalName,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
