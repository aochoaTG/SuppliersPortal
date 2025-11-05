<?php

namespace App\Http\Controllers;

use App\Enum\RequisitionStatus;
use App\Enum\Currency;
use App\Enum\UnitOfMeasure;
use App\Http\Requests\StoreRequisitionRequest;
use App\Http\Requests\UpdateRequisitionRequest;
use App\Models\CostCenter;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Tax;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;


/**
 * RequisitionController (MVP+ con partidas)
 * - Index con DataTable server-side
 * - Create/Edit con filas dinámicas
 * - Store/Update que crean/actualizan partidas y recalculan totales
 */
class RequisitionController extends Controller
{
    public function index(): View
    {
        return view('requisitions.index');
    }

    public function datatable(Request $request)
    {
        $query = Requisition::query()->with('costCenter');

        return DataTables::of($query)
            ->addColumn('cost_center', fn($r) => e($r->costCenter?->name ?? '—'))
            ->addColumn('requester', fn($r) => e($r->requested_by))
            ->editColumn('amount_requested', fn($r) => number_format((float) $r->amount_requested, 2))
            ->editColumn('status', function ($r) {
                $status = RequisitionStatus::from($r->status);
                $cls = RequisitionStatus::badgeClass($status);
                $label = RequisitionStatus::options()[$status->value] ?? $status->value;
                return '<span class="badge bg-' . $cls . '">' . $label . '</span>';
            })
            ->addColumn('actions', function ($r) {
                $showUrl = route('requisitions.show', $r->id);
                $editUrl = route('requisitions.edit', $r->id);
                $deleteUrl = route('requisitions.destroy', $r->id);

                return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i> Acciones
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="' . $showUrl . '">
                                <i class="ti ti-eye me-2"></i>Ver
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . $editUrl . '">
                                <i class="ti ti-edit me-2"></i>Editar
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="' . $deleteUrl . '" method="POST" class="js-delete-form">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="button" class="dropdown-item text-danger js-delete-btn" data-entity="Requisición ' . $r->folio . '">
                                    <i class="ti ti-trash me-2"></i>Eliminar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        $requisition = new Requisition([
            'fiscal_year' => (int) date('Y'),
            'currency_code' => 'MXN',
            'status' => RequisitionStatus::DRAFT->value,
            'required_date' => now()->addDays(7)->toDateString(),
        ]);

        // 🔎 Determina la compañía seleccionada (old > model > usuario)
        $selectedCompanyId = old('company_id', $requisition->company_id ?? (auth()->user()->company_id ?? null));

        // Catálogos
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::when($selectedCompanyId, fn($q) => $q->where('company_id', $selectedCompanyId))
            ->orderBy('name')->get(['id', 'name', 'company_id']);
        $departments = Department::orderBy('name')->get(['id', 'name', 'is_active']);
        $currencies = Currency::options();
        $statusOpts = RequisitionStatus::options();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get(['id', 'name', 'rate_percent']); // ✅ solo activos
        $unitOptions = UnitOfMeasure::groupedOptions(); // ✅ para select con <optgroup>

        return view('requisitions.create', compact(
            'requisition',
            'companies',
            'costCenters',
            'currencies',
            'statusOpts',
            'departments',
            'taxes',
            'unitOptions',
            'selectedCompanyId'
        ));
    }

    public function store(StoreRequisitionRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            // 0) Acción deseada
            $action = $request->string('submit_action')->toString();

            if (!in_array($action, ['draft', 'to_review'], true)) {
                $action = 'draft';
            }

            // 1) Compañía
            $companyId = $data['company_id'];

            // 2) Centro pertenece a la compañía
            $ccOk = CostCenter::where('id', $data['cost_center_id'])
                ->where('company_id', $companyId)
                ->exists();

            if (!$ccOk) {
                throw ValidationException::withMessages([
                    'cost_center_id' => 'El centro de costo no pertenece a la compañía seleccionada.',
                ]);
            }


            // 3) Encabezado
            $req = new Requisition();
            $req->fill([
                'company_id' => $companyId,
                'cost_center_id' => $data['cost_center_id'],
                'department_id' => $data['department_id'],
                'fiscal_year' => $data['fiscal_year'],
                'folio' => Requisition::nextFolio($data['fiscal_year']),
                'requested_by' => auth()->id(),
                'required_date' => $data['required_date'] ?? null,
                'description' => $data['description'] ?? null,
                'justification' => $data['justification'] ?? null,
                'currency_code' => $data['currency_code'],
                'status' => RequisitionStatus::DRAFT->value, // default
                'amount_requested' => 0,
            ]);
            $req->save();

            // 4) Partidas
            foreach ($data['items'] as $i => $it) {
                $qty = (float) ($it['quantity'] ?? 0);
                $price = (float) ($it['unit_price'] ?? 0);
                $tax = (float) ($it['tax_rate'] ?? 0);
                $subtotal = $qty * $price;
                $total = $subtotal + ($subtotal * ($tax / 100));

                RequisitionItem::create([
                    'requisition_id' => $req->id,
                    'line_number' => $it['line_number'] ?? ($i + 1),
                    'item_category' => $it['item_category'] ?? null,
                    'product_code' => $it['product_code'] ?? null,
                    'description' => $it['description'],
                    'quantity' => $qty,
                    'unit' => $it['unit'],
                    'unit_price' => $price,
                    'tax_rate' => $tax,
                    'tax_id' => $it['tax_id'] ?? null,
                    'line_subtotal' => $subtotal,
                    'line_total' => $total,
                    'suggested_vendor_id' => $it['suggested_vendor_id'] ?? null, // ojo: ya usas FK
                    'notes' => $it['notes'] ?? null,
                ]);
            }

            // 5) Totales
            $req->recalcTotals();

            // 6) Si se pidió enviar a revisión, cambia estado a in_review
            // Luego ajusta la comparación
            if ($action === 'to_review') {  // En lugar de 'submit_review'
                $req->status = RequisitionStatus::IN_REVIEW->value;
                $req->reviewed_by = null;   // 👈 asegúrate que no quede basura
                $req->reviewed_at = null;   // 👈 idem
                $req->save();
            }

            // 7) Redirección y mensaje
            if ($action === 'to_review') {
                return redirect()
                    ->route('requisitions.inbox.review')
                    ->with('success', 'Requisición creada y enviada a revisión.');
            }

            return redirect()
                ->route('requisitions.show', $req)
                ->with('success', 'Requisición creada como borrador.');
        });
    }

