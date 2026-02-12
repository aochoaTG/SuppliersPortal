---
description: Genera un plan detallado de implementación sin ejecutar código
---

## 🎯 Objetivo

Crear un plan completo con contratos API y estimaciones, pero sin implementar código aún. Útil para:
- Presentar propuestas a clientes
- Estimar tiempos antes de comprometerse
- Planificar sprints
- Revisar arquitectura con el equipo

---

## 📋 Proceso

### Paso 1: Solicitar Descripción Completa
```
📝 PLANIFICACIÓN - Paso 1/4

Describe detalladamente qué necesitas implementar:

Ejemplo:
"Sistema de cotizaciones con wizard de 5 pasos que permita:
- Seleccionar cliente
- Agregar productos con descuentos
- Calcular impuestos automáticamente
- Generar PDF con logo de empresa
- Enviar por email al cliente"

Tu descripción:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
_____________________________________________
_____________________________________________
_____________________________________________
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Espera respuesta del usuario.**

---

### Paso 2: Preguntas de Clarificación

PM Agent analiza la descripción y hace preguntas específicas:
```
📝 PLANIFICACIÓN - Paso 2/4

🔍 Necesito clarificar algunos puntos:

1. Usuarios y Permisos:
   ¿Cuántos tipos de usuarios usarán el sistema?
   ¿Necesitas roles/permisos diferentes?
   
2. Volumen de Datos:
   ¿Cuántos registros esperas manejar? (<1000 / 1000-10000 / >10000)
   ¿Necesitas paginación en todos los listados?

3. Integraciones Externas:
   ¿Hay integraciones con sistemas externos? (SAT, CRE, APIs, etc)
   ¿Necesitas webhooks o callbacks?

4. Reportes:
   ¿Qué tipo de reportes necesitas?
   ¿Excel, PDF, ambos, otros?

5. Prioridad y Plazo:
   ¿Cuál es la prioridad? (Alta/Media/Baja)
   ¿Hay un deadline específico?

6. Ambiente:
   ¿Dónde se desplegará? (Servidor local / Cloud / Compartido)
   ¿Necesitas ambiente de pruebas separado?

Responde solo las preguntas relevantes para tu proyecto:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
_____________________________________________
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Espera respuestas del usuario.**

---

### Paso 3: Activar PM Agent
```
📝 PLANIFICACIÓN - Paso 3/4

🎯 Activando pm-agent para análisis profundo...

[Llama a pm-agent con]:
"Analiza el siguiente requerimiento y genera un plan detallado:

Descripción: [PASO 1]
Clarificaciones: [PASO 2]

Genera:
1. Desglose completo de tareas (backend y frontend)
2. Contratos API para todos los endpoints
3. Diseño de base de datos con relaciones
4. Estimación de tiempo por tarea
5. Identificación de riesgos potenciales
6. Recomendaciones de arquitectura"
```

**PM Agent trabaja...**
```
⏳ Generando plan...

Analizando requerimientos... ✓
Diseñando arquitectura... ✓
Creando contratos API... ✓
Calculando estimaciones... ✓
Identificando riesgos... ✓
```

---

