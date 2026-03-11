<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisitionItem extends Model
{
    protected $table = 'requisition_items';

    protected $fillable = [
        'requisition_id',
        'product_service_id',
        'line_number',
        'item_category',
        'product_code',
        'description',
        'expense_category_id',
        'quantity',
        'unit',
        'suggested_vendor_id',
        'notes',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'expense_category_id' => 'integer',
        'quantity' => 'decimal:3',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /**
     * Relación con la requisición padre.
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Relación con el producto/servicio del catálogo.
     * RN-001: Solo productos del catálogo pueden ser requisitados.
     */
    public function productService(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, 'product_service_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, 'product_service_id');
    }

    /**
     * Relación con la categoría de gasto presupuestal (OBLIGATORIO).
     * RN-010A: Cada partida DEBE tener una categoría de gasto asignada.
     * RN-010B: La categoría es seleccionada por el REQUISITOR.
     * RN-010H: Compras NO puede modificar esta categoría.
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Relación con el proveedor sugerido del catálogo.
     * Este es solo una sugerencia, NO vinculante.
     */
    public function suggestedVendor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'suggested_vendor_id');
    }

    // =========================================================================
    // MÉTODOS DE VALIDACIÓN
    // =========================================================================

    /**
     * Verifica que la partida tenga todos los campos obligatorios.
     */
    public function isValid(): bool
    {
        return !empty($this->product_service_id)
            && !empty($this->expense_category_id)
            && $this->quantity > 0;
    }

    // =========================================================================
    // MÉTODOS DE INFORMACIÓN
    // =========================================================================

    /**
     * Obtiene la descripción completa del ítem.
     * Si tiene código de producto, lo incluye.
     */
    public function getFullDescription(): string
    {
        if ($this->product_code) {
            return "[{$this->product_code}] {$this->description}";
        }

        return $this->description;
    }

    /**
     * Obtiene información resumida de la partida para mostrar en listas.
     * RN-002: NO incluye precios porque no están disponibles en requisiciones.
     */
    public function getSummary(): array
    {
        return [
            'line_number' => $this->line_number,
            'description' => $this->getFullDescription(),
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'expense_category' => $this->expenseCategory?->name ?? 'Sin categoría',
            'notes' => $this->notes,
            'suggested_vendor' => $this->suggestedVendor?->name ?? null,
        ];
    }

    /**
     * Obtiene la cantidad formateada.
     */
    public function getFormattedQuantity(): string
    {
        return number_format($this->quantity, 3) . ' ' . $this->unit;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Partidas de una requisición específica.
     */
    public function scopeOfRequisition($query, int $requisitionId)
    {
        return $query->where('requisition_id', $requisitionId);
    }

    /**
     * Partidas de una categoría de gasto específica.
     */
    public function scopeOfExpenseCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    /**
     * Partidas ordenadas por número de línea.
     */
    public function scopeOrderedByLine($query)
    {
        return $query->orderBy('line_number');
    }

    /**
     * Partidas con eager loading de relaciones comunes.
     */
    public function scopeWithRelations($query)
    {
        return $query->with([
            'productService',
            'expenseCategory',
            'suggestedVendor'
        ]);
    }

    // =========================================================================
    // EVENTOS DEL MODELO
    // =========================================================================

    /**
     * Boot del modelo para eventos automáticos.
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de crear, asegurar que tenga número de línea
        static::creating(function ($item) {
            if (!$item->line_number) {
                $maxLine = static::where('requisition_id', $item->requisition_id)
                    ->max('line_number');
                $item->line_number = ($maxLine ?? 0) + 1;
            }

            // Heredar información del catálogo si viene de ahí
            if ($item->product_service_id && !$item->description) {
                $product = ProductService::find($item->product_service_id);
                if ($product) {
                    $item->description = $item->description ?? $product->technical_description;
                    $item->item_category = $item->item_category ?? $product->category?->name;
                    $item->product_code = $item->product_code ?? $product->code;
                    $item->unit = $item->unit ?? $product->unit_of_measure;
                }
            }
        });

        // Después de eliminar una partida, renumerar las restantes
        static::deleted(function ($item) {
            $remainingItems = static::where('requisition_id', $item->requisition_id)
                ->orderBy('line_number')
                ->get();

            $lineNumber = 1;
            foreach ($remainingItems as $remaining) {
                if ($remaining->line_number !== $lineNumber) {
                    $remaining->line_number = $lineNumber;
                    $remaining->save();
                }
                $lineNumber++;
            }
        });
    }

    // =========================================================================
    // MÉTODOS ESTÁTICOS ÚTILES
    // =========================================================================

    /**
     * Obtiene el siguiente número de línea disponible para una requisición.
     */
    public static function nextLineNumber(int $requisitionId): int
    {
        $maxLine = static::where('requisition_id', $requisitionId)
            ->max('line_number');

        return ($maxLine ?? 0) + 1;
    }

    /**
     * Cuenta cuántas partidas tiene una requisición.
     */
    public static function countByRequisition(int $requisitionId): int
    {
        return static::where('requisition_id', $requisitionId)->count();
    }

    /**
     * Obtiene partidas agrupadas por categoría de gasto.
     * 
     * IMPORTANTE: Este agrupamiento muestra CANTIDADES, NO PRECIOS.
     * Útil para que el requisitor vea la distribución de su solicitud.
     * 
     * La validación presupuestal con MONTOS se hará en la tabla quotations.
     */
    public static function groupedByCategory(int $requisitionId): array
    {
        $items = static::with('expenseCategory')
            ->where('requisition_id', $requisitionId)
            ->orderBy('expense_category_id')
            ->get();

        $grouped = [];

        foreach ($items as $item) {
            $key = $item->expense_category_id;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'expense_category_id' => $item->expense_category_id,
                    'expense_category_name' => $item->expenseCategory?->name ?? 'Sin categoría',
                    'items_count' => 0,
                    'items' => [],
                ];
            }

            $grouped[$key]['items_count']++;
            $grouped[$key]['items'][] = $item;
        }

        return array_values($grouped);
    }

    /**
     * Valida que todas las partidas de una requisición sean válidas.
     */
    public static function validateRequisitionItems(int $requisitionId): array
    {
        $items = static::where('requisition_id', $requisitionId)->get();
        $errors = [];

        if ($items->isEmpty()) {
            $errors[] = 'La requisición debe tener al menos una partida (RN-003)';
            return $errors;
        }

        foreach ($items as $item) {
            // Validar que tenga producto del catálogo (RN-001)
            if (!$item->product_service_id) {
                $errors[] = "Partida {$item->line_number}: No tiene producto del catálogo (RN-001)";
            }

            // Validar categoría de gasto (RN-010A)
            if (!$item->expense_category_id) {
                $errors[] = "Partida {$item->line_number}: Falta categoría de gasto (RN-010A)";
            }

            // Validar cantidad
            if ($item->quantity <= 0) {
                $errors[] = "Partida {$item->line_number}: La cantidad debe ser mayor a cero";
            }
        }

        return $errors;
    }

    /**
     * Grupos de cotización en los que está esta partida.
     */
    public function quotationGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            QuotationGroup::class,
            'quotation_group_items',
            'requisition_item_id',
            'quotation_group_id'
        )
            ->withPivot(['notes', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * RFQs individuales para esta partida.
     */
    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    /**
     * Respuestas de cotización para esta partida.
     */
    public function rfqResponses(): HasMany
    {
        return $this->hasMany(RfqResponse::class);
    }

    /**
     * Verifica si la partida está en algún grupo.
     */
    public function isInGroup(): bool
    {
        return $this->quotationGroups()->exists();
    }

    /**
     * Verifica si la partida tiene cotizaciones.
     */
    public function hasQuotations(): bool
    {
        return $this->rfqResponses()->exists();
    }
}
