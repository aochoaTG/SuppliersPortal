<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     * Filtros: ?q=texto&company_id=1&is_active=1|0|all&source_system=ControlGas
     */
    public function index(Request $request)
    {
        $query = Station::query()->with('company');

        // Filtro por empresa
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        // Filtro de estado (activo/inactivo/all)
        $isActive = $request->input('is_active', '1');
        if ($isActive !== 'all') {
            $query->where('is_active', $isActive === '1');
        }

        // Filtro por sistema origen
        if ($request->filled('source_system')) {
            $query->where('source_system', $request->input('source_system'));
        }

        // Búsqueda rápida
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($w) use ($q) {
                $w->where('station_name', 'like', "%{$q}%")
                    ->orWhere('external_id', 'like', "%{$q}%")
                    ->orWhere('cre_permit', 'like', "%{$q}%")
                    ->orWhere('municipality', 'like', "%{$q}%")
                    ->orWhere('state', 'like', "%{$q}%")
                    ->orWhere('expedition_place', 'like', "%{$q}%");
            });
        }

        $stations = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Si usas Blade:
        return view('stations.index', [
            'stations' => $stations,
            'filters' => $request->only(['q', 'company_id', 'is_active', 'source_system']),
        ]);
    }

    public function datatable(Request $request)
    {
        // Parámetros DataTables
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 50);
        $search = trim($request->input('search.value', ''));

        // Columnas permitidas para ordenar
        $orderColIndex = (int) ($request->input('order.0.column', 0));
        $orderDir = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $columnsMap = [
            0 => 'stations.id',
            1 => 'stations.station_name',
            2 => 'companies.name',
            3 => 'stations.state',
            4 => 'stations.municipality',
            5 => 'stations.cre_permit',
            6 => 'stations.is_active',
        ];
        $orderCol = $columnsMap[$orderColIndex] ?? 'stations.id';

        $base = Station::query()
            ->leftJoin('companies', 'companies.id', '=', 'stations.company_id')
            ->select([
                'stations.id',
                'stations.station_name',
                'stations.state',
                'stations.municipality',
                'stations.cre_permit',
                'stations.is_active',
                'stations.source_system',
                'stations.external_id',
                'companies.name as company_name',
            ]);

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('stations.station_name', 'like', "%{$search}%")
                    ->orWhere('stations.cre_permit', 'like', "%{$search}%")
                    ->orWhere('stations.external_id', 'like', "%{$search}%")
                    ->orWhere('stations.state', 'like', "%{$search}%")
                    ->orWhere('stations.municipality', 'like', "%{$search}%")
                    ->orWhere('companies.name', 'like', "%{$search}%");
            });
        }

        // Filtros avanzados (opcionales)
        if ($request->filled('company_id')) {
            $base->where('stations.company_id', $request->integer('company_id'));
        }
        if ($request->filled('is_active') && $request->input('is_active') !== 'all') {
            $base->where('stations.is_active', $request->input('is_active') == '1');
        }
        if ($request->filled('source_system')) {
            $base->where('stations.source_system', $request->input('source_system'));
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base->orderBy($orderCol, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($s) {
            $badgeActive = $s->is_active
                ? '<span class="badge bg-success">Activa</span>'
                : '<span class="badge bg-secondary">Inactiva</span>';

            $sys = e($s->source_system ?? '—');
            $extId = $s->external_id ? ' · ' . e($s->external_id) : '';

            $actions = view('stations.partials.actions', ['s' => $s])->render();

            return [
                'id' => $s->id,
                'station_name' => e($s->station_name),
                'company' => $s->company_name ? '<span class="badge bg-primary">' . e($s->company_name) . '</span>' : '<span class="badge bg-secondary">Sin empresa</span>',
                'state_mun' => e($s->state) . ' / ' . e($s->municipality),
                'cre_permit' => e($s->cre_permit ?? '—'),
                'sys_external' => $sys . $extId,
                'is_active' => $badgeActive,
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $station = new Station();
        return view('stations.partials.form', [
            'station' => $station,
            'companies' => $companies,
            'method' => 'POST',
            'action' => route('stations.store'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Regla clave: (source_system, external_id) único y company_id opcional.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'station_name' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'expedition_place' => ['nullable', 'string', 'max:255'],
            'server_ip' => ['nullable', 'ip'],
            'database_name' => ['nullable', 'string', 'max:100'],
            'cre_permit' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'source_system' => ['nullable', 'string', 'max:50'],
            'external_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('stations')->where(
                    fn($q) => $q
                        ->where('source_system', $request->input('source_system'))
                        ->where('external_id', $request->input('external_id'))
                )
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $station = Station::create($data);

        // Respuesta ligera para DataTable
        if ($request->ajax()) {
            return response()->noContent();
        }
        return redirect()->route('stations.show', $station)->with('success', 'Station created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Station $station)
    {
        return view('stations.show', compact('station'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Station $station)
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        return view('stations.partials.form', [
            'station' => $station,
            'companies' => $companies,
            'method' => 'PUT',
            'action' => route('stations.update', $station),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Regla de INTRANSFERIBILIDAD:
     * - Si company_id ya está asignado, NO se puede cambiar.
     */
    public function update(Request $request, Station $station)
    {
        $data = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'station_name' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'expedition_place' => ['nullable', 'string', 'max:255'],
            'server_ip' => ['nullable', 'ip'],
            'database_name' => ['nullable', 'string', 'max:100'],
            'cre_permit' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'source_system' => ['nullable', 'string', 'max:50'],
            'external_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('stations')->ignore($station->id)->where(
                    fn($q) => $q
                        ->where('source_system', $request->input('source_system'))
                        ->where('external_id', $request->input('external_id'))
                )
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($station->company_id !== null && array_key_exists('company_id', $data)) {
            if ($data['company_id'] !== null && (int) $data['company_id'] !== (int) $station->company_id) {
                return response()->json([
                    'message' => 'This station is already assigned to a company and cannot be transferred.',
                    'errors' => ['company_id' => ['Intransferible: no se puede cambiar la empresa.']],
                ], 422);
            }
            if ($data['company_id'] === null) {
                return response()->json([
                    'message' => 'Assigned stations cannot be unassigned.',
                    'errors' => ['company_id' => ['No se puede desasignar una estación ya asignada.']],
                ], 422);
            }
            unset($data['company_id']);
        }

        if (isset($data['is_active'])) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $station->update($data);

        if ($request->ajax()) {
            return response()->noContent();
        }
        return redirect()->route('stations.show', $station)->with('success', 'Station updated');
    }

    /**
     * Remove the specified resource from storage.
     * En lugar de borrar, desactivamos por seguridad (histórico).
     */
    public function destroy(Request $request, Station $station)
    {
        if ($station->is_active) {
            $station->update(['is_active' => false]);
        }
        return $request->ajax() ? response()->noContent()
            : redirect()->route('stations.index')->with('success', 'Station deactivated');
    }

    /**
     * Activar / Desactivar rápidamente (toggle).
     */
    public function toggleActive(Station $station)
    {
        $station->update(['is_active' => !$station->is_active]);

        return back()->with('success', 'Station status updated.');
    }

    // app/Http/Controllers/StationController.php (agrega al final de la clase)
    public function linkCompany(Request $request, Station $station)
    {
        if (!is_null($station->company_id)) {
            return back()->withErrors(['company_id' => 'This station is already assigned to a company and cannot be transferred.']);
        }

        $data = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $station->update(['company_id' => (int) $data['company_id']]);

        return back()->with('success', 'Station linked to company successfully.');
    }

}
