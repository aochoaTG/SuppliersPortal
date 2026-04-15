<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Recibe datos de un empleado desde el script Python y los persiste.
     * POST /api/empleados/recibir
     */
    public function recibir(Request $request): JsonResponse
    {
        $request->validate([
            'Nombre' => 'required|string|max:255',
        ]);

        $data = $request->all();

        $employee = Employee::updateOrCreate(
            [
                'company'         => $this->str($data, 'Empresa'),
                'employee_number' => $this->str($data, 'Numero'),
            ],
            [
                'archivo_origen'      => $this->str($data, 'archivo_origen'),
                'full_name'           => trim($data['Nombre']),
                'department'          => $this->str($data, 'Departamento'),
                'job_title'           => $this->str($data, 'Puesto'),
                'hire_date'           => $this->date($data, 'FechaIngreso'),
                'is_active'           => $this->str($data, 'Activo'),
                'termination_date'    => $this->date($data, 'FechaBaja'),
                'rehire_eligible'     => $this->str($data, 'Recontratar'),
                'termination_reason'  => $this->str($data, 'MotivoBaja'),
                'team'                => $this->str($data, 'Equipo'),
                'seniority'           => $this->str($data, 'Antiguedad'),
                'rfc'                 => $this->str($data, 'RFC'),
                'imss'                => $this->str($data, 'IMSS'),
                'curp'                => $this->str($data, 'CURP'),
                'gender'              => $this->str($data, 'Genero'),
                'phone'               => $this->str($data, 'Telefono'),
                'address'             => $this->str($data, 'Direccion'),
                'email'               => $this->str($data, 'Correo'),
                'education'           => $this->str($data, 'Estudios'),
                'responsible'         => $this->str($data, 'Responsable'),
                'leader'              => $this->str($data, 'Lider'),
                'vacation_balance'    => $this->decimal($data, 'SaldoVacaciones'),
                'savings_fund'        => $this->decimal($data, 'FondoAhorro'),
                'daily_salary'        => $this->decimal($data, 'SalarioDiario'),
                'severance_bonus'     => $this->decimal($data, 'Grat.Separacion'),
                'indemnization'       => $this->decimal($data, 'Indemnizacion'),
                'seniority_premium'   => $this->decimal($data, 'PrimaDeAntig.'),
            ]
        );

        $status = $employee->wasRecentlyCreated ? 201 : 200;
        $message = $employee->wasRecentlyCreated ? 'Empleado creado' : 'Empleado actualizado';

        return response()->json([
            'success' => true,
            'message' => $message,
            'id'      => $employee->id,
        ], $status);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function str(array $data, string $key): ?string
    {
        $value = trim($data[$key] ?? '');
        return $value !== '' ? $value : null;
    }

    private function date(array $data, string $key): ?string
    {
        $value = trim($data[$key] ?? '');
        if ($value === '') {
            return null;
        }
        // Esperamos formato YYYY-MM-DD; devolvemos null si no es válida
        $parsed = \DateTime::createFromFormat('Y-m-d', $value);
        return ($parsed && $parsed->format('Y-m-d') === $value) ? $value : null;
    }

    private function decimal(array $data, string $key): ?string
    {
        $value = trim($data[$key] ?? '');
        return is_numeric($value) ? $value : null;
    }
}