### Paso 4: Presentar Plan Completo
```
📝 PLANIFICACIÓN - Paso 4/4

✅ PLAN DE IMPLEMENTACIÓN COMPLETADO

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 RESUMEN EJECUTIVO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Proyecto: [NOMBRE DEL MÓDULO/SISTEMA]
Complejidad: [Baja/Media/Alta/Muy Alta]
Prioridad: [Alta/Media/Baja]

Módulos a desarrollar: [N]
Endpoints API: [N]
Tablas de base de datos: [N]
Vistas frontend: [N]

Estimación de tiempo:
- Backend: [X] horas
- Frontend: [Y] horas
- Testing: [Z] horas
- TOTAL: [X+Y+Z] horas

Si trabajas 8 horas/día: ~[N] días laborables

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🗄️ DISEÑO DE BASE DE DATOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Tablas a crear:

1. [tabla1]
   - Campos: [lista]
   - Relaciones: [lista]
   - Índices: [lista]

2. [tabla2]
   - Campos: [lista]
   - Relaciones: [lista]
   - Índices: [lista]

[continuar con todas las tablas]

Diagrama de relaciones:
[ASCII diagram o descripción de relaciones]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔌 API ENDPOINTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Lista todos los endpoints del contrato API]

Módulo: [Nombre]
GET    /[recurso]              → Listar con DataTable
POST   /[recurso]              → Crear
GET    /[recurso]/{id}         → Ver detalle
PUT    /[recurso]/{id}         → Actualizar
DELETE /[recurso]/{id}         → Eliminar
[endpoints custom si los hay]

[Repetir para cada módulo]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ TAREAS BACKEND ([N] tareas, [X] horas)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Sprint 1: Base de Datos ([X]h)
  1. ☐ Crear migración [tabla1] (0.5h)
  2. ☐ Crear modelo [Modelo1] (0.5h)
  3. ☐ Crear migración [tabla2] (0.5h)
  4. ☐ Crear modelo [Modelo2] (0.5h)
  [continuar...]

Sprint 2: Controllers y Validaciones ([Y]h)
  1. ☐ Form Request Store[Modelo1] (0.5h)
  2. ☐ Form Request Update[Modelo1] (0.5h)
  3. ☐ Controller [Modelo1]Controller (2h)
  4. ☐ DataTable service para [Modelo1] (1h)
  [continuar...]

Sprint 3: Lógica de Negocio ([Z]h)
  1. ☐ Service para cálculos complejos (3h)
  2. ☐ Eventos y listeners (2h)
  3. ☐ Jobs para tareas asíncronas (2h)
  [continuar...]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎨 TAREAS FRONTEND ([N] tareas, [Y] horas)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Sprint 1: Vistas Base ([X]h)
  1. ☐ Vista index.blade.php (1.5h)
  2. ☐ Modal crear/editar (1h)
  3. ☐ Layout y breadcrumbs (0.5h)
  [continuar...]

Sprint 2: JavaScript e Interactividad ([Y]h)
  1. ☐ DataTable configuración (1h)
  2. ☐ AJAX CRUD operations (2h)
  3. ☐ Validaciones jQuery (1h)
  4. ☐ SweetAlert confirmaciones (0.5h)
  [continuar...]

Sprint 3: UX y Refinamiento ([Z]h)
  1. ☐ Loading states y spinners (1h)
  2. ☐ Manejo de errores (1h)
  3. ☐ Responsive testing (1h)
  [continuar...]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠️ RIESGOS IDENTIFICADOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. [Riesgo 1]
   Impacto: [Alto/Medio/Bajo]
   Mitigación: [Estrategia]

2. [Riesgo 2]
   Impacto: [Alto/Medio/Bajo]
   Mitigación: [Estrategia]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
💡 RECOMENDACIONES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Arquitectura:
- [Recomendación 1]
- [Recomendación 2]

Performance:
- [Recomendación 1]
- [Recomendación 2]

Seguridad:
- [Recomendación 1]
- [Recomendación 2]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📄 ARCHIVOS GENERADOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ .agent/plan.json (plan completo en JSON)
✓ .agent/skills/_shared/api-contracts/[modulo]-contract.md
[✓ otros contratos si hay múltiples módulos]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### Paso 5: Opciones Post-Planificación
```
¿Qué quieres hacer ahora?

1. 📖 Ver plan detallado en JSON
2. ✏️ Modificar algo del plan
3. 🚀 Comenzar implementación (/coordinate)
4. 💾 Exportar plan a PDF/Excel
5. 📤 Compartir plan por email
6. 🗄️ Guardar para después
7. ❌ Descartar plan

Opción: _______
```

**Según la opción elegida:**

**Opción 1 - Ver plan:**
```
[Muestra contenido de .agent/plan.json formateado]
```

**Opción 2 - Modificar:**
```
¿Qué quieres modificar?

1. Agregar/quitar funcionalidades
2. Cambiar estimaciones
3. Ajustar prioridades
4. Modificar estructura de BD
5. Cambiar endpoints API

Selecciona: _______
```

**Opción 3 - Implementar:**
```
Perfecto, iniciando workflow /coordinate con este plan...

[Redirige a /coordinate con plan precargado]
```

**Opción 4 - Exportar:**
```
📄 Generando documento...

Archivo creado: ./storage/plans/plan-[modulo]-[fecha].pdf

Contenido:
- Resumen ejecutivo
- Diagramas de BD
- Lista de endpoints
- Desglose de tareas
- Cronograma estimado

✓ Listo para descargar o imprimir
```

**Opción 6 - Guardar:**
```
✅ Plan guardado

Ubicación: .agent/plans/[modulo]-[timestamp].json

Para retomar este plan más tarde, usa:
/coordinate --plan=[modulo]-[timestamp]
```

---

## 🎯 Casos de Uso

### Caso 1: Cotización para Cliente
```
Cliente: "¿Cuánto cuesta hacer un sistema de inventario?"

Tú: /plan
[Describes sistema de inventario]
[PM genera plan con 40 horas]
[Exportas a PDF]
[Envías cotización al cliente]
```

### Caso 2: Sprint Planning
```
Equipo: "¿Qué podemos completar este sprint?"

Tú: /plan
[Describes feature grande]
[PM estima 60 horas]
[Decides dividir en 2 sprints]
[Guardas plan para sprint 2]
```

### Caso 3: Revisión de Arquitectura
```
Tech Lead: "Revisa este plan antes de empezar"

Tú: /plan
[Generas plan detallado]
[Team revisa contratos API]
[Detectan problema en diseño]
[Modificas plan]
[Aprueban e implementas]
```

---

## 📝 Notas

- El plan NO ejecuta código, solo documenta
- Útil para validar requerimientos antes de codear
- Siempre guarda el plan, nunca sabes cuándo lo necesitarás
- Un buen plan ahorra horas de refactoring después