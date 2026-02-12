# Snippets Reutilizables - Backend Agent

## 🔥 Migraciones Comunes

### Tabla con Soft Deletes y Auditoría
```php
Schema::create('nombre_tabla', function (Blueprint $table) {
    $table->id();
    
    // Campos principales
    $table->string('campo', 255);
    
    // Foreign Keys
    $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
    
    // Timestamps y Soft Deletes
    $table->timestamps();
    $table->softDeletes();
    
    // Auditoría
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    
    // Índices
    $table->index('campo');
});
```

### Tabla Pivot con Datos Adicionales
```php
Schema::create('orden_producto', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_id')->constrained()->onDelete('cascade');
    $table->foreignId('producto_id')->constrained()->onDelete('cascade');
    
    // Datos adicionales en pivot
    $table->integer('cantidad');
    $table->decimal('precio_unitario', 10, 2);
    $table->decimal('subtotal', 10, 2);
    
    $table->timestamps();
    
    // Índice único para evitar duplicados
    $table->unique(['orden_id', 'producto_id']);
});
```

---

## 🎯 Modelos Eloquent

### Modelo con Relaciones Complejas
```php
class Orden extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['proveedor_id', 'fecha', 'total', 'estado'];
    
    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
    ];
    
    // 1:N
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    
    // N:M con pivot data
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_producto')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                    ->withTimestamps();
    }
    
    // Scope
    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }
    
    // Accessor
    public function getTotalFormateadoAttribute()
    {
        return '$' . number_format($this->total, 2);
    }
}
```

---

## 📝 Form Requests

### Request con Validación Condicional
```php
public function rules(): array
{
    return [
        'tipo' => 'required|in:fisica,moral',
        'nombre' => 'required|string|max:255',
        
        // Condicional: Si tipo = 'moral', requiere RFC
        'rfc' => [
            Rule::requiredIf($this->tipo === 'moral'),
            'nullable',
            'size:13',
        ],
        
        // Validar array
        'productos' => 'required|array|min:1',
        'productos.*.id' => 'required|exists:productos,id',
        'productos.*.cantidad' => 'required|integer|min:1',
    ];
}

protected function prepareForValidation(): void
{
    // Calcular total automáticamente
    if ($this->has('productos')) {
        $total = collect($this->productos)->sum(function ($p) {
            return $p['cantidad'] * $p['precio'];
        });
        
        $this->merge(['total' => $total]);
    }
}
```

---

## 🎛️ Controllers

### Controller con Service Layer
```php
class OrdenController extends Controller
{
    protected $ordenService;
    
    public function __construct(OrdenService $ordenService)
    {
        $this->ordenService = $ordenService;
    }
    
    public function store(StoreOrdenRequest $request): JsonResponse
    {
        try {
            $orden = $this->ordenService->crear($request->validated());
            
            return response()->json([
                'message' => 'Orden creada exitosamente',
                'data' => $orden
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (Exception $e) {
            Log::error('Error al crear orden', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al crear la orden'
            ], 500);
        }
    }
}
```

### Controller con Acciones Custom
```php
/**
 * Aprobar orden
 */
public function aprobar(Orden $orden): JsonResponse
{
    try {
        // Validar estado
        if ($orden->estado !== 'Pendiente') {
            return response()->json([
                'message' => 'Solo se pueden aprobar órdenes pendientes'
            ], 400);
        }
        
        DB::beginTransaction();
        
        $orden->update([
            'estado' => 'Aprobada',
            'aprobado_por' => auth()->id(),
            'fecha_aprobacion' => now()
        ]);
        
        // Lógica adicional (ej: notificar, actualizar inventario)
        event(new OrdenAprobada($orden));
        
        DB::commit();
        
        return response()->json([
            'message' => 'Orden aprobada exitosamente',
            'data' => $orden->fresh()
        ]);
        
    } catch (Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'message' => 'Error al aprobar la orden'
        ], 500);
    }
}
```

---

## 📊 DataTable Avanzado

### DataTable con Filtros Custom
```php
private function dataTable(Request $request): JsonResponse
{
    $query = Proveedor::query();
    
    // Filtro por estado
    if ($request->filled('estado')) {
        $query->where('activo', $request->estado === 'activo');
    }
    
    // Filtro por fecha
    if ($request->filled('fecha_desde')) {
        $query->whereDate('created_at', '>=', $request->fecha_desde);
    }
    
    return DataTables::of($query)
        ->filter(function ($query) use ($request) {
            // Búsqueda global custom
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('rfc', 'like', "%{$search}%");
                });
            }
        })
        ->addColumn('acciones', function ($row) {
            return view('proveedores.partials.acciones', compact('row'))->render();
        })
        ->rawColumns(['acciones'])
        ->make(true);
}
```

---

## 🔐 Middleware Custom

### Middleware de Auditoría
```php
// app/Http/Middleware/AuditMiddleware.php
public function handle(Request $request, Closure $next)
{
    $response = $next($request);
    
    // Log de operaciones críticas
    if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
        Log::info('Operación realizada', [
            'usuario' => auth()->user()->name ?? 'Invitado',
            'metodo' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
    }
    
    return $response;

---

use Illuminate\Support\Facades\Mail;

## 📧 Notificaciones

### Enviar Email tras Crear Orden
```php
use Illuminate\Support\Facades\Mail;
use App\Mail\OrdenCreada;

public function store(StoreOrdenRequest $request): JsonResponse
{
    $orden = Orden::create($request->validated());
    
    // Enviar email
    Mail::to($orden->proveedor->email)->send(new OrdenCreada($orden));
    
    return response()->json(['message' => 'Orden creada y email enviado']);
}
```
