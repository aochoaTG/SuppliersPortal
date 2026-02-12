# Contrato API: [Nombre del Módulo]

**Fecha**: YYYY-MM-DD  
**PM Agent**: [auto-generado]  
**Backend Agent**: [asignado]  
**Frontend Agent**: [asignado]

---

## 📌 Resumen

**Descripción**: [Breve descripción del módulo]  
**Endpoints**: X rutas  
**Prioridad**: Alta | Media | Baja

---

## 🎯 Endpoints

### 1. Listar [Recurso]

**Ruta**: `GET /[modulo]/[recurso]`  
**Middleware**: `auth`  
**Controller**: `[Recurso]Controller@index`

**Parámetros Query (DataTable)**:
```json
{
  "draw": "integer",
  "start": "integer",
  "length": "integer",
  "search[value]": "string",
  "order[0][column]": "integer",
  "order[0][dir]": "asc|desc"
}
```

**Response Success (200)**:
```json
{
  "draw": 1,
  "recordsTotal": 100,
  "recordsFiltered": 50,
  "data": [
    {
      "id": 1,
      "nombre": "string",
      "created_at": "2026-01-01 12:00:00",
      "acciones": "<button>...</button>"
    }
  ]
}
```

**Response Error (403)**:
```json
{
  "message": "No autorizado"
}
```

---

### 2. Crear [Recurso]

**Ruta**: `POST /[modulo]/[recurso]`  
**Middleware**: `auth`  
**Controller**: `[Recurso]Controller@store`

**Request Body**:
```json
{
  "nombre": "string|required|max:255",
  "descripcion": "string|nullable",
  "activo": "boolean|required"
}
```

**Validación (Form Request)**:
```php
// App\Http\Requests\Store[Recurso]Request
public function rules(): array
{
    return [
        'nombre' => 'required|string|max:255|unique:tabla,nombre',
        'descripcion' => 'nullable|string',
        'activo' => 'required|boolean'
    ];
}
```

**Response Success (201)**:
```json
{
  "message": "Registro creado exitosamente",
  "data": {
    "id": 1,
    "nombre": "string",
    "created_at": "2026-01-01 12:00:00"
  }
}
```

**Response Error (422)**:
```json
{
  "message": "Error de validación",
  "errors": {
    "nombre": ["El campo nombre es obligatorio"]
  }
}
```

---

### 3. Mostrar [Recurso]

**Ruta**: `GET /[modulo]/[recurso]/{id}`  
**Middleware**: `auth`  
**Controller**: `[Recurso]Controller@show`

**Response Success (200)**:
```json
{
  "id": 1,
  "nombre": "string",
  "descripcion": "string",
  "activo": true,
  "created_at": "2026-01-01 12:00:00",
  "updated_at": "2026-01-01 12:00:00"
}
```

---

### 4. Actualizar [Recurso]

**Ruta**: `PUT /[modulo]/[recurso]/{id}`  
**Middleware**: `auth`  
**Controller**: `[Recurso]Controller@update`

**Request Body**: (igual que POST, sin unique en nombre si es el mismo registro)

**Response Success (200)**:
```json
{
  "message": "Registro actualizado exitosamente",
  "data": { ... }
}
```

---

### 5. Eliminar [Recurso]

**Ruta**: `DELETE /[modulo]/[recurso]/{id}`  
**Middleware**: `auth`  
**Controller**: `[Recurso]Controller@destroy`

**Response Success (200)**:
```json
{
  "message": "Registro eliminado exitosamente"
}
```

**Response Error (409)**:
```json
{
  "message": "No se puede eliminar, tiene registros relacionados"
}
```

---

## 🗄️ Modelo y Migración

**Modelo**: `App\Models\[Recurso]`
```php
class [Recurso] extends Model
{
    protected $table = 'nombre_tabla';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Relaciones
    public function relacion()
    {
        return $this->belongsTo(OtroModelo::class);
    }
}
```

**Migración**:
```php
Schema::create('nombre_tabla', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 255)->unique();
    $table->text('descripcion')->nullable();
    $table->boolean('activo')->default(true);
    $table->foreignId('otra_tabla_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    
    $table->index('nombre');
});
```

---

## 🎨 Componentes Frontend

### Vista Index (Blade)
**Archivo**: `resources/views/[modulo]/[recurso]/index.blade.php`

**Estructura**:
- Breadcrumb
- Botón "Nuevo [Recurso]"
- DataTable con columnas: ID, Nombre, Descripción, Estado, Acciones
- Modal para crear/editar

### DataTable JavaScript
**Archivo**: `resources/js/[modulo]/[recurso].js`
```javascript
$('#tabla-recursos').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/[modulo]/[recurso]',
    columns: [
        { data: 'id', name: 'id' },
        { data: 'nombre', name: 'nombre' },
        { data: 'descripcion', name: 'descripcion' },
        { data: 'activo', name: 'activo' },
        { data: 'acciones', name: 'acciones', orderable: false }
    ],
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    }
});
```

---

## ✅ Checklist de Implementación

**Backend Agent**:
- [ ] Migración creada y ejecutada
- [ ] Modelo con fillable y casts
- [ ] Form Requests para validación
- [ ] Controller con métodos CRUD
- [ ] Rutas registradas en web.php o api.php
- [ ] DataTable service (Yajra) configurado
- [ ] Políticas de autorización (si aplica)

**Frontend Agent**:
- [ ] Vista index.blade.php creada
- [ ] Modal de creación/edición
- [ ] DataTable inicializado
- [ ] Validación jQuery en formularios
- [ ] SweetAlert2 para confirmaciones
- [ ] Manejo de errores AJAX
- [ ] Responsive verificado

---

## 🔗 Dependencias

**Requiere**:
- Modelo: [OtroModelo] (si aplica)
- Permisos: [crear_recurso, editar_recurso, eliminar_recurso]

**Requerido por**:
- [OtroMódulo] (si aplica)

---

**Notas adicionales**:
[Cualquier consideración especial, reglas de negocio, etc.]