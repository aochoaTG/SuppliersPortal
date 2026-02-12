<?php

// app/Http/Controllers/IncidentController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Incident;

class IncidentController extends Controller
{
    function index(Request $request) {
        $incidents = Incident::all();
        return view('incidents.index', compact('incidents'));
    }
    
    public function store(StoreIncidentRequest $request)
    {
        // Crear el incidente sin la imagen
        $incident = Incident::create($request->safe()->except('image') + [
            'user_id' => auth()->id(),
            'status'  => 'nuevo',
        ]);

        // Si el usuario subió imagen, guardarla
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Guardar en storage/app/public/incidents/{id}/
            $path = $file->store("incidents/{$incident->id}", 'public');

            $incident->update([
                'image_path' => $path, // Solo guardamos la ruta
            ]);
        }

        return back()->with('status', 'Gracias. Recibimos tu reporte.');
    }

    function destroy(Request $request, Incident $incident) {
        $incident->delete();
        return back()->with('status', 'Incidente eliminado correctamente.');
    }
}