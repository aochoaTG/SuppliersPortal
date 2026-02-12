<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterSupplierRequest;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierRegistrationController extends Controller
{
    public function create()
    {
        return view('auth.supplier-register');
    }

    public function store(RegisterSupplierRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // 1) Crear usuario
            $user = User::create([
                'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => $data['password'], // se hashea por cast "hashed"
                'is_active'  => true,
            ]);

            // (Opcional) asignar rol "supplier"
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('supplier');
            }

            // 2) Preparar datos REPSE
            $repseData = $this->prepareRepseData($data);

            // 3) Crear supplier (incluyendo campos REPSE)
            Supplier::create([
                'user_id'       => $user->id,
                'company_name'  => $data['company_name'],
                'rfc'           => strtoupper($data['rfc']),
                'address'       => $data['address'],
                'phone_number'  => $data['phone_number'],
                'email'         => $user->email,
                'contact_person' => $data['contact_person'],
                'contact_phone' => $data['contact_phone'] ?? null,
                'supplier_type' => $data['supplier_type'],
                'tax_regime'    => $data['tax_regime'],

                // Nuevos campos REPSE
                'provides_specialized_services' => $repseData['provides_specialized_services'],
                'repse_registration_number'     => $repseData['repse_registration_number'],
                'repse_expiry_date'            => $repseData['repse_expiry_date'],
                'specialized_services_types'   => $repseData['specialized_services_types'],

                // Bancarios: null hasta activación
                'bank_name'     => null,
                'account_number' => null,
                'clabe'         => null,
                'currency'      => null,
            ]);

            // 4) Login + verificación de correo
            Auth::login($user);

            // 5) Mensaje personalizado según si requiere REPSE
            $message = $this->getSuccessMessage($repseData['provides_specialized_services']);

            return redirect()->route('dashboard')->with('status', $message);
        });
    }

    /**
     * Preparar y limpiar datos REPSE
     */
    private function prepareRepseData(array $data): array
    {
        $providesSpecializedServices = ($data['provides_specialized_services'] ?? 0) == 1;

        // Si no presta servicios especializados, limpiar todos los campos REPSE
        if (!$providesSpecializedServices) {
            return [
                'provides_specialized_services' => false,
                'repse_registration_number'     => null,
                'repse_expiry_date'            => null,
                'specialized_services_types'   => null,
            ];
        }

        // Si presta servicios especializados, procesar los datos
        $specializedServices = $data['specialized_services_types'] ?? [];

        // Si seleccionó "otros", agregar la descripción
        if (in_array('otros', $specializedServices) && !empty($data['otros_descripcion'])) {
            // Reemplazar "otros" con la descripción personalizada
            $key = array_search('otros', $specializedServices);
            if ($key !== false) {
                $specializedServices[$key] = 'otros: ' . trim($data['otros_descripcion']);
            }
        }

        return [
            'provides_specialized_services' => true,
            'repse_registration_number'     => $this->formatRepseNumber($data['repse_registration_number'] ?? ''),
            'repse_expiry_date'            => $data['repse_expiry_date'] ?? null,
            'specialized_services_types'   => !empty($specializedServices) ? $specializedServices : null,
        ];
    }

    /**
     * Formatear número REPSE (agregar prefijo si no existe)
     */
    private function formatRepseNumber(string $number): ?string
    {
        if (empty($number)) {
            return null;
        }

        $number = strtoupper(trim($number));

        // Si no empieza con "REPSE-", agregarlo
        if (!str_starts_with($number, 'REPSE-')) {
            $number = 'REPSE-' . $number;
        }

        return $number;
    }

    /**
     * Generar mensaje de éxito personalizado
     */
    private function getSuccessMessage(bool $providesSpecializedServices): string
    {
        $baseMessage = 'Cuenta creada exitosamente. Por favor, carga tus documentos en la sección `Documentación` en el menú lateral.';

        if ($providesSpecializedServices) {
            $baseMessage .= ' Como proveedor de servicios especializados, asegúrate de subir también tu certificado REPSE.';
        }

        return $baseMessage;
    }
}