    public function edit(Requisition $requisition): View
    {
        // Items y relaciones necesarias
        $requisition->load('items');

        // Determina la compañía seleccionada (old > model > usuario)
        $selectedCompanyId = old('company_id', $requisition->company_id ?? (auth()->user()->company_id ?? null));

        // Catálogos
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::when($selectedCompanyId, fn($q) => $q->where('company_id', $selectedCompanyId))
            ->orderBy('name')->get(['id', 'name', 'company_id']);
        $departments = Department::orderBy('name')->get(['id', 'name', 'is_active']);
        $currencies = Currency::options();
        $statusOpts = RequisitionStatus::options();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get(['id', 'name', 'rate_percent']);
        $unitOptions = UnitOfMeasure::groupedOptions();

        return view('requisitions.edit', compact(
            'requisition',
            'companies',
            'costCenters',
            'currencies',
            'statusOpts',
            'departments',
            'taxes',
            'unitOptions',
            'selectedCompanyId',
        ));
    }

    public function update(UpdateRequisitionRequest $request, Requisition $requisition)
    {
        return DB::transaction(function () use ($request, $requisition) {

            $data = $request->validated();
            // Leer ANTES de validated() o directamente del request
            $action = $request->input('action', 'save'); // 'save' | 'submit_review'

            // Agregar validación por si acaso
            if (!in_array($action, ['save', 'submit_review'], true)) {
                $action = 'save';
            }

            // --- 0) Encabezado (no tocamos folio/requested_by/approved_*)
            $requisition->fill([
                'company_id' => $data['company_id'],
                'cost_center_id' => $data['cost_center_id'],
                'department_id' => $data['department_id'],
                'fiscal_year' => $data['fiscal_year'],
                'required_date' => $data['required_date'] ?? null,
                'description' => $data['description'] ?? null,
                'justification' => $data['justification'] ?? null,
                'currency_code' => $data['currency_code'],
            ]);
            $requisition->save();

            // --- 1) Prepara colecciones
            $incomingItems = $data['items'] ?? [];
            $incomingIds = collect($incomingItems)->pluck('id')->filter()->map(fn($v) => (int) $v)->all();

            // Partidas actuales en DB
            $currentItems = $requisition->items()->get(['id', 'line_number']);
            $currentIds = $currentItems->pluck('id')->all();

            // --- 2) Elimina primero las partidas que YA NO vienen en el payload
            $toDelete = array_diff($currentIds, $incomingIds);
            if (!empty($toDelete)) {
                RequisitionItem::where('requisition_id', $requisition->id)
                    ->whereIn('id', $toDelete)
                    ->delete();
            }

            // Relee los actuales (ya sin los eliminados) para tener max(line_number) real
            $remainingItems = $requisition->items()->get(['id', 'line_number']);
            $maxLineNumber = (int) ($remainingItems->max('line_number') ?? 0);

            // --- 3) Valida que NO haya line_number duplicados en el payload (entre sí)
            //     Ignoramos nulls; se asignarán automáticamente.
            $providedLineNumbers = [];
            foreach ($incomingItems as $idx => $it) {
                if (isset($it['line_number']) && $it['line_number'] !== null && $it['line_number'] !== '') {
                    $ln = (int) $it['line_number'];
                    if (isset($providedLineNumbers[$ln])) {
                        throw ValidationException::withMessages([
                            "items.$idx.line_number" => "El número de línea {$ln} está repetido en el formulario.",
                        ]);
                    }
                    $providedLineNumbers[$ln] = true;
                }
            }

            // --- 4) Upsert: actualiza existentes y crea nuevas
            $keptIds = [];

            foreach ($incomingItems as $i => $it) {
                $itemId = $it['id'] ?? null;

                // Normaliza campos numéricos
                $qty = (float) ($it['quantity'] ?? 0);
                $price = (float) ($it['unit_price'] ?? 0);
                $tax = (float) ($it['tax_rate'] ?? 0);
                $subtotal = $qty * $price;
                $total = $subtotal + ($subtotal * ($tax / 100));

                // Determina line_number:
                // - si viene en payload, se usa tal cual
                // - si viene null/empty, asigna max+1 incremental (dentro de esta requisición)
                $lineNumber = isset($it['line_number']) && $it['line_number'] !== '' ? (int) $it['line_number'] : null;
                if ($lineNumber === null) {
                    $lineNumber = ++$maxLineNumber; // autoincrementa en memoria
                }

                $payload = [
                    'line_number' => $lineNumber,
                    'item_category' => $it['item_category'] ?? null,
                    'product_code' => $it['product_code'] ?? null,
                    'description' => $it['description'],
                    'quantity' => $qty,
                    'unit' => $it['unit'],
                    'unit_price' => $price,
                    'tax_rate' => $tax,
                    'tax_id' => $it['tax_id'] ?? null,
                    'line_subtotal' => $subtotal,
                    'line_total' => $total,
                    'suggested_vendor_id' => $it['suggested_vendor_id'] ?? null,
                    'notes' => $it['notes'] ?? null,
                ];

                if ($itemId) {
                    // UPDATE: valida pertenencia
                    $item = RequisitionItem::where('id', $itemId)
                        ->where('requisition_id', $requisition->id)
                        ->first();

                    if (!$item) {
                        throw ValidationException::withMessages([
                            "items.$i.id" => 'La partida no pertenece a esta requisición.',
                        ]);
                    }

                    // update (esto puede cambiar line_number; ya validamos no duplicados en payload)
                    $item->update($payload);
                    $keptIds[] = (int) $item->id;
                } else {
                    // CREATE (usa line_number decidido arriba)
                    $payload['requisition_id'] = $requisition->id;
                    $item = RequisitionItem::create($payload);
                    $keptIds[] = (int) $item->id;
                }
            }

            // --- 5) Recalcula totales
            $requisition->recalcTotals();

            // --- 6) Resolver estado según acción del footer
            if ($action === 'submit_review') {
                $requisition->status = RequisitionStatus::IN_REVIEW->value;
                $requisition->reviewed_by = null;
                $requisition->reviewed_at = null;
                $requisition->save();

                return redirect()
                    ->route('requisitions.inbox.review')
                    ->with('success', 'Requisición actualizada y enviada a revisión.');
            }

            // Solo guardar
            return redirect()
                ->route('requisitions.show', $requisition)
                ->with('success', 'Requisición actualizada correctamente.');
        });
    }


    public function destroy(Requisition $requisition): RedirectResponse
    {
        $requisition->delete();

        return redirect()->route('requisitions.index')
            ->with('success', 'Requisición eliminada correctamente.');
    }

    public function show(Requisition $requisition): View
    {
        $requisition->load([
            'company',
            'costCenter',
            'department',
            'items' => fn($q) => $q->orderBy('line_number'), // único ORDER BY
        ]);

        return view('requisitions.show', compact('requisition'));
    }
}
