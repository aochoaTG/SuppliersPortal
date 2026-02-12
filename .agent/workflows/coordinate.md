---
description: 
---

# Coordinate - Coordinación Multi-Agente

**Comando**: `/coordinate`  
**Descripción**: Coordina la implementación completa de un módulo con múltiples agentes trabajando en paralelo

---

## 🎯 Objetivo

Guiar al usuario paso a paso para implementar un módulo completo (frontend + backend) de manera coordinada, asegurando que todos los agentes tengan el contexto necesario.

---

## 📋 Proceso

### Paso 1: Recopilar Información del Módulo

Pregunta al usuario lo siguiente:
```
📋 COORDINACIÓN MULTI-AGENTE - Paso 1/5

¿Qué módulo vamos a implementar?
Ejemplos: Proveedores, Productos, Órdenes de Compra, Facturas

Nombre del módulo: _____________
```

**Espera respuesta del usuario.**

---

### Paso 2: Definir Alcance Funcional

Una vez que tengas el nombre del módulo, pregunta:
```
📋 COORDINACIÓN MULTI-AGENTE - Paso 2/5

Selecciona las funcionalidades necesarias para [MÓDULO]:

Funcionalidades base:
☐ CRUD completo (Crear, Leer, Actualizar, Eliminar)
☐ DataTable con búsqueda, ordenamiento y paginación
☐ Validaciones de formulario (cliente y servidor)
☐ Confirmaciones con SweetAlert2

Funcionalidades adicionales:
☐ Exportar a Excel
☐ Exportar a PDF
☐ Importar desde Excel
☐ Filtros avanzados en DataTable
☐ Gestión de permisos por rol
☐ Soft deletes (papelera)
☐ Auditoría de cambios

Funcionalidades especiales:
☐ Wizard multi-paso
☐ Carga de archivos/imágenes
☐ Integración con otros módulos
☐ Generación de reportes
☐ Envío de emails
☐ Integración SAT/CRE
☐ API REST pública

Marca las que necesites (o escribe "estándar" para CRUD básico)
```

**Espera respuesta del usuario.**

---

### Paso 3: Validar Dependencias
```
📋 COORDINACIÓN MULTI-AGENTE - Paso 3/5

¿Este módulo tiene dependencias de otros módulos?

Por ejemplo:
- "Órdenes de Compra" requiere: Proveedores, Productos
- "Facturas" requiere: Clientes, Productos, Formas de Pago
- "Nómina" requiere: Empleados, Conceptos, Periodos

Dependencias: _____________ 
(escribe "ninguna" si no aplica)
```

**Si el usuario menciona dependencias:**

Verifica si existen:
```
🔍 Verificando dependencias...

Estado de módulos requeridos:
- [Módulo X]: ✓ Existe
- [Módulo Y]: ❌ No existe

⚠️ ATENCIÓN: Faltan módulos requeridos.

Opciones:
1. Implementar módulos faltantes primero
2. Continuar sin dependencias (puede requerir ajustes)
3. Cancelar y planificar mejor

¿Qué prefieres? (1/2/3)
```

---

### Paso 4: Activar PM Agent
```
📋 COORDINACIÓN MULTI-AGENTE - Paso 4/5

🎯 Activando pm-agent para análisis y planificación...

[Llama a pm-agent con el siguiente contexto]:
"Analiza el módulo [NOMBRE] con las siguientes características:
- Funcionalidades: [LISTA DEL PASO 2]
- Dependencias: [LISTA DEL PASO 3]

Genera:
1. Plan detallado de tareas (backend y frontend)
2. Contrato API completo
3. Estructura de base de datos
4. Estimación de tiempo"
```

**Espera a que pm-agent complete.**

Una vez que pm-agent termine:
```
✅ Planificación completada

Archivos generados:
📄 .agent/plan.json
📄 .agent/skills/_shared/api-contracts/[modulo]-contract.md

📊 Resumen del Plan:
- Tareas backend: [N]
- Tareas frontend: [N]  
- Estimación total: [X] horas
- Prioridad: [Alta/Media/Baja]

📝 Contrato API incluye:
- [N] endpoints REST
- Modelos Eloquent: [lista]
- Migraciones: [lista]
- Vistas Blade: [lista]

¿Quieres revisar el plan antes de continuar? (sí/no/mostrar)
```

**Si el usuario dice "mostrar" o "sí":**

Muestra un resumen del plan.json y espera confirmación:
```
¿Procedo con la implementación? (sí/no/modificar)
```

**Si dice "modificar":**
```
¿Qué deseas modificar del plan?
```

---

### Paso 5: Spawning de Agentes

Si el usuario confirma:
```
📋 COORDINACIÓN MULTI-AGENTE - Paso 5/5

🚀 Lanzando agentes en Agent Manager...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[BACKEND AGENT - Workspace 1]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📁 Directorio: ./app
📋 Contrato: _shared/api-contracts/[modulo]-contract.md

Tareas asignadas:
1. Crear migración para tabla [modulo]
2. Crear modelo Eloquent con relaciones
3. Crear Form Requests (Store/Update)
4. Crear Controller CRUD con DataTable
5. Registrar rutas en web.php
6. [Tareas adicionales según plan]

Estado: 🟡 Iniciando...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[FRONTEND AGENT - Workspace 2]  
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📁 Directorio: ./resources/views
📋 Contrato: _shared/api-contracts/[modulo]-contract.md

Tareas asignadas:
1. Crear vista index.blade.php con layout Zircos
2. Crear modal form (crear/editar)
3. Configurar DataTable Yajra
4. Implementar AJAX para CRUD
5. Agregar validaciones jQuery
6. Integrar SweetAlert2
7. [Tareas adicionales según plan]

Estado: 🟡 Iniciando...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⏱️ Tiempo estimado: [X] minutos

📊 Puedes monitorear el progreso en:
- Agent Manager UI (vista gráfica)
- Serena Memory Dashboard: bunx oh-my-ag dashboard

🔔 Te notificaré cuando ambos agentes completen sus tareas.
```

