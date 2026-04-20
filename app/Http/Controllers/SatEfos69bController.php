<?php

namespace App\Http\Controllers;

use App\Jobs\SyncEfosJob;
use App\Models\SatEfos69b;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SatEfos69bController extends Controller
{
    public function index()
    {
        return view('sat_efos_69b.index');
    }

    public function data()
    {
        // Trae lo necesario; puedes quitar orderBy si prefieres ordenar en el cliente
        $rows = SatEfos69b::select([
            'number',
            'rfc',
            'company_name',
            'situation',
            'sat_presumption_notice_date',
            'dof_presumption_notice_date',
            'sat_definitive_publication_date',
            'dof_definitive_publication_date',
        ])->orderBy('rfc')->get();

        return response()->json(['data' => $rows]);
    }

    public function sync(): JsonResponse
    {
        $currentJobId = Cache::get('efos_sync_current');
        if ($currentJobId) {
            $state = Cache::get("efos_sync_{$currentJobId}", []);
            if (($state['status'] ?? '') === 'running') {
                return response()->json(['message' => 'Ya hay una sincronización en curso'], 409);
            }
        }

        $jobId = uniqid('efos_', true);
        Cache::put('efos_sync_current', $jobId, now()->addHours(2));
        Cache::put("efos_sync_{$jobId}", [
            'status'      => 'pending',
            'processed'   => 0,
            'total'       => 0,
            'message'     => 'Iniciando sincronización...',
            'started_at'  => now()->toDateTimeString(),
            'finished_at' => null,
        ], now()->addHours(2));

        SyncEfosJob::dispatch($jobId);

        return response()->json(['job_id' => $jobId]);
    }

    public function syncStatus(string $jobId): JsonResponse
    {
        $state = Cache::get("efos_sync_{$jobId}");
        if (!$state) {
            return response()->json(['message' => 'Job no encontrado'], 404);
        }
        return response()->json($state);
    }

    public function create()
    {
        // return view('sat_efos_69b.create');
        return response()->json(['message' => 'Formulario create pendiente'], 200);
    }

    public function store(Request $request)
    {
        // Pendiente: validación/creación
        return response()->json(['message' => 'Store pendiente'], 501);
    }

    public function show(SatEfos69b $satEfos69b)
    {
        // return view('sat_efos_69b.show', compact('satEfos69b'));
        return response()->json(['message' => 'Show pendiente'], 200);
    }

    public function edit(SatEfos69b $satEfos69b)
    {
        // return view('sat_efos_69b.edit', compact('satEfos69b'));
        return response()->json(['message' => 'Edit pendiente'], 200);
    }

    public function update(Request $request, SatEfos69b $satEfos69b)
    {
        // Pendiente: validación/actualización
        return response()->json(['message' => 'Update pendiente'], 501);
    }

    public function destroy(SatEfos69b $satEfos69b)
    {
        // Pendiente: eliminación
        return response()->json(['message' => 'Destroy pendiente'], 501);
    }
}
