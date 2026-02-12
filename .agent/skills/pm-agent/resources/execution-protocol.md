# Protocolo de Ejecución - PM Agent

## Fase 1: Entendimiento del Requerimiento

**Objetivo**: Capturar requisitos completos antes de planificar.

**Proceso**:
1. Leo la solicitud inicial del usuario
2. Identifico información faltante crítica:
   - ¿Qué entidades/modelos se necesitan?
   - ¿Qué acciones puede hacer el usuario?
   - ¿Hay reglas de negocio especiales?
   - ¿Hay integraciones externas?
   - ¿Permisos/roles involucrados?

3. Si hay ambigüedad **crítica**, pregunto:

**Preguntas Template**:
```
🔍 Clarificación necesaria:

1. **Entidades**: ¿[Entidad] tiene relación con [OtraEntidad]?
2. **Reglas**: ¿Hay validaciones especiales para [campo]?
3. **Permisos**: ¿Quién puede [acción]?
4. **Flujo**: ¿Qué pasa si [escenario edge case]?
```

**Nivel de Pregunta**:
- 🟢 **Asumo** si es estándar (ej: CRUD básico)
- 🟡 **Pregunto** si impacta arquitectura (ej: soft deletes vs hard deletes)
- 🔴 **DEBO preguntar** si no puedo implementar sin saber (ej: cálculo de precio)

---

## Fase 2: Análisis de Dominio

**Objetivo**: Mapear entidades, relaciones y flujos de datos.

### 2.1 Identificar Entidades

**Ejemplo**: "Sistema de órdenes de compra"

| Entidad | Campos Clave | Relaciones |
|---------|--------------|------------|
| Orden | numero, fecha, total, estado | belongsTo Proveedor |
| DetalleOrden | cantidad, precio_unitario | belongsTo Orden, Producto |
| Proveedor | nombre, rfc, contacto | hasMany Ordenes |
| Producto | sku, nombre, precio | hasMany DetalleOrden |

### 2.2 Definir Endpoints

Para cada entidad, determino operaciones CRUD necesarias:
```
✅ GET    /ordenes              (Index con DataTable)
✅ POST   /ordenes              (Create)
✅ GET    /ordenes/{id}         (Show)
✅ PUT    /ordenes/{id}         (Update)
✅ DELETE /ordenes/{id}         (Destroy)
⚠️  POST   /ordenes/{id}/aprobar (Acción custom)
```

### 2.3 Reglas de Negocio

Documento reglas especiales:
```
📌 Reglas:
- Una orden no puede editarse si está "Aprobada"
- El total se calcula automáticamente: SUM(detalles.cantidad * precio_unitario)
- Solo el rol "Compras" puede crear órdenes
- Al aprobar orden, se descuenta del presupuesto
```

---

## Fase 3: Diseño de Contratos API

**Objetivo**: Crear documentación que frontend y backend sigan al pie de la letra.

### Template a Usar
Copio `_shared/api-contracts/contract-template.md` y lleno:
```markdown
# Contrato API: Órdenes de Compra

## 1. GET /ordenes
**Controller**: OrdenController@index
**DataTable**: Sí (server-side)

Columns:
- numero
- proveedor.nombre
- fecha
- total
- estado (badge con colores)
- acciones (editar, eliminar, aprobar)

Response:
{
  "draw": 1,
  "data": [...]
}

## 2. POST /ordenes
Request:
{
  "proveedor_id": 1,
  "fecha": "2026-01-15",
  "detalles": [
    { "producto_id": 5, "cantidad": 10, "precio_unitario": 150.00 }
  ]
}

Validación:
- proveedor_id: required|exists:proveedores
- detalles: required|array|min:1
- detalles.*.producto_id: required|exists:productos
- detalles.*.cantidad: required|integer|min:1

Response (201):
{
  "message": "Orden creada exitosamente",
  "data": { "id": 25, "numero": "OC-2026-025" }
}

## Modelo Eloquent
class Orden extends Model {
    protected $fillable = ['proveedor_id', 'fecha', 'total', 'estado'];
    
    public function detalles() {
        return $this->hasMany(DetalleOrden::class);
    }
}

## Migración
Schema::create('ordenes', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique();
    $table->foreignId('proveedor_id')->constrained();
    $table->date('fecha');
    $table->decimal('total', 10, 2)->default(0);
    $table->enum('estado', ['Borrador', 'Aprobada', 'Cancelada'])->default('Borrador');
    $table->timestamps();
});
```

**Guardo en**: `.agent/skills/_shared/api-contracts/ordenes-contract.md`

---

## Fase 4: Desglose de Tareas

**Objetivo**: Crear lista priorizada de tareas para cada agente.

