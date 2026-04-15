<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Campos rastreados para eventos, en orden.
     * clave => etiqueta legible en español
     */
    private const CAMPOS_RASTREADOS = [
        'archivo_origen'     => 'archivo de origen',
        'full_name'          => 'nombre completo',
        'department'         => 'departamento',
        'job_title'          => 'puesto',
        'hire_date'          => 'fecha de ingreso',
        'is_active'          => 'estado activo',
        'termination_date'   => 'fecha de baja',
        'rehire_eligible'    => 'recontratable',
        'termination_reason' => 'motivo de baja',
        'team'               => 'equipo',
        'seniority'          => 'antigüedad',
        'rfc'                => 'RFC',
        'imss'               => 'IMSS',
        'curp'               => 'CURP',
        'gender'             => 'género',
        'phone'              => 'teléfono',
        'address'            => 'dirección',
        'email'              => 'correo electrónico',
        'education'          => 'estudios',
        'company'            => 'empresa',
        'responsible'        => 'responsable',
        'leader'             => 'líder',
        'vacation_balance'   => 'saldo de vacaciones',
        'savings_fund'       => 'fondo de ahorro',
        'daily_salary'       => 'salario diario',
        'severance_bonus'    => 'gratificación por separación',
        'indemnization'      => 'indemnización',
        'seniority_premium'  => 'prima de antigüedad',
    ];

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

        $numero  = $this->str($data, 'Numero');
        $empresa = $this->str($data, 'Empresa');
        $rfc     = $this->str($data, 'RFC');

        // Buscar explícitamente por los tres campos clave
        $employee = Employee::where('employee_number', $numero)
            ->where('company', $empresa)
            ->where('rfc', $rfc)
            ->first();

        $esNuevo = $employee === null;

        if ($esNuevo) {
            $employee = new Employee([
                'employee_number' => $numero,
                'company'         => $empresa,
            ]);
        }

        // Capturar valores RAW antes de cualquier cambio
        $valoresAnteriores = $esNuevo ? [] : $employee->getRawOriginal();

        $employee->fill([
            'archivo_origen'     => $this->str($data, 'archivo_origen'),
            'full_name'          => trim($data['Nombre']),
            'department'         => $this->str($data, 'Departamento'),
            'job_title'          => $this->str($data, 'Puesto'),
            'hire_date'          => $this->date($data, 'FechaIngreso'),
            'is_active'          => $this->str($data, 'Activo'),
            'termination_date'   => $this->date($data, 'FechaBaja'),
            'rehire_eligible'    => $this->str($data, 'Recontratar'),
            'termination_reason' => $this->str($data, 'MotivoBaja'),
            'team'               => $this->str($data, 'Equipo'),
            'seniority'          => $this->str($data, 'Antiguedad'),
            'rfc'                => $rfc,
            'imss'               => $this->str($data, 'IMSS'),
            'curp'               => $this->str($data, 'CURP'),
            'gender'             => $this->str($data, 'Genero'),
            'phone'              => $this->str($data, 'Telefono'),
            'address'            => $this->str($data, 'Direccion'),
            'email'              => $this->str($data, 'Correo'),
            'education'          => $this->str($data, 'Estudios'),
            'responsible'        => $this->str($data, 'Responsable'),
            'leader'             => $this->str($data, 'Lider'),
            'vacation_balance'   => $this->decimal($data, 'SaldoVacaciones'),
            'savings_fund'       => $this->decimal($data, 'FondoAhorro'),
            'daily_salary'       => $this->decimal($data, 'SalarioDiario'),
            'severance_bonus'    => $this->decimal($data, 'Grat.Separacion'),
            'indemnization'      => $this->decimal($data, 'Indemnizacion'),
            'seniority_premium'  => $this->decimal($data, 'PrimaDeAntig.'),
        ]);

        $employee->save();

        $eventos = 0;

        if (!$esNuevo) {
            $eventos = $this->registrarCambios($valoresAnteriores, $employee);
        }

        return response()->json([
            'success' => true,
            'message' => $esNuevo ? 'Empleado creado' : 'Empleado actualizado',
            'id'      => $employee->id,
            'eventos' => $eventos,
        ], $esNuevo ? 201 : 200);
    }

    // ── Lógica de eventos ─────────────────────────────────────────────────────

    /**
     * Compara los valores RAW anteriores contra los guardados
     * e inserta un EmployeeEvent por cada campo que haya cambiado.
     *
     * @return int Número de eventos registrados
     */
    private function registrarCambios(array $antes, Employee $despues): int
    {
        $eventos = [];

        foreach (self::CAMPOS_RASTREADOS as $campo => $etiqueta) {
            $valorAntes   = $this->normalizar($antes[$campo] ?? null);
            $valorDespues = $this->normalizar($despues->getRawOriginal($campo));

            if ($valorAntes === $valorDespues) {
                continue;
            }

            $eventos[] = [
                'employee_id'    => $despues->id,
                'campo'          => $campo,
                'evento'         => "Se actualizó el campo '{$etiqueta}' del empleado",
                'valor_anterior' => $valorAntes,
                'valor_nuevo'    => $valorDespues,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        if (!empty($eventos)) {
            EmployeeEvent::insert($eventos);
        }

        return count($eventos);
    }

    /**
     * Convierte cualquier valor a string normalizado para comparación.
     * Fechas Carbon se estandarizan a Y-m-d; nulls y strings vacíos → null.
     */
    private function normalizar(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }

    // ── Helpers de parseo ─────────────────────────────────────────────────────

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
        $parsed = \DateTime::createFromFormat('Y-m-d', $value);
        return ($parsed && $parsed->format('Y-m-d') === $value) ? $value : null;
    }

    private function decimal(array $data, string $key): ?string
    {
        $value = trim($data[$key] ?? '');
        return is_numeric($value) ? $value : null;
    }
}
