# Protocolo de Ejecución - Backend Agent

## Fase 1: Análisis y Diseño de Base de Datos

**Objetivo**: Diseñar estructura de tablas con relaciones correctas.

### 1.1 Identificar Entidades

**Ejemplo**: Sistema de Proveedores

| Entidad | Tabla | Campos Clave |
|---------|-------|--------------|
| Proveedor | proveedores | id, nombre, rfc, contacto, activo |
| Producto | productos | id, proveedor_id, nombre, precio |
| Compra | compras | id, proveedor_id, fecha, total |

### 1.2 Definir Relaciones
```
Proveedor (1) ──< (N) Productos
Proveedor (1) ──< (N) Compras
```

### 1.3 Índices Necesarios
```sql
-- Para búsquedas
INDEX idx_proveedores_nombre ON proveedores(nombre)
INDEX idx_proveedores_rfc ON proveedores(rfc)

-- Para foreign keys (SQL Server auto-crea, pero verifico)
INDEX idx_productos_proveedor ON productos(proveedor_id)
```

---

## Fase 2: Crear Migración

**Objetivo**: Esquema de BD con convenciones Laravel + SQL Server.

### Comando Artisan
```bash
php artisan make:migration create_proveedores_table
```

### Archivo: `database/migrations/YYYY_MM_DD_HHMMSS_create_proveedores_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('rfc', 13)->unique();
            $table->string('contacto', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Si se requiere soft delete
            
            // Índices
            $table->index('nombre');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
```

### Convenciones SQL Server
```php
// SQL Server usa [dbo].[tabla] por defecto
// Laravel maneja esto automáticamente con driver sqlsrv

// Para tipos especiales SQL Server:
$table->char('codigo', 10);           // char(10)
$table->decimal('precio', 10, 2);     // decimal(10,2)
$table->datetime('fecha_entrega');    // datetime2(7) en SQL Server
```

### Migración con Foreign Keys
```php
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
    $table->string('nombre', 255);
    $table->string('sku', 50)->unique();
    $table->decimal('precio', 10, 2);
    $table->boolean('activo')->default(true);
    $table->timestamps();
    
    $table->index('nombre');
    $table->index(['proveedor_id', 'activo']); // Índice compuesto
});
```

### Ejecutar Migración
```bash
php artisan migrate
# Verifico en SQL Server Management Studio
```

---

## Fase 3: Crear Modelo Eloquent

**Objetivo**: Modelo con relaciones, accessors, scopes.

### Comando Artisan
```bash
php artisan make:model Proveedor
```

### Archivo: `app/Models/Proveedor.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tabla asociada al modelo
     */
    protected $table = 'proveedores';

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'nombre',
        'rfc',
        'contacto',
        'telefono',
        'direccion',
        'email',
        'activo',
    ];

    /**
     * Atributos que deben ser cast
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Atributos ocultos para serialización
     */
    protected $hidden = [
        // Campos sensibles si los hubiera
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    /**
     * Productos del proveedor
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Compras realizadas a este proveedor
     */
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope para filtrar proveedores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para buscar por nombre o RFC
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('rfc', 'like', "%{$termino}%");
        });
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Formatear RFC en mayúsculas
     */
    protected function rfc(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper($value),
        );
    }

    /**
     * Obtener nombre completo con RFC
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} ({$this->rfc})";
    }

    // ==========================================
    // MÉTODOS AUXILIARES
    // ==========================================

    /**
     * Verificar si tiene productos asociados
     */
    public function tieneProductos(): bool
    {
        return $this->productos()->exists();
    }

    /**
     * Desactivar proveedor (soft)
     */
    public function desactivar(): bool
    {
        return $this->update(['activo' => false]);
    }
}
```

---

## Fase 4: Form Requests (Validación)

**Objetivo**: Validación robusta y centralizada.

### Comando Artisan
```bash
php artisan make:request StoreProveedorRequest
php artisan make:request UpdateProveedorRequest
```

### Archivo: `app/Http/Requests/StoreProveedorRequest.php`
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProveedorRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado
     */
    public function authorize(): bool
    {
        // return $this->user()->can('crear_proveedores');
        return true; // Por ahora permitir todos
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:proveedores,nombre',
            ],
            'rfc' => [
                'required',
                'string',
                'size:13',
                'regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/',
                'unique:proveedores,rfc',
            ],
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'activo' => 'required|boolean',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio',
            'nombre.unique' => 'Ya existe un proveedor con ese nombre',
            'rfc.required' => 'El RFC es obligatorio',
            'rfc.size' => 'El RFC debe tener 13 caracteres',
            'rfc.regex' => 'Formato de RFC inválido',
            'rfc.unique' => 'Ya existe un proveedor con ese RFC',
            'email.email' => 'El email no tiene un formato válido',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso',
        ];
    }

    /**
     * Preparar datos antes de validación
     */
    protected function prepareForValidation(): void
    {
        // Convertir RFC a mayúsculas antes de validar
        if ($this->rfc) {
            $this->merge([
                'rfc' => strtoupper($this->rfc),
            ]);
        }

        // Convertir checkbox activo a boolean
        $this->merge([
            'activo' => $this->boolean('activo', true),
        ]);
    }
}
```

