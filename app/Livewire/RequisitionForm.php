<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Enum\RequisitionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RequisitionForm extends Component
{
    // ===== PROPIEDADES DE MODO =====
    public $isEditMode = false;
    public $requisitionId;
    public $folio;

    // ===== PROPIEDADES DEL FORMULARIO =====
    public $company_id;
    public $cost_center_id;
    public $required_date;
    public $description = '';

    // ===== COLECCIONES =====
    public $companies = [];
    public $costCenters = [];

    // ===== CONTADOR DE CARACTERES =====
    public $descriptionMaxLength = 500;
    public $descriptionRemainingChars = 500;

    // ===== PARTIDAS =====
    public $items = [];
    public $editingItemIndex = null;

    /**
     * Inicialización del componente.
     * Soporta modo CREACIÓN y EDICIÓN.
     */
    public function mount($requisition = null)
    {
        // Cargar solo las compañías del usuario autenticado
        $this->companies = Auth::user()->companies()->orderBy('name')->get();

        if ($requisition && $requisition->exists) {
            // ===== MODO EDICIÓN =====
            $this->isEditMode = true;
            $this->requisitionId = $requisition->id;
            $this->folio = $requisition->folio;

            // Validar que sea editable (solo DRAFT)
            if ($requisition->status->value !== RequisitionStatus::DRAFT->value && $requisition->status->value !== RequisitionStatus::REJECTED->value) {
                session()->flash('error', 'Solo se pueden editar requisiciones en estado Borrador o Rechazada.');
                return redirect()->route('requisitions.index');
            }

            // Cargar datos del formulario
            $this->company_id = $requisition->company_id;
            $this->description = $requisition->description;
            $this->required_date = $requisition->required_date
                ? $requisition->required_date->format('Y-m-d')
                : null;

            // Cargar centros de costo de la compañía
            $this->loadCostCenters($this->company_id);
            $this->cost_center_id = $requisition->cost_center_id;

            // Cargar partidas existentes
            $this->items = $requisition->items->map(function ($item) {
                return [
                    'product_id' => $item->product_service_id,
                    'product_name' => "[{$item->product_code}] " . $item->productService->short_name ?? $item->description,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'expense_category_id' => $item->expense_category_id,
                    'expense_category_name' => $item->expenseCategory->name ?? 'N/A',
                    'notes' => $item->notes ?? '',
                ];
            })->toArray();

            // Actualizar contador de caracteres
            $this->updatedDescription($this->description);
        } else {
            // ===== MODO CREACIÓN =====
            $this->isEditMode = false;
            $this->required_date = now()->addDays(7)->format('Y-m-d');
            $this->items = [];
        }
    }

    // =====================================================
    // MÉTODOS DE GUARDADO
    // =====================================================

    /**
     * Guardar como borrador.
     */
    public function saveDraft()
    {
        $this->save('draft');
    }

    /**
     * Enviar a Compras.
     */
    public function submit()
    {
        $this->save('pending');
    }

    /**
     * Método privado para guardar/actualizar la requisición.
     */
    private function save($status = 'draft')
    {
        // Validar campos obligatorios
        $this->validate([
            'company_id' => 'required|exists:companies,id',
            'cost_center_id' => 'required|exists:cost_centers,id',
            'required_date' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string|max:500',
        ], [
            'company_id.required' => 'La compañía es obligatoria.',
            'cost_center_id.required' => 'El centro de costos es obligatorio.',
            'required_date.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy.',
        ]);

        // Validar que tenga al menos una partida (RN-003)
        if (empty($this->items)) {
            $this->dispatch('validation-error', message: 'Debe agregar al menos una partida a la requisición (RN-003).');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->isEditMode) {
                // ===== MODO EDICIÓN =====
                $requisition = Requisition::findOrFail($this->requisitionId);

                // Actualizar datos principales
                $requisition->update([
                    'company_id' => $this->company_id,
                    'cost_center_id' => $this->cost_center_id,
                    'required_date' => $this->required_date,
                    'description' => $this->description,
                    'status' => $status === 'pending' ? 'draft' : $status, // Se cambiará después si es pending
                    'updated_by' => Auth::id(),
                ]);

                // Eliminar partidas existentes
                $requisition->items()->delete();

                // Recrear partidas
                foreach ($this->items as $index => $item) {
                    $product = \App\Models\ProductService::find($item['product_id']);
                    RequisitionItem::create([
                        'requisition_id'      => $requisition->id,
                        'product_service_id'  => $item['product_id'],
                        'line_number'         => $index + 1,
                        'product_code'        => $product->code,
                        'description'         => $item['description'],
                        'expense_category_id' => $item['expense_category_id'],
                        'item_category'       => $product->product_type,
                        'quantity'            => $item['quantity'],
                        'unit'                => $item['unit'],
                        'suggested_vendor_id' => $product->default_vendor_id ?? null,
                        'notes'               => $item['notes'] ?? null,
                    ]);
                }

                $message = "Requisición {$requisition->folio} actualizada correctamente";
            } else {
                // ===== MODO CREACIÓN =====
                $requisition = Requisition::create([
                    'company_id' => $this->company_id,
                    'cost_center_id' => $this->cost_center_id,
                    'folio' => Requisition::nextFolio(),
                    'requested_by' => Auth::id(),
                    'required_date' => $this->required_date,
                    'description' => $this->description,
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                    'fiscal_year' => now()->year,
                ]);

                // Crear partidas
                foreach ($this->items as $index => $item) {
                    $product = \App\Models\ProductService::find($item['product_id']);
                    RequisitionItem::create([
                        'requisition_id'      => $requisition->id,
                        'product_service_id'  => $item['product_id'],
                        'line_number'         => $index + 1,
                        'product_code'        => $product->code,
                        'description'         => $item['description'],
                        'expense_category_id' => $item['expense_category_id'],
                        'item_category'       => $product->product_type,
                        'quantity'            => $item['quantity'],
                        'unit'                => $item['unit'],
                        'suggested_vendor_id' => $product->default_vendor_id ?? null,
                        'notes'               => $item['notes'] ?? null,
                    ]);
                }

                $message = "Requisición creada con folio: {$requisition->folio}";
            }

            // Si se va a enviar a Compras
            if ($status === 'pending') {
                Log::info('📤 Enviando requisición a Compras desde Livewire', [
                    'requisition_id' => $requisition->id,
                    'folio' => $requisition->folio,
                ]);

                $requisition->refresh();
                $requisition->load('requester', 'costCenter', 'items');

                $sent = $requisition->submitToCompras();

                if (!$sent) {
                    throw new \Exception('No se pudo enviar la requisición a Compras.');
                }

                $message = "Requisición {$requisition->folio} enviada a Compras correctamente";
            }

            DB::commit();

            session()->flash('success', $message);
            return redirect()->route('requisitions.index');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Error al guardar requisición en Livewire', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('save-error', message: 'Error al guardar la requisición: ' . $e->getMessage());
        }
    }

    // =====================================================
    // LISTENERS
    // =====================================================

    public function updatedCompanyId($value)
    {
        $userCompanyIds = Auth::user()->companies()->pluck('companies.id')->toArray();

        if ($value && !in_array($value, $userCompanyIds)) {
            $this->addError('company_id', 'No tienes permiso para usar esta compañía.');
            $this->company_id = null;
            $this->cost_center_id = null;
            $this->costCenters = [];
            return;
        }

        $this->cost_center_id = null;

        if ($value) {
            $this->loadCostCenters($value);
        } else {
            $this->costCenters = [];
        }
    }

    /**
     * Cargar centros de costo del usuario para una compañía.
     */
    private function loadCostCenters($companyId)
    {
        $this->costCenters = Auth::user()->costCenters()
            ->where('cost_centers.company_id', $companyId)
            ->where('cost_center_user.is_active', true)
            ->orderBy('cost_centers.code')
            ->get();

        // ✅ Seleccionar automáticamente el centro de costo predeterminado
        $defaultCostCenter = $this->costCenters->firstWhere('pivot.is_default', true);

        if ($defaultCostCenter && !$this->cost_center_id) {
            $this->cost_center_id = $defaultCostCenter->id;
        }
    }

    public function updatedDescription($value)
    {
        $this->descriptionRemainingChars = $this->descriptionMaxLength - strlen($value);
    }

    // =====================================================
    // GESTIÓN DE PARTIDAS
    // =====================================================

    public function addItem($itemData)
    {
        if (empty($itemData['product_id']) || empty($itemData['expense_category_id'])) {
            $this->dispatch('item-error', message: 'Faltan campos obligatorios');
            return;
        }

        $this->items[] = [
            'product_id' => $itemData['product_id'],
            'product_name' => $itemData['product_name'],
            'description' => $itemData['description'],
            'quantity' => $itemData['quantity'],
            'unit' => $itemData['unit'],
            'expense_category_id' => $itemData['expense_category_id'],
            'expense_category_name' => $itemData['expense_category_name'],
            'notes' => $itemData['notes'] ?? '',
        ];

        $this->dispatch('item-added', message: 'Partida agregada correctamente');
    }

    public function updateItem($index, $itemData)
    {
        if (!isset($this->items[$index])) {
            $this->dispatch('item-error', message: 'Partida no encontrada');
            return;
        }

        $this->items[$index] = [
            'product_id' => $itemData['product_id'],
            'product_name' => $itemData['product_name'],
            'description' => $itemData['description'],
            'quantity' => $itemData['quantity'],
            'unit' => $itemData['unit'],
            'expense_category_id' => $itemData['expense_category_id'],
            'expense_category_name' => $itemData['expense_category_name'],
            'notes' => $itemData['notes'] ?? '',
        ];

        $this->dispatch('item-updated', message: 'Partida actualizada correctamente');
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->dispatch('item-removed', message: 'Partida eliminada correctamente');
        }
    }

    public function getItemForEdit($index)
    {
        if (isset($this->items[$index])) {
            $this->editingItemIndex = $index;
            return $this->items[$index];
        }
        return null;
    }

    public function render()
    {
        return view('livewire.requisition-form');
    }
}