### Estructura de plan.json
```json
{
  "modulo": "ordenes-compra",
  "descripcion": "Sistema de órdenes de compra con aprobaciones",
  "prioridad": "alta",
  "estimacion_horas": 16,
  
  "tareas": [
    {
      "id": 1,
      "agente": "backend",
      "titulo": "Migración y Modelo Orden",
      "descripcion": "Crear migración, modelo Eloquent, relaciones",
      "prioridad": "alta",
      "estimacion": "2h",
      "archivo_salida": "app/Models/Orden.php",
      "depende_de": []
    },
    {
      "id": 2,
      "agente": "backend",
      "titulo": "Migración y Modelo DetalleOrden",
      "prioridad": "alta",
      "estimacion": "1h",
      "depende_de": [1]
    },
    {
      "id": 3,
      "agente": "backend",
      "titulo": "Form Requests (Store/Update)",
      "prioridad": "alta",
      "estimacion": "1.5h",
      "depende_de": [1, 2]
    },
    {
      "id": 4,
      "agente": "backend",
      "titulo": "Controller CRUD + DataTable",
      "prioridad": "alta",
      "estimacion": "4h",
      "archivo_salida": "app/Http/Controllers/OrdenController.php",
      "depende_de": [3]
    },
    {
      "id": 5,
      "agente": "backend",
      "titulo": "Lógica de aprobación (aprobar orden)",
      "prioridad": "media",
      "estimacion": "2h",
      "depende_de": [4]
    },
    {
      "id": 6,
      "agente": "frontend",
      "titulo": "Vista index con DataTable",
      "descripcion": "Blade + DataTable Yajra + botones de acción",
      "prioridad": "alta",
      "estimacion": "3h",
      "archivo_salida": "resources/views/ordenes/index.blade.php",
      "depende_de": [4]
    },
    {
      "id": 7,
      "agente": "frontend",
      "titulo": "Modal crear/editar orden",
      "prioridad": "alta",
      "estimacion": "2h",
      "depende_de": [6]
    },
    {
      "id": 8,
      "agente": "frontend",
      "titulo": "Confirmación SweetAlert2 para aprobar",
      "prioridad": "media",
      "estimacion": "0.5h",
      "depende_de": [7]
    }
  ],
  
  "dependencias_externas": [
    {
      "modulo": "proveedores",
      "razon": "Foreign key proveedor_id"
    },
    {
      "modulo": "productos",
      "razon": "Foreign key producto_id en detalles"
    }
  ],
  
  "checklist_final": [
    "Migración ejecutada sin errores",
    "CRUD completo funcional",
    "DataTable muestra datos correctamente",
    "Aprobación de orden funciona",
    "Validaciones frontend y backend consistentes"
  ]
}
```

**Guardo en**: `.agent/plan.json`

---

## Fase 5: Documentación Adicional

### Diagrama de BD (Opcional)
Si hay muchas relaciones, creo un diagrama simple:
```
┌─────────────┐       ┌──────────────────┐
│  Proveedor  │◄──────│     Orden        │
└─────────────┘ 1:N   │ - numero         │
                      │ - fecha          │
                      │ - total          │
                      └──────────────────┘
                            │ 1:N
                            ▼
                      ┌──────────────────┐       ┌─────────────┐
                      │  DetalleOrden    │──────►│  Producto   │
                      │ - cantidad       │  N:1  └─────────────┘
                      │ - precio_unit    │
                      └──────────────────┘
```

### Reglas de Validación (Resumen)
```
Orden:
- proveedor_id: required|exists
- fecha: required|date|after_or_equal:today
- estado: enum (Borrador, Aprobada, Cancelada)

DetalleOrden:
- producto_id: required|exists
- cantidad: required|integer|min:1
- precio_unitario: required|numeric|min:0.01
```

---

## Entrega Final

**Lo que el usuario ve**:
```
✅ Plan creado: .agent/plan.json
✅ Contrato API: .agent/skills/_shared/api-contracts/ordenes-contract.md

📊 Resumen:
- 8 tareas (5 backend, 3 frontend)
- Estimación: 16 horas
- Dependencias: Proveedores, Productos

🚀 Siguiente paso:
Workflow Guide coordinará la implementación con backend-agent y frontend-agent.
```

---

## 🚨 Manejo de Ambigüedad

**Caso 1: Regla de negocio poco clara**
```
Usuario: "Sistema de descuentos para clientes"
PM: ¿El descuento es por cliente o por producto? ¿Porcentaje o monto fijo?
```

**Caso 2: Relación compleja**
```
Usuario: "Módulo de nómina"
PM: Detecté que necesitas: Empleados, Conceptos, Periodos, Cálculos.
    Esto es muy complejo. ¿Divido en sprints o necesitas todo junto?
```

**Caso 3: Integración externa**
```
Usuario: "Generar XML para SAT"
PM: Necesito saber: ¿Usas alguna librería específica? ¿Tienes certificados?
```

---

## ✅ Checklist Antes de Entregar

- [ ] Contrato API completo con todos los endpoints
- [ ] Plan.json con tareas priorizadas
- [ ] Dependencias identificadas
- [ ] Migraciones diseñadas
- [ ] Relaciones Eloquent claras
- [ ] Validaciones documentadas
- [ ] Frontend sabe estructura de request/response