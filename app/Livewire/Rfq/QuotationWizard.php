<?php

namespace App\Livewire\Rfq;

use App\Enum\RequisitionStatus;
use App\Models\Requisition;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Rfq;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotationWizard extends Component
{
    // La requisición con la que trabajaremos
    public Requisition $requisition;

    // Paso actual (1 a 5)
    public $currentStep = 1;

    // Datos que se van recolectando en cada paso
    public $validationData = [];
    public $planningData = [];
    public $suppliersData = [];
    public $rfqData = [];
    public $comparisonData = [];

    // ======= NUEVO: Datos para el paso 2 =======
    public $unassignedItems = [];
    public $groups = [];

    /**
     * Inicializar el wizard con la requisición
     */
    public function mount(Requisition $requisition)
    {
        $this->requisition = $requisition->load([
            'requester',
            'company',
            'costCenter',
            'department',
            'items.productService',
            'items.expenseCategory',
            'quotationGroups.items',
            'rfqs',
            'rfqs.suppliers',
        ]);

        // Determinar el paso inicial basado en el estado de la requisición
        // Si la URL no trae un paso (es 1 por defecto), calculamos el paso lógico
        if ($this->currentStep == 1) {
            $this->currentStep = $this->determineCurrentStep();
        }

        // Cargar datos según el paso en el que quedamos
        $this->loadStepData();

        // Cargar datos de validación si ya fueron validados
        if ($this->requisition->validated_at) {
            $this->validationData = [
                'specs_clear' => $this->requisition->validation_specs_clear,
                'time_feasible' => $this->requisition->validation_time_feasible,
                'alternatives_evaluated' => $this->requisition->validation_alternatives_evaluated,
                'purchasing_notes' => $this->requisition->purchasing_validation_notes,
            ];
        }

        // Cargar datos de planificación si estamos en paso 2 o superior
        if ($this->currentStep >= 2) {
            $this->loadPlanningData();
        }

        // Cargar datos de proveedores si estamos en paso 3 o superior
        if ($this->currentStep >= 3) {
            $this->loadSuppliersData();
        }
    }

    /**
     * Nueva función auxiliar para cargar datos según el paso actual
     */
    public function loadStepData()
    {
        if ($this->currentStep >= 2) $this->loadPlanningData();
        if ($this->currentStep >= 3) $this->loadSuppliersData();
    }

    /**
     * Determinar el paso actual basado en el estado de la requisición
     */
    private function determineCurrentStep(): int
    {
        // 🎯 NUEVO: Si hay RFQs que ya tienen respuestas, ir al paso 5
        if ($this->requisition->rfqs()->whereIn('status', ['RECEIVED', 'EVALUATED'])->exists()) {
            return 5;
        }

        // Si hay RFQs creadas, ir al paso 4
        if ($this->requisition->rfqs()->exists()) {
            return 4;
        }

        // Si tiene grupos de cotización, ir al paso 3
        if ($this->requisition->quotationGroups()->exists()) {
            return 3;
        }

        // Si ya fue validada, ir al paso 2
        if ($this->requisition->validated_at) {
            return 2;
        }

        // Por defecto, iniciar en paso 1
        return 1;
    }

    /**
     * Cargar datos para el planificador (Paso 2)
     */
    public function loadPlanningData()
    {
        // Obtener partidas que NO están en ningún grupo
        $this->unassignedItems = $this->requisition->items()
            ->whereDoesntHave('quotationGroups')
            ->with('productService', 'expenseCategory')
            ->get()
            ->toArray();

        // Obtener grupos existentes con sus partidas
        $this->groups = $this->requisition->quotationGroups()
            ->with('items.productService')
            ->get()
            ->toArray();
    }

    /**
     * Completar y guardar el paso 1 (Validación)
     */
    public function completeStep1()
    {
        // Validar que todos los checkboxes estén marcados
        if (
            !($this->validationData['specs_clear'] ?? false) ||
            !($this->validationData['time_feasible'] ?? false) ||
            !($this->validationData['alternatives_evaluated'] ?? false)
        ) {

            session()->flash('error', 'Debes completar todas las validaciones antes de continuar.');
            return;
        }

        try {
            // Cambiar estado a IN_QUOTATION y guardar validaciones
            $this->requisition->update([
                'status' => RequisitionStatus::IN_QUOTATION,
                'updated_by' => Auth::id(),

                // Limpiar campos de pausa
                'pause_reason' => null,
                'paused_by' => null,
                'paused_at' => null,

                // ======= NUEVO: Guardar validaciones =======
                'validation_specs_clear' => $this->validationData['specs_clear'] ?? false,
                'validation_time_feasible' => $this->validationData['time_feasible'] ?? false,
                'validation_alternatives_evaluated' => $this->validationData['alternatives_evaluated'] ?? false,
                'validated_at' => now(),
                'validated_by' => Auth::id(),

                // Guardar notas si existen
                'purchasing_validation_notes' => $this->validationData['purchasing_notes'] ?? null,
            ]);

            // Enviar notificación al requisitor
            if ($this->requisition->requester) {
                $this->requisition->requester->notify(new \App\Notifications\RequisitionInQuotationNotification($this->requisition));
            }

            // Recargar la requisición con el nuevo estado
            $this->requisition->refresh();

            session()->flash('success', '✅ Requisición validada. Puede proceder con el proceso de cotización.');

            // Avanzar al siguiente paso
            $this->currentStep = 2;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al validar la requisición: ' . $e->getMessage());
        }
    }

    /**
     * Ir al siguiente paso (genérico para pasos 2-5)
     */
    public function nextStep()
    {
        if ($this->currentStep < 5) {
            $this->currentStep++;

            // Recargar datos según el paso
            if ($this->currentStep === 2) {
                $this->loadPlanningData();
            }
        }
    }

    /**
     * Ir al paso anterior
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;

            // Recargar datos según el paso
            if ($this->currentStep === 2) {
                $this->loadPlanningData();
            } elseif ($this->currentStep === 3) {
                $this->loadSuppliersData();
            }
        }
    }

    /**
     * Ir a un paso específico
     */
    public function goToStep($step)
    {
        if ($step >= 1 && $step <= 5) {
            $this->currentStep = $step;

            // Recargar datos según el paso
            if ($step === 2) {
                $this->loadPlanningData();
            } elseif ($step === 3) {
                $this->loadSuppliersData();
            }
        }
    }

    /**
     * Validar el paso actual
     */
    private function validateCurrentStep()
    {
        // Aquí agregaremos validaciones específicas para cada paso
        // Por ahora dejamos la validación básica
    }

    /**
     * Validar y guardar datos del paso 1
     */
    public function validateStep1()
    {
        // Validar que todos los checkboxes estén marcados
        $this->validate([
            'validationData.specs_clear' => 'required|accepted',
            'validationData.time_feasible' => 'required|accepted',
            'validationData.alternatives_evaluated' => 'required|accepted',
        ], [
            'validationData.specs_clear.accepted' => 'Debes verificar la claridad de especificaciones',
            'validationData.time_feasible.accepted' => 'Debes verificar la factibilidad de tiempos',
            'validationData.alternatives_evaluated.accepted' => 'Debes verificar las alternativas',
        ]);

        // Guardar datos y avanzar
        $this->nextStep();
    }

    /**
     * Rechazar y devolver requisición al usuario
     */
    public function rejectRequisition($reason)
    {
        // Validar longitud mínima del motivo
        if (strlen($reason) < 20) {
            session()->flash('error', 'El motivo debe tener al menos 20 caracteres.');
            return;
        }

        try {
            $this->requisition->update([
                'status' => RequisitionStatus::REJECTED,
                'rejection_reason' => $reason,
                'rejected_at' => now(),
                'rejected_by' => Auth::id()
            ]);

            session()->flash('success', "Requisición {$this->requisition->folio} devuelta al usuario correctamente.");
            return redirect()->route('quotes.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al rechazar la requisición: ' . $e->getMessage());
        }
    }

    /**
     * Completar y guardar el paso 3 (Selección de Proveedores y Creación de RFQs)
     */
    public function completeStep3($groupsData)
    {
        $groupsData = collect($groupsData)->toArray();
        DB::beginTransaction();

        try {
            foreach ($groupsData as $groupData) {
                $existingRfq = Rfq::where('requisition_id', $this->requisition->id)
                    ->where('quotation_group_id', $groupData['group_id'])
                    ->where('status', '!=', 'CANCELLED')
                    ->first();

                if ($existingRfq) {
                    // 🔍 COMPARACIÓN DE CAMBIOS
                    // Sacamos los IDs actuales de la DB
                    $currentSuppliers = $existingRfq->suppliers->pluck('id')->sort()->values()->toArray();
                    // Sacamos los IDs que vienen del JS
                    $newSuppliers = collect($groupData['supplier_ids'])->map(fn($id) => (int)$id)->sort()->values()->toArray();

                    $hasChanges = (
                        $currentSuppliers !== $newSuppliers ||
                        $existingRfq->response_deadline->format('Y-m-d') !== $groupData['response_deadline'] ||
                        $existingRfq->message !== ($groupData['notes'] ?? null)
                    );

                    // Si NO hay cambios, saltamos este grupo (No lo tocamos)
                    if (!$hasChanges) {
                        Log::info("⏭️ Sin cambios en RFQ {$existingRfq->folio}, ignorando.");
                        continue;
                    }

                    // Si hay cambios y es DRAFT, actualizamos
                    if ($existingRfq->status === 'DRAFT') {
                        $existingRfq->update([
                            'response_deadline' => $groupData['response_deadline'],
                            'message' => $groupData['notes'] ?? null,
                        ]);
                        $existingRfq->suppliers()->sync($this->prepareSupplierPivotData($groupData['supplier_ids']));
                    }
                    // Si hay cambios y ya fue ENVIADA, entonces sí cancelamos y creamos nueva
                    else {
                        $existingRfq->update([
                            'status' => 'CANCELLED',
                            'cancelled_at' => now(),
                            'cancelled_by' => Auth::id(),
                            'cancellation_reason' => 'Actualización manual de proveedores tras envío.',
                        ]);
                        $this->createNewRfq($groupData);
                    }
                } else {
                    $this->createNewRfq($groupData);
                }
            }

            DB::commit();
            $this->currentStep = 4;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Función auxiliar para crear un nuevo registro de RFQ limpio
     */
    private function createNewRfq($groupData)
    {
        $rfq = Rfq::create([
            'folio' => $this->generateRFQFolio(),
            'requisition_id' => $this->requisition->id,
            'quotation_group_id' => $groupData['group_id'],
            'status' => 'DRAFT',
            'response_deadline' => $groupData['response_deadline'],
            'message' => $groupData['notes'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $rfq->suppliers()->attach($this->prepareSupplierPivotData($groupData['supplier_ids']));
        return $rfq;
    }

    /**
     * Formatea los IDs de proveedores para el método sync/attach
     */
    private function prepareSupplierPivotData($supplierIds)
    {
        $pivotData = [];
        foreach ($supplierIds as $id) {
            $pivotData[$id] = [
                'invited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return $pivotData;
    }

    /**
     * Generar folio único de RFQ
     * 
     * @return string
     */
    private function generateRFQFolio(): string
    {
        $date = now()->format('Ymd');
        $count = Rfq::whereDate('created_at', today())->count() + 1;

        return 'RFQ-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cargar datos de proveedores desde RFQs existentes (Paso 3)
     */
    public function loadSuppliersData()
    {
        $this->suppliersData = [];

        // Obtener RFQs existentes agrupadas por quotation_group_id
        $rfqs = $this->requisition->rfqs()
            ->with(['suppliers', 'quotationGroup'])
            ->get();

        if ($rfqs->isEmpty()) {
            return;
        }

        // Mapear datos de RFQs existentes
        foreach ($rfqs as $rfq) {
            $this->suppliersData[] = [
                'rfq_id' => $rfq->id,
                'group_id' => $rfq->quotation_group_id,
                'group_name' => $rfq->quotationGroup->name ?? '',
                'supplier_ids' => $rfq->suppliers->pluck('id')->toArray(),
                'response_deadline' => $rfq->response_deadline?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d'),
                'notes' => $rfq->message ?? '',
            ];
        }

        Log::info('✅ Datos de proveedores cargados', [
            'count' => count($this->suppliersData),
            'data' => $this->suppliersData
        ]);
    }

    /**
     * Renderizar el componente
     */
    public function render()
    {
        return view('livewire.rfq.quotation-wizard');
    }
}
