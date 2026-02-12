# Ejemplos - Workflow Guide

## Ejemplo 1: CRUD de Proveedores

### Input Usuario
```
"Necesito un módulo de proveedores con CRUD completo, 
DataTable con búsqueda, y exportar a Excel"
```

### Mi Respuesta (Workflow Guide)
```
📊 Análisis: Tarea COMPLEJA
- Dominios: Frontend + Backend + DB
- Agentes necesarios: 3 (PM + Frontend + Backend)

🎯 Plan de Coordinación:
1. PM Agent crea contrato API
2. Backend implementa CRUD + DataTable service
3. Frontend implementa vistas + DataTable + exportación
4. Verifico integración

Iniciando PM Agent...
```

### Output PM Agent
```json
{
  "modulo": "proveedores",
  "tareas": [
    {
      "id": 1,
      "agente": "backend",
      "titulo": "Modelo y Migración Proveedor",
      "prioridad": "alta"
    },
    {
      "id": 2,
      "agente": "backend",
      "titulo": "Controller con DataTable",
      "prioridad": "alta"
    },
    {
      "id": 3,
      "agente": "frontend",
      "titulo": "Vista index con DataTable",
      "prioridad": "media",
      "depende_de": [1, 2]
    }
  ]
}
```

### Coordinación
```
✅ Contrato API creado: .agent/skills/_shared/api-contracts/proveedores-contract.md

🚀 Spawning Agents en Agent Manager:

[Backend Agent - Workspace 1]
Contexto: Contrato API proveedores
Tarea: "Implementa modelo, migración, controller y DataTable service"

[Frontend Agent - Workspace 2]
Contexto: Contrato API proveedores
Tarea: "Implementa vista index.blade.php con DataTable y exportación Excel"

⏳ Esperando completación...
```

---

## Ejemplo 2: Wizard de Cotización Multi-Paso

### Input Usuario
```
"Wizard de cotización con 5 pasos:
1. Datos generales
2. Selección de productos
3. Descuentos
4. Resumen
5. Generación de PDF

Con validación en cada paso y guardado de progreso."
```

### Mi Respuesta
```
📊 Análisis: Tarea MUY COMPLEJA
- Dominios: Frontend (wizard) + Backend (API) + PDF + Sesión
- Agentes: 4 (PM + Frontend + Backend + posible Debug)

🎯 Estrategia:
1. PM descompone en sprints (cada paso = sprint)
2. Backend primero: API para guardar progreso
3. Frontend: componente wizard reutilizable
4. Integración paso a paso

🚧 Recomendación: Dividir en 2 fases
Fase 1: Pasos 1-3 funcionales
Fase 2: Resumen + PDF

¿Procedo con Fase 1?
```

---

## Ejemplo 3: Corrección Simple (NO coordino)

### Input Usuario
```
"El botón de guardar en proveedores no funciona, 
muestra error 500"
```

### Mi Respuesta
```
❌ NO requiere coordinación multi-agente

Esta es una tarea de debug simple.
Delegando directamente a debug-agent...

[No intervengo más, debug-agent toma el control]
```

---

## Ejemplo 4: Feature con Dependencias

### Input Usuario
```
"Módulo de órdenes de compra que depende de:
- Proveedores (ya existe)
- Productos (ya existe)
- Presupuesto (nuevo)
"
```

### Mi Respuesta
```
📊 Análisis: COMPLEJA con dependencias externas

🔗 Dependencias detectadas:
✅ Proveedores - OK (existe)
✅ Productos - OK (existe)
❌ Presupuesto - FALTANTE

📝 Plan Escalonado:
Sprint 1: Módulo Presupuesto (PM + Backend + Frontend)
Sprint 2: Módulo Órdenes con integración

¿Procedo con Sprint 1 primero?
```

---

## Patrón: Uso de Contratos API

### Momento en que Creo Contrato
```
Usuario: "Sistema de inventario con entradas/salidas"

Workflow Guide:
1. ✅ Detecto múltiples dominios
2. ✅ Llamo a PM Agent
3. ✅ PM crea: _shared/api-contracts/inventario-contract.md
4. ✅ Paso contrato a Frontend + Backend
```

### Contenido del Contrato
```markdown
# Contrato API: Inventario

## Endpoint: POST /inventario/entradas
Request:
{
  "producto_id": 1,
  "cantidad": 100,
  "almacen_id": 2
}

Response:
{
  "message": "Entrada registrada",
  "data": { "id": 5, "saldo_nuevo": 150 }
}
```

### Cómo lo Usan los Agentes
```
[Backend Agent]
Lee contrato → Implementa Controller exacto

[Frontend Agent]
Lee contrato → Sabe estructura del request/response
```

---

## Anti-Patrón: Sobre-Coordinación

### ❌ Mal Uso
```
Usuario: "Cambia el color del botón a azul"

Workflow Guide (INCORRECTO):
"Voy a coordinar con PM Agent para analizar 
el impacto de este cambio..."
```

### ✅ Correcto
```
Usuario: "Cambia el color del botón a azul"

Workflow Guide:
"Tarea simple, no requiere coordinación.
Delegando a frontend-agent..."
[frontend-agent toma el control directamente]
```