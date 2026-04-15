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
        'first_name'         => 'nombre',
        'last_name'          => 'apellidos',
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

        // Estrategia de búsqueda:
        // 1. Si tiene número y empresa → buscar por ambos (caso normal)
        // 2. Si falta alguno pero tiene RFC → buscar solo por RFC
        // 3. Sin ningún identificador → siempre crear (no se puede deduplicar)
        if ($numero !== null && $empresa !== null) {
            $employee = Employee::where('employee_number', $numero)
                ->where('company', $empresa)
                ->first();
        } elseif ($rfc !== null) {
            $employee = Employee::where('rfc', $rfc)->first();
        } else {
            $employee = null;
        }

        $esNuevo = $employee === null;

        if ($esNuevo) {
            $employee = new Employee([
                'employee_number' => $numero,
                'company'         => $empresa,
            ]);
        }

        // Capturar valores RAW antes de cualquier cambio
        $valoresAnteriores = $esNuevo ? [] : $employee->getRawOriginal();

        ['first_name' => $firstName, 'last_name' => $lastName] =
            $this->parsearNombre($this->str($data, 'Nombre'));

        $rawLider = $this->str($data, 'Lider');

        $employee->fill([
            'archivo_origen'     => $this->str($data, 'archivo_origen'),
            'first_name'         => $firstName,
            'last_name'          => $lastName,
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
            'leader'             => $this->resolverLider($this->limpiarLider($rawLider), $rawLider),
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
     * Campos que NO se comparan para detectar cambios.
     */
    private const CAMPOS_EXCLUIDOS_COMPARACION = [
        'hire_date',
        'savings_fund',
        'seniority_premium',
    ];

    /**
     * Campos de fecha que deben normalizarse a Y-m-dd para comparación.
     */
    private const CAMPOS_FECHA = [
        'hire_date',
        'termination_date',
    ];

    /**
     * Campos decimales que deben normalizarse para comparación.
     */
    private const CAMPOS_DECIMALES = [
        'daily_salary',
        'severance_bonus',
        'indemnization',
        'seniority_premium',
    ];

    /**
     * Campos que se comparan como números enteros.
     */
    private const CAMPOS_ENTEROS = [
        'vacation_balance',
    ];

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
            // Saltar campos excluidos de la comparación
            if (in_array($campo, self::CAMPOS_EXCLUIDOS_COMPARACION, true)) {
                continue;
            }

            $valorAntes   = $antes[$campo] ?? null;
            $valorDespues = $despues->getRawOriginal($campo);

            // Normalizar según tipo de campo
            if (in_array($campo, self::CAMPOS_FECHA, true)) {
                $valorAntes   = $this->normalizarFecha($valorAntes);
                $valorDespues = $this->normalizarFecha($valorDespues);
            } elseif (in_array($campo, self::CAMPOS_DECIMALES, true)) {
                $valorAntes   = $this->normalizarDecimal($valorAntes);
                $valorDespues = $this->normalizarDecimal($valorDespues);
            } elseif (in_array($campo, self::CAMPOS_ENTEROS, true)) {
                $valorAntes   = $this->normalizarEntero($valorAntes);
                $valorDespues = $this->normalizarEntero($valorDespues);
            } else {
                $valorAntes   = $this->normalizar($valorAntes);
                $valorDespues = $this->normalizar($valorDespues);
            }

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

    /**
     * Normaliza fechas a formato Y-m-d para evitar falsos positivos
     * por diferencias de hora o formato (ej: "2024-01-15" vs "2024-01-15 00:00:00").
     */
    private function normalizarFecha(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Carbon instance
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        // String con fecha (puede incluir hora)
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
            // Intentar parsear y extraer solo la fecha
            try {
                $dt = new \DateTime($value);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {
                return $value;
            }
        }

        return (string) $value;
    }

    /**
     * Normaliza decimales para evitar falsos positivos por diferencia de precisión.
     * Ej: "1000.50" vs "1000.50000" → ambos se convierten a "1000.50"
     */
    private function normalizarDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = trim((string) $value);
        if ($str === '' || !is_numeric($str)) {
            return $str;
        }

        // Formatear con 2 decimales para estandarizar
        return number_format((float) $str, 2, '.', '');
    }

    /**
     * Normaliza valores enteros para evitar falsos positivos.
     * Ej: "9" vs "9.0000" → ambos se convierten a "9".
     */
    private function normalizarEntero(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = trim((string) $value);
        if ($str === '' || !is_numeric($str)) {
            return $str;
        }

        return (string) (int) round((float) $str);
    }

    // ── Helpers de parseo ─────────────────────────────────────────────────────

    /**
     * Divide el nombre del empleado en first_name y last_name.
     *
     * - Con coma ("Apellidos, Nombre(s)"): separación exacta.
     * - Sin coma (ya en orden natural): primer token = first_name, resto = last_name.
     *
     * @return array{first_name: string|null, last_name: string|null}
     */
    private function parsearNombre(?string $valor): array
    {
        if ($valor === null || trim($valor) === '') {
            return ['first_name' => null, 'last_name' => null];
        }

        $valor = trim($valor);

        if (str_contains($valor, ',')) {
            [$apellidos, $nombres] = explode(',', $valor, 2);
            return [
                'first_name' => trim($nombres) ?: null,
                'last_name'  => trim($apellidos) ?: null,
            ];
        }

        // Sin coma: primer token = nombre(s), resto = apellidos
        $partes = preg_split('/\s+/', $valor, 2);
        return [
            'first_name' => $partes[0] ?? null,
            'last_name'  => isset($partes[1]) ? $partes[1] : null,
        ];
    }

    /**
     * Convierte "Apellidos, Nombre" → "Nombre Apellidos".
     * Si no hay coma, devuelve el valor sin cambios.
     */
    private function reordenarNombre(string $nombre): string
    {
        if (!str_contains($nombre, ',')) {
            return $nombre;
        }

        [$apellidos, $nombres] = explode(',', $nombre, 2);
        return trim($nombres) . ' ' . trim($apellidos);
    }

    /**
     * Quita el prefijo tipo "9235 - " o "Jarudo - " al inicio del valor.
     * Reutilizado por limpiarLider y resolverLider.
     */
    private function quitarPrefijo(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        return str_contains($valor, ' - ')
            ? trim(substr($valor, strpos($valor, ' - ') + 3))
            : trim($valor);
    }

    /**
     * Limpia el nombre del líder recibido del archivo externo.
     *
     * - Elimina el prefijo de clave (e.g. "9235 - ", "Jarudo - ").
     * - Retorna null para valores sin información útil:
     *   vacío, "-", "no aplica", "desconocido", "vacante", "vacant", "sin jefe".
     * - Reordena "Apellidos, Nombre" → "Nombre Apellidos".
     */
    private function limpiarLider(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $nombre = $this->quitarPrefijo($valor) ?? '';

        $sinLider = ['no aplica', 'desconocido', 'vacante', 'vacant', 'sin jefe', '-', 'null'];

        if ($nombre === '' || in_array(mb_strtolower($nombre), $sinLider, true)) {
            return null;
        }

        return $this->reordenarNombre($nombre);
    }

    /**
     * Intenta resolver el nombre del líder a su número de empleado.
     *
     * Usa el valor crudo para extraer apellidos y nombre por separado
     * y hacer una búsqueda LIKE doble contra first_name / last_name.
     * Solo sustituye si encuentra exactamente un resultado (evita ambigüedad).
     * Si no hay match, devuelve el nombre limpio como fallback.
     *
     * @param string|null $nombreLimpio  Nombre ya limpio y reordenado (fallback)
     * @param string|null $rawLider      Valor original del campo Lider
     */
    private function resolverLider(?string $nombreLimpio, ?string $rawLider): ?string
    {
        if ($nombreLimpio === null || $rawLider === null) {
            return $nombreLimpio;
        }

        // Quitar prefijo para obtener "Apellidos, Nombre" o "Nombre Apellidos"
        $sinPrefijo = $this->quitarPrefijo($rawLider) ?? '';

        if ($sinPrefijo === '') {
            return $nombreLimpio;
        }

        // LIKE con el fragmento disponible (puede estar truncado en el origen)
        $query = Employee::whereNotNull('employee_number');

        if (str_contains($sinPrefijo, ',')) {
            // Formato origen "Apellidos, Nombre" → split exacto, LIKE por separado
            [$apellidos, $nombres] = explode(',', $sinPrefijo, 2);
            $apellidos = trim($apellidos);
            $nombres   = trim($nombres);

            if ($nombres !== '') {
                $query->where('first_name', 'like', $nombres . '%');
            }
            if ($apellidos !== '') {
                $query->where('last_name', 'like', $apellidos . '%');
            }
        } else {
            // Sin coma: el valor ya está en orden natural "Nombre Apellidos".
            // Buscar contra el nombre completo concatenado para evitar el problema
            // de split asimétrico (e.g. "Vicente Alejandro Carril" no parte igual
            // que first_name="Vicente Alejandro" / last_name="Carrillo").
            $query->whereRaw(
                "(first_name + ' ' + ISNULL(last_name, '')) LIKE ?",
                [$sinPrefijo . '%']
            );
        }

        $numeros = $query->pluck('employee_number')->unique()->values();

        // Resuelve si todos los matches apuntan al mismo número de empleado
        // (la misma persona puede aparecer en varios archivos/empresas)
        return $numeros->count() === 1
            ? $numeros->first()
            : $nombreLimpio;
    }

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
