# Protocolo de Ejecución - Workflow Guide

## Fase 1: Análisis de Complejidad

**Objetivo**: Determinar si la tarea requiere coordinación multi-agente.

**Proceso**:
1. Leo la solicitud del usuario
2. Identifico dominios involucrados (Frontend? Backend? Base de datos?)
3. Evalúo dependencias entre dominios
4. Decido estrategia:
   - **Simple**: Delego a 1 agente directamente
   - **Media**: PM + 1 agente
   - **Compleja**: PM + múltiples agentes en paralelo

**Criterios de Complejidad**:

| Complejidad | Indicadores | Agentes |
|-------------|-------------|---------|
| **Simple** | 1 dominio, sin integración | 1 |
| **Media** | 2 dominios, integración básica | 2-3 |
| **Compleja** | 3+ dominios, múltiples pasos, wizard | 4+ |

**Ejemplos**:
```
❌ Simple: "Agrega validación al campo email"
   → frontend-agent solo

✅ Media: "CRUD de proveedores con DataTable"
   → pm-agent → frontend + backend

✅✅ Compleja: "Wizard de cotización con 5 pasos, PDF, email"
   → workflow-guide coordina
```

---

## Fase 2: Delegación a PM Agent

**Objetivo**: PM Agent crea el plan maestro y contratos API.

**Acción**:
```
Llamo a pm-agent con el contexto completo:
"Analiza este requerimiento y crea:
1. Plan de tareas desglosado
2. Contratos API necesarios
3. Dependencias entre componentes"
```

**Espero**:
- `.agent/plan.json` generado
- Contratos API en `_shared/api-contracts/[modulo]-contract.md`
- Lista de tareas priorizadas

**Validación**:
- [ ] Plan tiene tareas frontend y backend separadas
- [ ] Contratos API definen todos los endpoints
- [ ] Dependencias están claras

---

## Fase 3: Spawning de Agentes

**Objetivo**: Lanzar agentes frontend y backend en paralelo.

**Estrategia**:

### Opción A: Agent Manager UI (Recomendado)
1. Abro Agent Manager en Antigravity
2. Creo workspace para `frontend-agent`:
```
   Workspace: frontend-cotizaciones
   Contexto: Contrato API + Plan PM
   Tarea: "Implementa vistas Blade y DataTable según contrato"
```
3. Creo workspace para `backend-agent`:
```
   Workspace: backend-cotizaciones
   Contexto: Contrato API + Plan PM
   Tarea: "Implementa controllers, models, migraciones según contrato"
```

### Opción B: CLI Orchestrator (Avanzado)
```bash
# Requiere oh-my-ag instalado globalmente
oh-my-ag agent:spawn frontend "Implementa UI wizard" session-01 ./resources/views &
oh-my-ag agent:spawn backend "Implementa API endpoints" session-01 ./app/Http &
wait
```

**Coordinación**:
- Frontend lee contrato API de `_shared/api-contracts/`
- Backend implementa según contrato
- Ambos trabajan en paralelo sin bloquearse

---

## Fase 4: Verificación de Integración

**Objetivo**: Asegurar que frontend y backend se integran correctamente.

**Checklist**:
- [ ] Endpoints implementados coinciden con contrato
- [ ] Formularios frontend envían datos en formato correcto
- [ ] DataTable consume endpoint con parámetros correctos
- [ ] Validaciones frontend y backend son consistentes
- [ ] Respuestas JSON tienen estructura esperada
- [ ] Errores 422/403/500 se manejan en frontend

**Proceso**:
1. Reviso output de ambos agentes
2. Verifico archivos clave:
   - Frontend: `resources/views/[modulo]/index.blade.php`
   - Backend: `app/Http/Controllers/[Modulo]Controller.php`
   - Rutas: `routes/web.php`
3. Simulo flujo completo:
   - Usuario abre vista → DataTable carga → CRUD funciona
4. Si hay inconsistencias, spawneo `debug-agent`

---

## Fase 5: Entrega

**Objetivo**: Confirmar que el módulo está completo y funcional.

**Deliverables**:
1. **Código**:
   - Frontend completo en `resources/views/`
   - Backend completo en `app/`
   - Migraciones ejecutadas
   - Rutas registradas

2. **Documentación**:
   - README con instrucciones de uso
   - Contrato API cumplido
   - Screenshots (opcional)

3. **Testing Manual**:
```
   ✅ Abrir http://localhost/[modulo]
   ✅ DataTable carga datos
   ✅ Crear registro → éxito
   ✅ Editar registro → éxito
   ✅ Eliminar registro → confirmación
   ✅ Validaciones funcionan
```

**Mensaje Final**:
```
✅ Módulo [Nombre] completado

Frontend:
- Vista index con DataTable (/resources/views/[modulo]/index.blade.php)
- Modal de creación/edición
- Validaciones jQuery

Backend:
- Controller CRUD (/app/Http/Controllers/[Modulo]Controller.php)
- Modelo y migración
- Form Requests
- Rutas registradas

Prueba en: http://localhost/[modulo]
```

---

## 🚨 Manejo de Errores

**Problema**: Frontend y backend tienen inconsistencias
**Solución**: 
1. Identifico la discrepancia (ej: campo faltante en API)
2. Determino quién debe cambiar (usualmente backend si frontend sigue contrato)
3. Respawneo el agente con corrección específica

**Problema**: Tarea demasiado compleja para coordinar
**Solución**:
1. Solicito al usuario dividir en módulos más pequeños
2. Coordino módulos uno a la vez
3. Aseguro integración al final

**Problema**: Agentes no finalizan
**Solución**:
1. Reviso inbox en Agent Manager
2. Proporciono feedback específico
3. Ajusto scope si es muy amplio