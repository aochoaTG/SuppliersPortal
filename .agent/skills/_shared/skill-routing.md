# Enrutamiento de Skills

## Palabras Clave → Skill

### workflow-guide
**Activa cuando**:
- "coordina", "organiza el proyecto", "multi-agente"
- Solicitud completa con frontend + backend
- Menciona "wizard", "flujo completo", "módulo completo"

**Delegará a**:
- PM Agent → Planificación
- Frontend Agent → Vistas Blade
- Backend Agent → Controllers/Models

---

### pm-agent
**Activa cuando**:
- "planifica", "analiza requerimientos", "descompón"
- "qué necesitamos para...", "cómo debería estructurarse"
- Inicio de proyecto o módulo nuevo

**Output**:
- `.agent/plan.json` con tareas
- Contratos API en `_shared/api-contracts/`

---

### frontend-agent
**Activa cuando**:
- "vista", "formulario", "modal", "DataTable"
- "Bootstrap", "jQuery", "SweetAlert", "Blade"
- "diseño", "UI", "interfaz", "layout"

**Scope**:
- Archivos `.blade.php`
- JavaScript en `resources/js/`
- CSS custom (si aplica)
- Integración con Zircos template

---

### backend-agent
**Activa cuando**:
- "modelo", "migración", "controller", "ruta"
- "API", "endpoint", "servicio", "base de datos"
- "Laravel", "Eloquent", "SQL Server"

**Scope**:
- Models en `app/Models/`
- Controllers en `app/Http/Controllers/`
- Requests en `app/Http/Requests/`
- Migraciones en `database/migrations/`
- Rutas en `routes/`

---

## Reglas de Ejecución Paralela

### ✅ Pueden ejecutarse en paralelo:
- Frontend Agent + Backend Agent (si hay contrato API)
- PM Agent primero → luego Frontend/Backend en paralelo

### ❌ NO pueden ejecutarse en paralelo:
- PM Agent + cualquier otro (PM siempre va primero)
- Frontend/Backend sin contrato API previo

---

## Escalamiento

**Tarea Simple** (1 agente):
"Crea un formulario de login con Bootstrap"

**Tarea Media** (2 agentes):
"Implementa CRUD de usuarios con DataTable"
→ pm-agent → frontend-agent + backend-agent

**Tarea Compleja** (4 agentes):
"Sistema de cotizaciones con wizard de 5 pasos"
→ workflow-guide → pm-agent → frontend + backend en paralelo