---

### Paso 6: Monitoreo y Verificación

**Durante la ejecución, muestra actualizaciones periódicas:**
```
📊 Actualización de Progreso (cada 2-3 minutos)

[Backend Agent] 
✅ Migración creada
✅ Modelo Eloquent creado
🟡 Trabajando en Controller...

[Frontend Agent]
✅ Vista index creada
✅ Modal form implementado
🟡 Configurando DataTable...
```

**Cuando ambos agentes terminen:**
```
✅ IMPLEMENTACIÓN COMPLETADA

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📦 BACKEND (Completado)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Archivos creados:
✓ database/migrations/YYYY_MM_DD_create_[modulo]_table.php
✓ app/Models/[Modelo].php
✓ app/Http/Requests/Store[Modelo]Request.php
✓ app/Http/Requests/Update[Modelo]Request.php
✓ app/Http/Controllers/[Modelo]Controller.php
✓ routes/web.php (rutas registradas)

Endpoints disponibles:
GET    /[modulo]           → Index + DataTable
POST   /[modulo]           → Store
GET    /[modulo]/{id}      → Show
PUT    /[modulo]/{id}      → Update
DELETE /[modulo]/{id}      → Destroy

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎨 FRONTEND (Completado)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Archivos creados:
✓ resources/views/[modulo]/index.blade.php
✓ resources/views/[modulo]/partials/modal-form.blade.php
✓ resources/js/[modulo]/index.js

Características implementadas:
✓ DataTable con búsqueda y ordenamiento
✓ Modal crear/editar con validación
✓ Confirmación eliminar con SweetAlert2
✓ Feedback visual con toasts
✓ Manejo de errores AJAX
✓ Responsive (Bootstrap 5)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### Paso 7: Instrucciones Post-Implementación
```
🧪 SIGUIENTE PASO: Probar la implementación

1. Ejecuta la migración:
   php artisan migrate

2. Inicia el servidor (si no está corriendo):
   php artisan serve

3. Abre en tu navegador:
   http://localhost/[modulo]

4. Prueba el flujo completo:
   ✓ Ver listado en DataTable
   ✓ Crear nuevo registro
   ✓ Editar registro existente
   ✓ Eliminar registro
   ✓ Buscar en DataTable
   ✓ Exportar (si aplica)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

¿Todo funciona correctamente? (sí/no)
```

**Si el usuario reporta problemas:**
```
🐛 Detecté un problema. Voy a activar debug-agent...

Describe el error o comportamiento inesperado:
_____________
```

**Si todo funciona:**
```
🎉 ¡Excelente! Módulo [NOMBRE] implementado exitosamente.

📚 Documentación generada:
- Contrato API: .agent/skills/_shared/api-contracts/[modulo]-contract.md
- Plan de tareas: .agent/plan.json

💡 Próximos pasos sugeridos:
1. Agregar tests unitarios: php artisan make:test [Modelo]Test
2. Configurar permisos si usas roles
3. Optimizar queries con eager loading si tienes relaciones
4. Agregar seeds si necesitas datos de prueba

¿Necesitas implementar otro módulo? (sí/no)
```

Si dice "sí", reinicia el workflow.

---

## 🚨 Manejo de Errores

### Si pm-agent falla:
```
❌ Error en planificación

Posibles causas:
- Descripción del módulo ambigua
- Dependencias circulares detectadas
- Conflicto con módulos existentes

¿Quieres que pm-agent lo intente nuevamente con más contexto? (sí/no)
```

### Si algún agente se bloquea:
```
⚠️ [Backend/Frontend] Agent requiere tu input

Revisa Agent Manager para ver qué información necesita.
Una vez proporcionada, el workflow continuará automáticamente.
```

### Si hay conflictos de archivos:
```
⚠️ Detecté que algunos archivos ya existen:
- app/Models/[Modelo].php

Opciones:
1. Sobrescribir (reemplazar completamente)
2. Fusionar (mantener cambios personalizados)
3. Saltar (dejar archivos existentes)
4. Cancelar workflow

¿Qué prefieres? (1/2/3/4)
```

---

## ✅ Criterios de Éxito

El workflow se considera exitoso cuando:

- [x] Plan.json generado con todas las tareas
- [x] Contrato API creado y completo
- [x] Backend agent completó todas sus tareas
- [x] Frontend agent completó todas sus tareas
- [x] No hay errores en consola del navegador
- [x] No hay errores en logs de Laravel
- [x] DataTable carga datos correctamente
- [x] CRUD completo funciona end-to-end
- [x] Usuario confirma que todo funciona

---

## 📝 Notas Importantes

1. **No saltes pasos**: Cada paso valida información crítica
2. **Espera confirmación**: No asumas respuestas del usuario
3. **Mantén sincronizados**: Backend y frontend deben usar el mismo contrato
4. **Verifica dependencias**: Módulos faltantes causan errores en runtime
5. **Documenta todo**: Cada decisión debe estar en plan.json o contrato API