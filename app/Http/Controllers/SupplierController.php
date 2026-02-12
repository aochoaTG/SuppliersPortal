<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;


class SupplierController extends Controller
{
    public function edit(User $user)
    {
        // retorna SOLO el contenido del modal (la vista de arriba)
        return view('users.staff.partials.supplier_form', compact('user'));
    }

    public function store(Request $request, User $user)
    {
        $data = $this->validateData($request);
        $data['user_id'] = $user->id;

        // specialized_services_types puede venir como JSON string
        if (isset($data['specialized_services_types']) && is_string($data['specialized_services_types'])) {
            $data['specialized_services_types'] = json_decode($data['specialized_services_types'], true) ?? [];
        }

        Supplier::create($data);

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, User $user)
    {
        $supplier = $user->supplier;
        abort_unless($supplier, 404);

        $data = $this->validateData($request);

        if (isset($data['specialized_services_types']) && is_string($data['specialized_services_types'])) {
            $data['specialized_services_types'] = json_decode($data['specialized_services_types'], true) ?? [];
        }

        $supplier->update($data);

        return response()->json(['ok' => true]);
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:13'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'supplier_type' => ['nullable', 'string', 'max:100'],
            'tax_regime' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'clabe' => ['nullable', 'string', 'max:18'],
            'currency' => ['nullable', 'in:MXN,USD,EUR'],
            'swift_bic' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:50'],
            'bank_address' => ['nullable', 'string', 'max:255'],
            'aba_routing' => ['nullable', 'string', 'max:50'],
            'us_bank_name' => ['nullable', 'string', 'max:150'],
            'provides_specialized_services' => ['nullable', 'boolean'],
            'repse_registration_number' => ['nullable', 'string', 'max:100'],
            'repse_expiry_date' => ['nullable', 'date'],
            'specialized_services_types' => ['nullable'], // JSON string o array
            'economic_activity' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /**
     * Buscar proveedores activos excluyendo EFOS 69-B (Definitivo/Presunto).
     * Respuesta compatible con Select2: { results: [{id, text}], pagination: { more: bool } }
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;

        $paginator = \App\Models\Supplier::query()
            // ->active() // si aplica
            ->notEfos69b()
            ->search($term)                 // usa scope corregido (abajo)
            ->orderBy('company_name')       // 👈 aquí
            ->simplePaginate($perPage, ['id', 'company_name', 'rfc'], 'page', $page);

        $results = collect($paginator->items())->map(function ($s) {
            $rfc = $s->rfc ? " ({$s->rfc})" : '';
            return [
                'id' => $s->id,
                'text' => \Illuminate\Support\Str::limit($s->company_name, 80) . $rfc, // 👈 aquí
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }
}