### Archivo: `app/Http/Requests/UpdateProveedorRequest.php`
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $proveedorId = $this->route('proveedor'); // ID desde la ruta

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('proveedores', 'nombre')->ignore($proveedorId),
            ],
            'rfc' => [
                'required',
                'string',
                'size:13',
                'regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/',
                Rule::unique('proveedores', 'rfc')->ignore($proveedorId),
            ],
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'activo' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio',
            'nombre.unique' => 'Ya existe un proveedor con ese nombre',
            'rfc.required' => 'El RFC es obligatorio',
            'rfc.regex' => 'Formato de RFC inválido',
            'rfc.unique' => 'Ya existe un proveedor con ese RFC',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->rfc) {
            $this->merge(['rfc' => strtoupper($this->rfc)]);
        }
        
        $this->merge(['activo' => $this->boolean('activo', true)]);
    }
}
```

---

## Fase 5: Controller con DataTable

**Objetivo**: Implementar CRUD completo con Yajra DataTables.

### Comando Artisan
```bash
php artisan make:controller ProveedorController --resource
```

### Archivo: `app/Http/Controllers/ProveedorController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource (Vista Index + DataTable AJAX)
     */
    public function index(Request $request)
    {
        // Si es petición AJAX (DataTable), devolver JSON
        if ($request->ajax()) {
            return $this->dataTable();
        }

        // Si no, renderizar la vista
        return view('proveedores.index');
    }

    /**
     * DataTable server-side processing
     */
    private function dataTable(): JsonResponse
    {
        $proveedores = Proveedor::select([
            'id',
            'nombre',
            'rfc',
            'contacto',
            'telefono',
            'activo',
            'created_at'
        ]);

        return DataTables::of($proveedores)
            ->addColumn('activo', function ($proveedor) {
                return $proveedor->activo
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>';
            })
            ->addColumn('acciones', function ($proveedor) {
                $btnEditar = '<button type="button" class="btn btn-sm btn-warning btn-editar" 
                    data-id="' . $proveedor->id . '" title="Editar">
                    <i class="ti ti-pencil"></i>
                </button>';

                $btnEliminar = '<button type="button" class="btn btn-sm btn-danger btn-eliminar" 
                    data-id="' . $proveedor->id . '" 
                    data-nombre="' . htmlspecialchars($proveedor->nombre) . '" 
                    title="Eliminar">
                    <i class="ti ti-trash"></i>
                </button>';

                return '<div class="btn-group" role="group">' . $btnEditar . ' ' . $btnEliminar . '</div>';
            })
            ->rawColumns(['activo', 'acciones'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource
     * (No usado en TotalGas, usamos modales)
     */
    public function create(): View
    {
        return view('proveedores.create');
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(StoreProveedorRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $proveedor = Proveedor::create($request->validated());

            DB::commit();

            Log::info('Proveedor creado', [
                'id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
                'usuario' => auth()->user()->name ?? 'Sistema'
            ]);

            return response()->json([
                'message' => 'Proveedor creado exitosamente',
                'data' => $proveedor
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear proveedor', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error al crear el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource
     */
    public function show(Proveedor $proveedor): JsonResponse
    {
        return response()->json($proveedor);
    }

    /**
     * Show the form for editing the specified resource
     * (No usado, modal maneja esto)
     */
    public function edit(Proveedor $proveedor): View
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    /**
     * Update the specified resource in storage
     */
    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $proveedor->update($request->validated());

            DB::commit();

            Log::info('Proveedor actualizado', [
                'id' => $proveedor->id,
                'cambios' => $proveedor->getChanges(),
                'usuario' => auth()->user()->name ?? 'Sistema'
            ]);

            return response()->json([
                'message' => 'Proveedor actualizado exitosamente',
                'data' => $proveedor
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar proveedor', [
                'id' => $proveedor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage
     */
    public function destroy(Proveedor $proveedor): JsonResponse
    {
        try {
            // Verificar si tiene relaciones que impidan eliminación
            if ($proveedor->tieneProductos()) {
                return response()->json([
                    'message' => 'No se puede eliminar el proveedor porque tiene productos asociados'
                ], 409); // 409 Conflict
            }

            DB::beginTransaction();

            $nombreProveedor = $proveedor->nombre;
            $proveedor->delete(); // Soft delete si está configurado

            DB::commit();

            Log::info('Proveedor eliminado', [
                'id' => $proveedor->id,
                'nombre' => $nombreProveedor,
                'usuario' => auth()->user()->name ?? 'Sistema'
            ]);

            return response()->json([
                'message' => 'Proveedor eliminado exitosamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar proveedor', [
                'id' => $proveedor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar el proveedor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
```

---

## Fase 6: Registrar Rutas

**Objetivo**: Exponer endpoints en `routes/web.php`.

### Archivo: `routes/web.php`
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProveedorController;

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    
    // CRUD de Proveedores
    Route::resource('proveedores', ProveedorController::class);
    
    // Rutas adicionales si las hay
    // Route::post('proveedores/{proveedor}/activar', [ProveedorController::class, 'activar'])
    //     ->name('proveedores.activar');
});
```

### Rutas Generadas (Resource)
```bash
php artisan route:list --name=proveedores

# Genera:
GET    /proveedores              → index    (Vista + DataTable AJAX)
POST   /proveedores              → store    (Crear)
GET    /proveedores/{id}         → show     (Ver uno)
PUT    /proveedores/{id}         → update   (Actualizar)
DELETE /proveedores/{id}         → destroy  (Eliminar)
```

---

## Fase 7: Pruebas

### 7.1 Testing Manual con Postman/Thunder Client
```http
### 1. Listar (DataTable)
GET http://localhost/proveedores?draw=1&start=0&length=10
Accept: application/json

### 2. Crear
POST http://localhost/proveedores
Content-Type: application/json

{
  "nombre": "Proveedor Test SA de CV",
  "rfc": "PTE210615ABC",
  "contacto": "Juan Pérez",
  "telefono": "6141234567",
  "activo": true
}

### 3. Ver uno
GET http://localhost/proveedores/1

### 4. Actualizar
PUT http://localhost/proveedores/1
Content-Type: application/json

{
  "nombre": "Proveedor Test EDITADO",
  "rfc": "PTE210615ABC",
  "contacto": "María López",
  "activo": true
}

### 5. Eliminar
DELETE http://localhost/proveedores/1
```

### 7.2 Verificar en SQL Server
```sql
-- Ver registros
SELECT * FROM proveedores;

-- Ver con soft deletes
SELECT * FROM proveedores WHERE deleted_at IS NULL;

-- Ver índices
EXEC sp_helpindex 'proveedores';

-- Ver foreign keys
SELECT 
    fk.name AS ForeignKey,
    tp.name AS ParentTable,
    tr.name AS ReferencedTable
FROM sys.foreign_keys AS fk
INNER JOIN sys.tables AS tp ON fk.parent_object_id = tp.object_id
INNER JOIN sys.tables AS tr ON fk.referenced_object_id = tr.object_id
WHERE tp.name = 'productos';
```

---

## Fase 8: Optimizaciones

### 8.1 Eager Loading (Evitar N+1)
```php
// En Controller
public function index(Request $request)
{
    if ($request->ajax()) {
        $proveedores = Proveedor::with('productos') // Cargar relación
            ->select(['id', 'nombre', 'rfc', 'contacto', 'activo']);
        
        return DataTables::of($proveedores)
            ->addColumn('total_productos', function ($proveedor) {
                return $proveedor->productos->count();
            })
            ->make(true);
    }
    
    return view('proveedores.index');
}
```

### 8.2 Cache para Queries Pesadas
```php
use Illuminate\Support\Facades\Cache;

public function getProveedoresActivos()
{
    return Cache::remember('proveedores_activos', 3600, function () {
        return Proveedor::activos()->get();
    });
}

// Limpiar cache cuando se actualiza
public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
{
    $proveedor->update($request->validated());
    Cache::forget('proveedores_activos'); // Invalidar cache
    
    return response()->json(['message' => 'Actualizado']);
}
```

### 8.3 Índices Compuestos para Búsquedas
```php
// En migración
$table->index(['activo', 'nombre']); // Para búsquedas filtradas por activo

// Query optimizada
Proveedor::where('activo', true)
    ->where('nombre', 'like', "%{$search}%")
    ->get(); // Usa índice compuesto
```

---

## Checklist Final Backend

- [ ] **Migración** ejecutada sin errores
- [ ] **Modelo** con fillable, casts, relaciones
- [ ] **Form Requests** con validaciones robustas
- [ ] **Controller** CRUD completo
- [ ] **DataTable** funciona (server-side)
- [ ] **Rutas** registradas en `web.php`
- [ ] **Logs** en operaciones críticas
- [ ] **Transacciones DB** en create/update/delete
- [ ] **Manejo de errores** con try-catch
- [ ] **Validación de relaciones** antes de eliminar
- [ ] **Testing manual** con Postman
- [ ] **SQL Server** registros visibles
- [ ] **No hay N+1 queries** (usar Debugbar)