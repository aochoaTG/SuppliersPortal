# ESPECIFICACIONES TÉCNICAS
## Sistema de Control Presupuestal y Gestión de Requisiciones - TotalGas

**Versión:** 1.0
**Fecha:** 20 de Noviembre de 2025
**Documentos de Referencia:**
- PROC-FIN-001: Gestión del Catálogo de Productos y Servicios
- PROC-FIN-002: Requisiciones y Control Presupuestal
- PROC-FIN-003: Administración de Centros de Costo y Presupuestos

---

## 1. OBJETIVO PRINCIPAL

Desarrollar un **Sistema Integral de Control Presupuestal y Gestión de Requisiciones** que permita a TotalGas:

1. **Mantener un catálogo centralizado** de productos y servicios autorizados con estructura contable completa
2. **Gestionar el ciclo completo de requisiciones** desde la solicitud hasta la autorización, con validación presupuestal automática
3. **Administrar centros de costo** tanto anuales como de consumo libre con control financiero estricto
4. **Garantizar disciplina financiera** mediante validaciones automáticas de presupuesto y estructura de autorizaciones por monto
5. **Proporcionar trazabilidad total** de todas las operaciones, cambios y decisiones en el sistema
6. **Generar reportes financieros** para seguimiento y toma de decisiones

El sistema debe garantizar que cada gasto esté correctamente clasificado contablemente, validado presupuestalmente y autorizado por el nivel correspondiente según su monto.

---

## 2. REQUISITOS FUNCIONALES

### 2.1. MÓDULO: Catálogo de Productos y Servicios

#### RF-CAT-001: Gestión de Ítems del Catálogo
**Prioridad:** CRÍTICA

El sistema debe permitir:
- Solicitar alta de nuevos productos/servicios con formulario completo (FORM-FIN-001-A)
- Modificar atributos de ítems existentes manteniendo historial
- Desactivar productos/servicios obsoletos sin eliminarlos del sistema
- Buscar ítems por múltiples criterios (descripción, categoría, centro de costo, código)
- Visualizar historial completo de cambios de cada ítem

**Validaciones:**
- Descripción técnica mínimo 20 caracteres
- Verificación automática de no duplicidad
- Validación de estructura contable contra catálogo de cuentas institucional
- Coherencia entre categoría/subcategoría y descripción

#### RF-CAT-002: Estados de Ítems
**Prioridad:** CRÍTICA

El sistema debe gestionar cuatro estados:
- **PENDIENTE DE APROBACIÓN**: Solicitud registrada, en revisión por Administrador
- **ACTIVO**: Ítem aprobado y disponible para requisiciones
- **INACTIVO**: Desactivado, no disponible para nuevas requisiciones (mantiene trazabilidad)
- **RECHAZADO**: Solicitud rechazada con motivo registrado

**Reglas de transición:**
- PENDIENTE → ACTIVO (aprobación del Administrador)
- PENDIENTE → RECHAZADO (rechazo con motivo)
- ACTIVO → INACTIVO (desactivación, solo si no hay requisiciones activas)
- Solo ítems ACTIVOS aparecen en búsquedas para requisiciones

#### RF-CAT-003: Atributos Obligatorios
**Prioridad:** CRÍTICA

Cada ítem debe contener:
- **Código de Ítem**: Generado automáticamente (único)
- **Descripción Técnica**: Texto detallado (≥20 caracteres)
- **Categoría**: Clasificación principal
- **Subcategoría**: Clasificación específica
- **Centro de Costo**: Referencia a centro existente
- **Empresa**: Entidad legal (multiempresa)
- **Precio Estimado**: Referencial (puede variar en requisiciones)
- **Estructura Contable**: Cuenta Mayor + Subcuenta + Subsubcuenta (OBLIGATORIO)
- **Estado**: PENDIENTE/ACTIVO/INACTIVO/RECHAZADO
- **Justificación**: Solo en solicitud de alta
- **Fecha de Alta**: Timestamp de creación
- **Usuario de Alta**: Quien solicitó el alta

#### RF-CAT-004: Validación de Estructura Contable
**Prioridad:** CRÍTICA

El sistema debe:
- Validar que la estructura contable (Cuenta Mayor, Subcuenta, Subsubcuenta) exista en el Catálogo de Cuentas Institucional
- Permitir consulta al Departamento de Contabilidad para asesoría
- Registrar la estructura contable completa al momento del alta
- No permitir ítems activos sin estructura contable válida

---

### 2.2. MÓDULO: Requisiciones

#### RF-REQ-001: Creación de Requisiciones
**Prioridad:** CRÍTICA

El sistema debe permitir:
- Crear nueva requisición asociada a un centro de costo
- Agregar múltiples ítems del catálogo (solo ACTIVOS)
- Especificar cantidad y precio unitario por ítem
- Calcular monto total automáticamente
- Adjuntar documentos de soporte/justificación
- Guardar como borrador antes de enviar

**Validaciones:**
- Solo ítems ACTIVOS pueden agregarse
- Centro de costo debe existir y estar activo
- Monto total debe ser > 0
- Al menos un ítem debe estar presente

#### RF-REQ-002: Estados de Requisiciones
**Prioridad:** CRÍTICA

El sistema debe gestionar cinco estados:
- **BORRADOR**: En captura por solicitante, puede editarse
- **PAUSADA**: En espera de alta de ítem en catálogo
- **PENDIENTE**: Enviada a autorizador, esperando aprobación
- **AUTORIZADA**: Aprobada por autorizador Y validada presupuestalmente
- **RECHAZADA**: Rechazada por autorizador O por falta de presupuesto

**Reglas de transición:**
- BORRADOR → PAUSADA (si ítem no existe en catálogo)
- BORRADOR → PENDIENTE (al enviar, si todos los ítems existen)
- PAUSADA → PENDIENTE (cuando el ítem faltante es dado de alta)
- PENDIENTE → AUTORIZADA (aprobación + validación presupuestal exitosa)
- PENDIENTE → RECHAZADA (rechazo del autorizador O sin presupuesto)

#### RF-REQ-003: Flujo de Autorización
**Prioridad:** CRÍTICA

El sistema debe:
- Determinar automáticamente el autorizador único según el monto total:
  - **Nivel 1** (≤ $50,000): Jefe de Área
  - **Nivel 2** (≤ $150,000): Gerente de Departamento
  - **Nivel 3** (≤ $500,000): Director de Área
  - **Nivel 4** (> $500,000): Director General / CEO
- Enviar notificación automática al autorizador correspondiente
- Permitir al autorizador: Aprobar o Rechazar (con motivo obligatorio)
- **NO requiere aprobación en cascada** (solo una persona aprueba)
- Registrar timestamp y usuario de la decisión

#### RF-REQ-004: Validación Presupuestal Automática
**Prioridad:** CRÍTICA

**INMEDIATAMENTE después de la aprobación del autorizador**, el sistema debe ejecutar validación automática según tipo de centro de costo:

**Para Centros de Costo Anuales (~90%):**
- Verificar presupuesto disponible en la subcategoría del ítem
- Validar contra presupuesto mensual asignado
- Verificar estructura contable
- Si hay presupuesto: AUTORIZADA y compromete presupuesto
- Si NO hay presupuesto: RECHAZADA automáticamente con notificación a solicitante y autorizador

**Para Centros de Costo con Consumo Libre (~10%):**
- Verificar existencia y vigencia del centro de costo
- Validar contra monto global autorizado (no temporal)
- Verificar estructura contable
- NO aplican restricciones mensuales/anuales
- Si dentro del límite global: AUTORIZADA y compromete contra límite
- Si excede límite global: RECHAZADA automáticamente

**Cálculo de Presupuesto Disponible:**
```
Presupuesto Disponible = Presupuesto Asignado - Consumo Ejecutado - Compromisos
```

#### RF-REQ-005: Compromiso Presupuestal
**Prioridad:** CRÍTICA

Al cambiar una requisición a estado AUTORIZADA, el sistema debe:
- Reducir automáticamente el presupuesto disponible del centro de costo
- Registrar el compromiso con referencia a la requisición
- El compromiso permanece hasta que:
  - Se ejecute la compra (pasa a consumo ejecutado)
  - Se cancele la requisición (libera el compromiso)

---

### 2.3. MÓDULO: Centros de Costo y Presupuestos

#### RF-CCP-001: Gestión de Centros de Costo
**Prioridad:** CRÍTICA

El sistema debe permitir:
- Crear centros de costo con dos tipos:
  - **Anuales** (~90%): Con presupuesto anual dividido mensualmente
  - **Consumo Libre** (~10%): Con monto global sin límite temporal
- Asignar responsable (Jefe de Área)
- Definir empresa (multiempresa)
- Activar/Desactivar centros de costo
- Visualizar historial de cambios

**Validaciones específicas para Centros de Consumo Libre:**
- Requiere autorización expresa del Director General
- Debe especificarse el monto global autorizado
- Debe justificarse el motivo (obra, proyecto, uso continuo)

#### RF-CCP-002: Planificación Anual de Presupuestos
**Prioridad:** ALTA

El sistema debe permitir (proceso Octubre-Noviembre):
- Registrar propuestas presupuestales por área
- Consolidar presupuestos de todas las áreas
- Definir presupuesto anual total por centro de costo
- Distribución mensual por categorías de gasto
- Distribución puede ser:
  - Uniforme (monto igual cada mes)
  - Ajustada según estacionalidad operativa
- Aprobar presupuesto consolidado (Director General)

#### RF-CCP-003: Categorías de Gasto
**Prioridad:** ALTA

El sistema debe gestionar categorías estándar:
- Materiales (Insumos y materias primas)
- Servicios (Servicios profesionales y técnicos)
- Viáticos (Gastos de viaje y representación)
- Mantenimiento (Mantenimiento de equipos e instalaciones)
- Capacitación (Programas de desarrollo de personal)
- Tecnología (Software, hardware y servicios TI)
- Otros Gastos (Gastos diversos no clasificados)

Debe permitir:
- Asignar presupuesto por categoría/mes
- Modificar categorías (configuración)
- Reportes por categoría

#### RF-CCP-004: Seguimiento y Monitoreo
**Prioridad:** ALTA

El sistema debe proporcionar:
- Dashboard de consumo vs. presupuesto en tiempo real
- Alertas automáticas cuando el consumo alcanza:
  - 70% del presupuesto mensual
  - 90% del presupuesto mensual
  - 100% del presupuesto mensual
- Visualización de:
  - Presupuesto asignado
  - Consumo ejecutado
  - Compromisos pendientes
  - Presupuesto disponible
- Filtros por centro de costo, categoría, mes, año

#### RF-CCP-005: Ajustes Presupuestales
**Prioridad:** MEDIA

El sistema debe permitir:
- Solicitar ajuste de presupuesto (aumento/reducción)
- Justificar el ajuste
- Requiere autorización del Director General
- Registrar historial completo de ajustes
- Los sobrantes mensuales NO se acumulan automáticamente

---

### 2.4. MÓDULO: Reportes y Análisis

#### RF-REP-001: Reportes Operativos
**Prioridad:** ALTA

El sistema debe generar:
- **Reporte de Requisiciones**: Por estado, centro de costo, período, monto
- **Reporte de Consumo Presupuestal**: Por centro, categoría, mes
- **Reporte de Desviaciones**: Diferencia presupuesto vs. consumo real
- **Reporte de Compromisos Pendientes**: Requisiciones autorizadas no ejecutadas
- **Reporte de Catálogo**: Ítems activos/inactivos, por categoría
- **Historial de Cambios**: Auditoría completa de modificaciones

Formato de exportación:
- Excel (.xlsx)
- PDF
- CSV

#### RF-REP-002: Dashboard Ejecutivo
**Prioridad:** MEDIA

Dashboard para Director General con:
- Resumen consolidado de todos los centros de costo
- Consumo total vs. presupuesto anual
- Top 10 centros con mayor consumo
- Top 10 categorías con mayor gasto
- Requisiciones pendientes de autorización de nivel 4
- Alertas de desviaciones presupuestales significativas (>15%)

---

### 2.5. MÓDULO: Administración y Seguridad

#### RF-ADM-001: Gestión de Usuarios y Roles
**Prioridad:** CRÍTICA

El sistema debe gestionar roles:
- **Solicitante**: Crea requisiciones, solicita alta de ítems
- **Administrador del Catálogo**: Gestiona catálogo, aprueba/rechaza altas
- **Jefe de Área** (Autorizador Nivel 1): Autoriza requisiciones ≤ $50,000
- **Gerente de Departamento** (Autorizador Nivel 2): Autoriza requisiciones ≤ $150,000
- **Director de Área** (Autorizador Nivel 3): Autoriza requisiciones ≤ $500,000
- **Director General / CEO** (Autorizador Nivel 4): Autoriza requisiciones > $500,000, aprueba presupuestos, autoriza centros de consumo libre
- **Departamento de Finanzas**: Gestiona centros de costo, presupuestos, monitoreo
- **Departamento de Contabilidad**: Valida estructura contable, mantiene catálogo de cuentas

**Permisos por rol:**
- Control de acceso basado en roles (RBAC)
- Un usuario puede tener múltiples roles
- Los permisos deben ser granulares por módulo

#### RF-ADM-002: Trazabilidad y Auditoría
**Prioridad:** CRÍTICA

El sistema debe registrar automáticamente:
- Usuario que realiza cada acción
- Timestamp (fecha y hora exacta)
- Tipo de acción (crear, modificar, aprobar, rechazar, etc.)
- Valores anteriores y nuevos (para modificaciones)
- IP del usuario
- Justificación/motivo (cuando aplique)

Todas las tablas críticas deben incluir:
- `created_by`, `created_at`
- `updated_by`, `updated_at`
- Soft delete (`deleted_by`, `deleted_at`)

#### RF-ADM-003: Notificaciones
**Prioridad:** ALTA

El sistema debe enviar notificaciones automáticas:
- **Email** (obligatorio)
- **In-app** (notificaciones en el sistema)

Eventos que disparan notificaciones:
- Nueva requisición enviada a autorizador
- Requisición aprobada/rechazada
- Requisición rechazada por falta de presupuesto
- Nueva solicitud de alta de ítem
- Ítem aprobado/rechazado
- Alerta de consumo presupuestal (70%, 90%, 100%)
- Requisición pausada (esperando ítem)
- Requisición desbloqueada (ítem dado de alta)

---

## 3. REQUISITOS NO FUNCIONALES

### 3.1. Rendimiento

**RNF-REN-001: Tiempo de Respuesta**
- Operaciones de lectura: ≤ 2 segundos
- Operaciones de escritura: ≤ 3 segundos
- Generación de reportes simples: ≤ 5 segundos
- Generación de reportes complejos: ≤ 15 segundos
- Dashboard ejecutivo: ≤ 3 segundos

**RNF-REN-002: Concurrencia**
- Soportar al menos 100 usuarios concurrentes sin degradación de rendimiento
- Validación presupuestal debe soportar múltiples requisiciones simultáneas sin condiciones de carrera

**RNF-REN-003: Volumen de Datos**
- El sistema debe manejar eficientemente:
  - 10,000+ ítems en catálogo
  - 50,000+ requisiciones anuales
  - 200+ centros de costo
  - Historial de 5+ años

### 3.2. Seguridad

**RNF-SEG-001: Autenticación**
- Autenticación mediante usuario y contraseña
- Políticas de contraseña segura:
  - Mínimo 8 caracteres
  - Combinación de mayúsculas, minúsculas, números y símbolos
  - Expiración cada 90 días
- Bloqueo de cuenta después de 5 intentos fallidos
- Soporte para Single Sign-On (SSO) - deseable

**RNF-SEG-002: Autorización**
- Control de acceso basado en roles (RBAC)
- Principio de mínimo privilegio
- Validación de permisos en cada operación

**RNF-SEG-003: Protección de Datos**
- Encriptación de datos sensibles en base de datos
- Conexiones HTTPS obligatorias
- Logs de seguridad para detección de intrusos
- Cumplimiento con GDPR y normativas locales de protección de datos

**RNF-SEG-004: Auditoría**
- Registro completo de todas las operaciones críticas
- Logs inmutables (no pueden modificarse)
- Retención de logs de auditoría: 7 años

### 3.3. Usabilidad

**RNF-USA-001: Interfaz de Usuario**
- Diseño responsive (escritorio, tablet, móvil)
- Interfaz intuitiva sin necesidad de capacitación extensa
- Navegación clara y consistente
- Mensajes de error claros y accionables
- Confirmación para acciones destructivas

**RNF-USA-002: Accesibilidad**
- Cumplimiento con WCAG 2.1 nivel AA
- Soporte para lectores de pantalla
- Navegación por teclado
- Contraste de colores adecuado

**RNF-USA-003: Multiidioma**
- Soporte para español (principal)
- Preparado para internacionalización (i18n)

### 3.4. Disponibilidad y Confiabilidad

**RNF-DIS-001: Disponibilidad**
- Disponibilidad del sistema: 99.5% (tiempo de inactividad máximo: ~3.6 horas/mes)
- Ventana de mantenimiento: Domingos 2:00 AM - 6:00 AM

**RNF-DIS-002: Backup y Recuperación**
- Backup automático diario
- Backup incremental cada 6 horas
- RPO (Recovery Point Objective): 6 horas
- RTO (Recovery Time Objective): 4 horas
- Pruebas de recuperación trimestrales

**RNF-DIS-003: Escalabilidad**
- Arquitectura escalable horizontalmente
- Soporte para crecimiento de 50% anual en volumen de datos
- Bases de datos con capacidad de sharding

### 3.5. Mantenibilidad

**RNF-MAN-001: Código y Arquitectura**
- Código limpio y documentado
- Arquitectura modular y desacoplada
- Documentación técnica actualizada
- Pruebas unitarias: cobertura mínima 80%
- Pruebas de integración para flujos críticos

**RNF-MAN-002: Monitoreo**
- Sistema de monitoreo de salud de la aplicación
- Alertas automáticas para:
  - Errores críticos
  - Degradación de rendimiento
  - Uso excesivo de recursos
  - Fallos de servicios externos

### 3.6. Integración

**RNF-INT-001: APIs**
- API RESTful para integración con otros sistemas
- Documentación OpenAPI/Swagger
- Versionado de APIs

**RNF-INT-002: Exportación de Datos**
- Exportación de reportes en Excel, PDF, CSV
- API para extracción de datos hacia sistemas de BI externos

---

## 4. FLUJOS DE TRABAJO

### 4.1. FLUJO: Alta de Producto/Servicio en Catálogo

```
┌─────────────┐
│ Solicitante │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Identifica necesidad de nuevo           │
│ producto/servicio no existente          │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Accede al sistema y completa            │
│ formulario de alta (FORM-FIN-001-A)     │
│ - Descripción técnica (≥20 chars)       │
│ - Categoría y subcategoría              │
│ - Centro de costo                       │
│ - Empresa                               │
│ - Precio estimado                       │
│ - Justificación                         │
│ - Estructura contable propuesta         │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ SISTEMA: Registra solicitud             │
│ Estado: PENDIENTE DE APROBACIÓN         │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ SISTEMA: Validación automática          │
│ - Campos obligatorios completos         │
│ - Formato correcto                      │
│ - Estructura contable existe en         │
│   catálogo de cuentas institucional     │
└──────┬──────────────────────────────────┘
       │
       ├──[FALLA]──┐
       │           ▼
       │       ┌───────────────────────────┐
       │       │ Notifica a solicitante    │
       │       │ para corrección           │
       │       └───────────────────────────┘
       │
       ▼ [PASA]
┌─────────────────────────────────────────┐
│ Notifica a Administrador del Catálogo   │
└──────┬──────────────────────────────────┘
       │
       ▼
┌──────────────────────┐
│ Administrador del    │
│ Catálogo             │
└──────┬───────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Revisa solicitud:                       │
│ - No duplicidad en catálogo             │
│ - Coherencia descripción vs categoría   │
│ - Estructura contable correcta          │
│ - Pertinencia de justificación          │
└──────┬──────────────────────────────────┘
       │
       ├──[DUDA CONTABLE]──┐
       │                   ▼
       │              ┌─────────────────────┐
       │              │ Consulta con        │
       │              │ Departamento de     │
       │              │ Contabilidad        │
       │              └─────────────────────┘
       │
       ▼
    ┌──────────┐
    │ DECISIÓN │
    └────┬─────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
[APROBAR]  [RECHAZAR]
    │         │
    │         ▼
    │    ┌─────────────────────────────────┐
    │    │ Estado: RECHAZADO               │
    │    │ Notifica a solicitante con      │
    │    │ motivo del rechazo              │
    │    └─────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────┐
│ Estado: ACTIVO                          │
│ Ítem disponible para requisiciones      │
│ Notifica a solicitante                  │
└─────────────────────────────────────────┘
```

### 4.2. FLUJO: Creación y Autorización de Requisición

```
┌─────────────┐
│ Solicitante │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Identifica necesidad operativa          │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Accede al sistema                       │
│ Selecciona "Nueva Requisición"          │
│ Estado inicial: BORRADOR                │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Busca ítem en Catálogo                  │
└──────┬──────────────────────────────────┘
       │
    ┌──┴──┐
    │     │
    ▼     ▼
[EXISTE] [NO EXISTE]
 ACTIVO      │
    │        ▼
    │   ┌─────────────────────────────────┐
    │   │ Solicita alta en catálogo       │
    │   │ Estado: PAUSADA                 │
    │   │ (Espera alta del ítem)          │
    │   └──────────┬──────────────────────┘
    │              │
    │              ▼
    │         [Proceso de alta
    │          en catálogo]
    │              │
    │              ▼
    │         ┌────────────────────────────┐
    │         │ Ítem dado de alta (ACTIVO) │
    │         │ Desbloquea requisición     │
    │         └──────────┬─────────────────┘
    │                    │
    └──────────┬─────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│ Agrega ítem(s) a requisición            │
│ - Especifica cantidad                   │
│ - Especifica precio unitario            │
│ - Sistema calcula monto total           │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Adjunta documentos de soporte           │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Envía requisición                       │
│ Estado: PENDIENTE                       │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ SISTEMA: Determina autorizador según    │
│ monto total:                            │
│ ≤ $50K → Jefe de Área                   │
│ ≤ $150K → Gerente de Depto              │
│ ≤ $500K → Director de Área              │
│ > $500K → Director General/CEO          │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Notifica a autorizador correspondiente  │
└──────┬──────────────────────────────────┘
       │
       ▼
┌──────────────────────┐
│ Autorizador Único    │
└──────┬───────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Revisa requisición:                     │
│ - Necesidad operativa                   │
│ - Correcta selección del ítem           │
│ - Justificación                         │
│ - Tipo de centro de costo               │
│ - Clasificación contable                │
└──────┬──────────────────────────────────┘
       │
       ▼
    ┌──────────┐
    │ DECISIÓN │
    └────┬─────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
[APROBAR]  [RECHAZAR]
    │         │
    │         ▼
    │    ┌─────────────────────────────────┐
    │    │ Estado: RECHAZADA               │
    │    │ Notifica a solicitante          │
    │    │ Presupuesto NO comprometido     │
    │    └─────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────┐
│ SISTEMA: Validación presupuestal        │
│ automática INMEDIATA                    │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│ Identifica tipo de centro de costo      │
└──────┬──────────────────────────────────┘
       │
    ┌──┴──┐
    │     │
    ▼     ▼
[ANUAL] [CONSUMO LIBRE]
 (~90%)    (~10%)
    │         │
    ▼         ▼
┌─────────────────────┐  ┌─────────────────────┐
│ Valida presupuesto  │  │ Valida contra monto │
│ disponible mensual  │  │ global autorizado   │
│ en subcategoría     │  │ (sin límite temp.)  │
└──────┬──────────────┘  └──────┬──────────────┘
       │                        │
       └──────────┬─────────────┘
                  │
                  ▼
            ┌──────────┐
            │ ¿HAY     │
            │ PRESUP?  │
            └────┬─────┘
                 │
            ┌────┴────┐
            │         │
            ▼         ▼
          [SÍ]      [NO]
            │         │
            │         ▼
            │    ┌─────────────────────────────────┐
            │    │ Estado: RECHAZADA               │
            │    │ Motivo: Sin presupuesto         │
            │    │ Notifica a solicitante y        │
            │    │ autorizador                     │
            │    │ Presupuesto NO comprometido     │
            │    └─────────────────────────────────┘
            │
            ▼
┌─────────────────────────────────────────┐
│ Estado: AUTORIZADA                      │
│ Presupuesto COMPROMETIDO                │
│ Notifica a solicitante                  │
│ Lista para siguiente fase               │
│ (cotización/compra)                     │
└─────────────────────────────────────────┘
```

### 4.3. FLUJO: Validación Presupuestal Detallada

```
┌──────────────────────────────────────────┐
│ Requisición APROBADA por autorizador    │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ SISTEMA: Inicia validación automática    │
│ (ejecuta INMEDIATAMENTE)                 │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Obtiene tipo de centro de costo          │
└──────┬───────────────────────────────────┘
       │
    ┌──┴───────────────┐
    │                  │
    ▼                  ▼
[ANUAL ~90%]    [CONSUMO LIBRE ~10%]
    │                  │
    │                  ▼
    │          ┌──────────────────────────────┐
    │          │ Obtiene monto global         │
    │          │ autorizado del centro        │
    │          └──────┬───────────────────────┘
    │                 │
    │                 ▼
    │          ┌──────────────────────────────┐
    │          │ Calcula:                     │
    │          │ Disponible = Monto Global -  │
    │          │   Consumo - Compromisos      │
    │          └──────┬───────────────────────┘
    │                 │
    │                 ▼
    │          ┌──────────────────────────────┐
    │          │ ¿Monto req ≤ Disponible?     │
    │          └──────┬───────────────────────┘
    │                 │
    │            ┌────┴────┐
    │            │         │
    │            ▼         ▼
    │          [SÍ]      [NO]
    │            │         │
    │            │         └──┐
    │            │            │
    ▼            │            │
┌──────────────────────────┐ │
│ Obtiene subcategoría del │ │
│ ítem de la requisición   │ │
└──────┬───────────────────┘ │
       │                     │
       ▼                     │
┌──────────────────────────┐ │
│ Obtiene presupuesto      │ │
│ mensual asignado para    │ │
│ esa subcategoría         │ │
└──────┬───────────────────┘ │
       │                     │
       ▼                     │
┌──────────────────────────┐ │
│ Calcula:                 │ │
│ Disponible = Asignado -  │ │
│   Consumo - Compromisos  │ │
└──────┬───────────────────┘ │
       │                     │
       ▼                     │
┌──────────────────────────┐ │
│ ¿Monto req ≤ Disponible? │ │
└──────┬───────────────────┘ │
       │                     │
  ┌────┴────┐                │
  │         │                │
  ▼         ▼                │
[SÍ]      [NO]               │
  │         │                │
  │         └────────────────┼─┐
  │                          │ │
  │                          ▼ ▼
  │                  ┌────────────────────┐
  │                  │ Estado: RECHAZADA  │
  │                  │ Motivo: Sin presu- │
  │                  │ puesto disponible  │
  │                  └──────┬─────────────┘
  │                         │
  │                         ▼
  │                  ┌────────────────────┐
  │                  │ Notifica:          │
  │                  │ - Solicitante      │
  │                  │ - Autorizador que  │
  │                  │   aprobó           │
  │                  └────────────────────┘
  │
  ▼
┌──────────────────────────────────────────┐
│ Valida estructura contable del ítem      │
│ (ya validada en alta, solo consistencia) │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Estado: AUTORIZADA                       │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ COMPROMETE PRESUPUESTO:                  │
│ - Reduce presupuesto disponible          │
│ - Registra compromiso con ref. a req.    │
│ - Timestamp y usuario                    │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Notifica a solicitante                   │
│ "Requisición AUTORIZADA"                 │
└──────────────────────────────────────────┘
```

### 4.4. FLUJO: Planificación Anual de Presupuestos

```
OCTUBRE - NOVIEMBRE

┌──────────────────────┐
│ Departamento de      │
│ Finanzas             │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Solicita a cada área propuestas          │
│ presupuestales para año siguiente        │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────┐
│ Jefes de Área        │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Preparan propuestas con:                 │
│ - Necesidades operativas                 │
│ - Justificación técnica                  │
│ - Montos por categoría                   │
│ - Distribución mensual propuesta         │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Entregan propuestas a Finanzas           │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────┐
│ Departamento de      │
│ Finanzas             │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Consolida todas las propuestas           │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Analiza:                                 │
│ - Viabilidad financiera                  │
│ - Coherencia con objetivos estratégicos  │
│ - Balance entre áreas                    │
│ - Proyecciones de ingresos               │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Prepara presupuesto consolidado          │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────┐
│ Director General     │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Revisa presupuesto consolidado           │
└──────┬───────────────────────────────────┘
       │
       ▼
    ┌──────────┐
    │ DECISIÓN │
    └────┬─────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
[APROBAR]  [AJUSTAR]
    │         │
    │         ▼
    │    ┌────────────────────────────────┐
    │    │ Solicita ajustes a Finanzas    │
    │    └──────┬─────────────────────────┘
    │           │
    │           └─────┐
    │                 │
    └─────────────────┤
                      │
                      ▼
┌──────────────────────────────────────────┐
│ SISTEMA: Registra presupuesto aprobado   │
│ para ejercicio siguiente                 │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│ Para cada Centro de Costo Anual:         │
│ - Asigna presupuesto anual total         │
│ - Define distribución mensual            │
│ - Asigna por categorías de gasto         │
│ - Activa para uso a partir de 1° enero   │
└──────────────────────────────────────────┘
```

---

## 5. ENTIDADES Y DATOS

### 5.1. Entidad: PRODUCTO/SERVICIO (Ítem del Catálogo)

**Tabla:** `productos_servicios`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único del ítem |
| `codigo` | VARCHAR(20) | UNIQUE, NOT NULL, Auto | Código generado automáticamente |
| `descripcion_tecnica` | TEXT | NOT NULL, ≥20 chars | Descripción detallada del producto/servicio |
| `categoria_id` | UUID | FK, NOT NULL | Referencia a tabla categorías |
| `subcategoria_id` | UUID | FK, NOT NULL | Referencia a tabla subcategorías |
| `centro_costo_id` | UUID | FK, NOT NULL | Centro de costo al que pertenece |
| `empresa_id` | UUID | FK, NOT NULL | Empresa (multiempresa) |
| `precio_estimado` | DECIMAL(15,2) | NOT NULL | Precio referencial |
| `cuenta_mayor_id` | UUID | FK, NOT NULL | Cuenta contable mayor |
| `subcuenta_id` | UUID | FK, NOT NULL | Subcuenta contable |
| `subsubcuenta_id` | UUID | FK, NOT NULL | Subsubcuenta contable |
| `estado` | ENUM | NOT NULL | PENDIENTE, ACTIVO, INACTIVO, RECHAZADO |
| `justificacion` | TEXT | NULL | Motivo de solicitud de alta |
| `motivo_rechazo` | TEXT | NULL | Motivo del rechazo (si aplica) |
| `created_by` | UUID | FK, NOT NULL | Usuario que creó el registro |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_by` | UUID | FK, NULL | Usuario que modificó por última vez |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de última modificación |
| `approved_by` | UUID | FK, NULL | Administrador que aprobó |
| `approved_at` | TIMESTAMP | NULL | Fecha/hora de aprobación |
| `deleted_by` | UUID | FK, NULL | Usuario que eliminó (soft delete) |
| `deleted_at` | TIMESTAMP | NULL | Fecha/hora de eliminación |

**Índices:**
- `idx_codigo` (código)
- `idx_estado` (estado)
- `idx_categoria` (categoria_id, subcategoria_id)
- `idx_centro_costo` (centro_costo_id)
- `idx_estructura_contable` (cuenta_mayor_id, subcuenta_id, subsubcuenta_id)
- `idx_created_at` (created_at)

**Reglas de negocio:**
- `codigo` se genera automáticamente al crear el registro
- Solo ítems con `estado = 'ACTIVO'` pueden agregarse a requisiciones
- Al cambiar a `estado = 'INACTIVO'`, verificar que no existan requisiciones activas
- `estructura contable` debe validarse contra catálogo de cuentas institucional
- `descripcion_tecnica` debe tener al menos 20 caracteres

---

### 5.2. Entidad: REQUISICIÓN

**Tabla:** `requisiciones`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único de la requisición |
| `numero_requisicion` | VARCHAR(20) | UNIQUE, NOT NULL, Auto | Número generado automáticamente |
| `solicitante_id` | UUID | FK, NOT NULL | Usuario solicitante |
| `centro_costo_id` | UUID | FK, NOT NULL | Centro de costo asociado |
| `fecha_solicitud` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `monto_total` | DECIMAL(15,2) | NOT NULL, >0 | Suma de todos los ítems |
| `estado` | ENUM | NOT NULL | BORRADOR, PAUSADA, PENDIENTE, AUTORIZADA, RECHAZADA |
| `autorizador_id` | UUID | FK, NULL | Usuario autorizador asignado |
| `fecha_autorizacion` | TIMESTAMP | NULL | Fecha/hora de aprobación/rechazo |
| `motivo_rechazo` | TEXT | NULL | Motivo del rechazo |
| `tipo_rechazo` | ENUM | NULL | AUTORIZADOR, SIN_PRESUPUESTO |
| `justificacion` | TEXT | NOT NULL | Justificación de la necesidad |
| `documentos_adjuntos` | JSON | NULL | Referencias a archivos adjuntos |
| `created_by` | UUID | FK, NOT NULL | Usuario que creó |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_by` | UUID | FK, NULL | Usuario que modificó |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de última modificación |
| `deleted_by` | UUID | FK, NULL | Soft delete |
| `deleted_at` | TIMESTAMP | NULL | Soft delete |

**Índices:**
- `idx_numero` (numero_requisicion)
- `idx_solicitante` (solicitante_id, fecha_solicitud DESC)
- `idx_centro_costo` (centro_costo_id, fecha_solicitud DESC)
- `idx_estado` (estado)
- `idx_autorizador` (autorizador_id, estado)
- `idx_monto` (monto_total)

**Reglas de negocio:**
- `numero_requisicion` se genera automáticamente (formato: REQ-YYYY-NNNNNN)
- `estado = 'BORRADOR'`: puede editarse y eliminarse
- `estado = 'PAUSADA'`: en espera de alta de ítem en catálogo
- `estado = 'PENDIENTE'`: enviada a autorizador, no editable
- `estado = 'AUTORIZADA'`: aprobada y validada presupuestalmente
- `estado = 'RECHAZADA'`: rechazada por autorizador o sin presupuesto
- `autorizador_id` se asigna automáticamente según `monto_total`
- Al autorizar, debe ejecutarse validación presupuestal inmediata

---

### 5.3. Entidad: ÍTEM DE REQUISICIÓN

**Tabla:** `requisicion_items`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `requisicion_id` | UUID | FK, NOT NULL | Requisición a la que pertenece |
| `producto_servicio_id` | UUID | FK, NOT NULL | Producto/servicio del catálogo |
| `cantidad` | DECIMAL(10,2) | NOT NULL, >0 | Cantidad solicitada |
| `precio_unitario` | DECIMAL(15,2) | NOT NULL, >0 | Precio unitario |
| `monto_total` | DECIMAL(15,2) | NOT NULL, >0 | cantidad * precio_unitario |
| `observaciones` | TEXT | NULL | Observaciones específicas del ítem |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |

**Índices:**
- `idx_requisicion` (requisicion_id)
- `idx_producto` (producto_servicio_id)

**Reglas de negocio:**
- `monto_total = cantidad * precio_unitario` (calculado automáticamente)
- Solo productos/servicios con `estado = 'ACTIVO'` pueden agregarse
- Al agregar/modificar/eliminar ítem, recalcular `monto_total` de la requisición

---

### 5.4. Entidad: CENTRO DE COSTO

**Tabla:** `centros_costo`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `codigo` | VARCHAR(20) | UNIQUE, NOT NULL | Código del centro de costo |
| `nombre` | VARCHAR(200) | NOT NULL | Nombre descriptivo |
| `descripcion` | TEXT | NULL | Descripción detallada |
| `tipo` | ENUM | NOT NULL | ANUAL, CONSUMO_LIBRE |
| `empresa_id` | UUID | FK, NOT NULL | Empresa a la que pertenece |
| `responsable_id` | UUID | FK, NOT NULL | Jefe de área responsable |
| `estado` | ENUM | NOT NULL | ACTIVO, INACTIVO |
| `monto_global` | DECIMAL(15,2) | NULL | Solo para CONSUMO_LIBRE |
| `justificacion_consumo_libre` | TEXT | NULL | Solo para CONSUMO_LIBRE |
| `autorizado_por` | UUID | FK, NULL | Director General (para CONSUMO_LIBRE) |
| `fecha_autorizacion` | TIMESTAMP | NULL | Fecha autorización (CONSUMO_LIBRE) |
| `created_by` | UUID | FK, NOT NULL | Usuario que creó |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_by` | UUID | FK, NULL | Usuario que modificó |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |
| `deleted_by` | UUID | FK, NULL | Soft delete |
| `deleted_at` | TIMESTAMP | NULL | Soft delete |

**Índices:**
- `idx_codigo` (codigo)
- `idx_tipo_estado` (tipo, estado)
- `idx_empresa` (empresa_id)
- `idx_responsable` (responsable_id)

**Reglas de negocio:**
- `tipo = 'ANUAL'`: ~90% de los centros, con presupuesto anual dividido mensualmente
- `tipo = 'CONSUMO_LIBRE'`: ~10% de los centros, con monto global sin límite temporal
- Centros de `tipo = 'CONSUMO_LIBRE'` requieren `monto_global` y autorización del Director General
- Solo centros con `estado = 'ACTIVO'` pueden usarse en requisiciones

---

### 5.5. Entidad: PRESUPUESTO ANUAL

**Tabla:** `presupuestos_anuales`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `centro_costo_id` | UUID | FK, NOT NULL | Centro de costo |
| `ejercicio_fiscal` | INTEGER | NOT NULL | Año (YYYY) |
| `monto_total_anual` | DECIMAL(15,2) | NOT NULL, >0 | Presupuesto total del año |
| `estado` | ENUM | NOT NULL | PLANIFICACION, APROBADO, CERRADO |
| `aprobado_por` | UUID | FK, NULL | Director General |
| `fecha_aprobacion` | TIMESTAMP | NULL | Fecha de aprobación |
| `created_by` | UUID | FK, NOT NULL | Usuario que creó |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_by` | UUID | FK, NULL | Usuario que modificó |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |

**Índices:**
- `idx_centro_ejercicio` (centro_costo_id, ejercicio_fiscal) UNIQUE
- `idx_ejercicio` (ejercicio_fiscal)
- `idx_estado` (estado)

**Reglas de negocio:**
- Un centro de costo solo puede tener un presupuesto por ejercicio fiscal
- Solo aplica para centros de `tipo = 'ANUAL'`
- Estado inicial: `PLANIFICACION`
- Requiere aprobación del Director General para cambiar a `APROBADO`

---

### 5.6. Entidad: DISTRIBUCIÓN MENSUAL DE PRESUPUESTO

**Tabla:** `presupuesto_mensual`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `presupuesto_anual_id` | UUID | FK, NOT NULL | Presupuesto anual |
| `mes` | INTEGER | NOT NULL | Mes (1-12) |
| `categoria_gasto_id` | UUID | FK, NOT NULL | Categoría de gasto |
| `monto_asignado` | DECIMAL(15,2) | NOT NULL, ≥0 | Monto asignado para ese mes/categoría |
| `consumo_ejecutado` | DECIMAL(15,2) | NOT NULL, ≥0, DEFAULT 0 | Consumo real ejecutado |
| `compromisos` | DECIMAL(15,2) | NOT NULL, ≥0, DEFAULT 0 | Requisiciones autorizadas pendientes |
| `presupuesto_disponible` | DECIMAL(15,2) | NOT NULL, ≥0, COMPUTED | asignado - ejecutado - compromisos |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |

**Índices:**
- `idx_presupuesto_mes_cat` (presupuesto_anual_id, mes, categoria_gasto_id) UNIQUE
- `idx_mes` (mes)
- `idx_categoria` (categoria_gasto_id)

**Reglas de negocio:**
- `presupuesto_disponible` se calcula automáticamente: `monto_asignado - consumo_ejecutado - compromisos`
- Al autorizar requisición, incrementar `compromisos`
- Al ejecutar compra, decrementar `compromisos` e incrementar `consumo_ejecutado`
- Al cancelar requisición autorizada, decrementar `compromisos`

---

### 5.7. Entidad: CATEGORÍA DE GASTO

**Tabla:** `categorias_gasto`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `codigo` | VARCHAR(20) | UNIQUE, NOT NULL | Código de la categoría |
| `nombre` | VARCHAR(200) | NOT NULL | Nombre de la categoría |
| `descripcion` | TEXT | NULL | Descripción detallada |
| `estado` | ENUM | NOT NULL | ACTIVO, INACTIVO |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |

**Valores predeterminados:**
- Materiales (Insumos y materias primas)
- Servicios (Servicios profesionales y técnicos)
- Viáticos (Gastos de viaje y representación)
- Mantenimiento (Mantenimiento de equipos e instalaciones)
- Capacitación (Programas de desarrollo de personal)
- Tecnología (Software, hardware y servicios TI)
- Otros Gastos (Gastos diversos no clasificados)

**Índices:**
- `idx_codigo` (codigo)
- `idx_estado` (estado)

---

### 5.8. Entidad: COMPROMISO PRESUPUESTAL

**Tabla:** `compromisos_presupuestales`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `requisicion_id` | UUID | FK, NOT NULL, UNIQUE | Requisición que genera el compromiso |
| `centro_costo_id` | UUID | FK, NOT NULL | Centro de costo |
| `monto_comprometido` | DECIMAL(15,2) | NOT NULL, >0 | Monto del compromiso |
| `fecha_compromiso` | TIMESTAMP | NOT NULL | Fecha/hora del compromiso |
| `estado` | ENUM | NOT NULL | VIGENTE, EJECUTADO, CANCELADO |
| `fecha_ejecutado` | TIMESTAMP | NULL | Fecha de ejecución (compra) |
| `fecha_cancelado` | TIMESTAMP | NULL | Fecha de cancelación |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |

**Índices:**
- `idx_requisicion` (requisicion_id) UNIQUE
- `idx_centro_costo_estado` (centro_costo_id, estado)
- `idx_fecha_compromiso` (fecha_compromiso DESC)

**Reglas de negocio:**
- Se crea automáticamente al autorizar una requisición
- `estado = 'VIGENTE'`: compromiso activo, reduce presupuesto disponible
- `estado = 'EJECUTADO'`: compra ejecutada, libera compromiso, incrementa consumo
- `estado = 'CANCELADO'`: requisición cancelada, libera compromiso

---

### 5.9. Entidad: ESTRUCTURA CONTABLE (Catálogo de Cuentas)

**Tabla:** `cuentas_contables`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `nivel` | ENUM | NOT NULL | CUENTA_MAYOR, SUBCUENTA, SUBSUBCUENTA |
| `codigo` | VARCHAR(20) | NOT NULL | Código contable |
| `nombre` | VARCHAR(200) | NOT NULL | Nombre de la cuenta |
| `padre_id` | UUID | FK, NULL | Cuenta padre (NULL para cuentas mayores) |
| `descripcion` | TEXT | NULL | Descripción detallada |
| `estado` | ENUM | NOT NULL | ACTIVO, INACTIVO |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |

**Índices:**
- `idx_codigo` (codigo) UNIQUE
- `idx_nivel_estado` (nivel, estado)
- `idx_padre` (padre_id)

**Reglas de negocio:**
- Estructura jerárquica de tres niveles: Cuenta Mayor → Subcuenta → Subsubcuenta
- Una Cuenta Mayor no tiene `padre_id` (NULL)
- Una Subcuenta tiene `padre_id` apuntando a Cuenta Mayor
- Una Subsubcuenta tiene `padre_id` apuntando a Subcuenta
- Al dar de alta un ítem en catálogo, debe tener estructura contable completa (los tres niveles)

---

### 5.10. Entidad: USUARIO

**Tabla:** `usuarios`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `username` | VARCHAR(100) | UNIQUE, NOT NULL | Nombre de usuario |
| `email` | VARCHAR(200) | UNIQUE, NOT NULL | Correo electrónico |
| `password_hash` | VARCHAR(255) | NOT NULL | Contraseña hasheada |
| `nombre_completo` | VARCHAR(200) | NOT NULL | Nombre completo |
| `departamento` | VARCHAR(100) | NULL | Departamento al que pertenece |
| `empresa_id` | UUID | FK, NOT NULL | Empresa |
| `estado` | ENUM | NOT NULL | ACTIVO, INACTIVO, BLOQUEADO |
| `intentos_fallidos` | INTEGER | DEFAULT 0 | Intentos de login fallidos |
| `ultimo_login` | TIMESTAMP | NULL | Fecha/hora de último login |
| `password_expira_en` | TIMESTAMP | NULL | Fecha de expiración de contraseña |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |
| `updated_at` | TIMESTAMP | NULL | Fecha/hora de modificación |

**Índices:**
- `idx_username` (username) UNIQUE
- `idx_email` (email) UNIQUE
- `idx_estado` (estado)
- `idx_empresa` (empresa_id)

**Reglas de negocio:**
- Contraseña debe cumplir política: ≥8 caracteres, mayúsculas, minúsculas, números, símbolos
- Bloqueo automático después de 5 intentos fallidos
- Contraseña expira cada 90 días

---

### 5.11. Entidad: ROL

**Tabla:** `roles`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `codigo` | VARCHAR(50) | UNIQUE, NOT NULL | Código del rol |
| `nombre` | VARCHAR(200) | NOT NULL | Nombre del rol |
| `descripcion` | TEXT | NULL | Descripción del rol |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |

**Valores predeterminados:**
- `SOLICITANTE`: Crea requisiciones, solicita alta de ítems
- `ADMIN_CATALOGO`: Gestiona catálogo, aprueba/rechaza altas
- `JEFE_AREA`: Autorizador Nivel 1 (≤ $50,000)
- `GERENTE_DEPTO`: Autorizador Nivel 2 (≤ $150,000)
- `DIRECTOR_AREA`: Autorizador Nivel 3 (≤ $500,000)
- `DIRECTOR_GENERAL`: Autorizador Nivel 4 (> $500,000), aprueba presupuestos, autoriza centros de consumo libre
- `FINANZAS`: Gestiona centros de costo, presupuestos, monitoreo
- `CONTABILIDAD`: Valida estructura contable, mantiene catálogo de cuentas

---

### 5.12. Entidad: USUARIO-ROL

**Tabla:** `usuarios_roles`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `usuario_id` | UUID | FK, NOT NULL | Usuario |
| `rol_id` | UUID | FK, NOT NULL | Rol |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de asignación |

**Índices:**
- `idx_usuario_rol` (usuario_id, rol_id) UNIQUE
- `idx_usuario` (usuario_id)
- `idx_rol` (rol_id)

**Reglas de negocio:**
- Un usuario puede tener múltiples roles
- Los permisos son la unión de todos los roles asignados

---

### 5.13. Entidad: AUDITORÍA

**Tabla:** `auditoria`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `tabla` | VARCHAR(100) | NOT NULL | Nombre de la tabla afectada |
| `registro_id` | UUID | NOT NULL | ID del registro afectado |
| `accion` | ENUM | NOT NULL | CREATE, UPDATE, DELETE, APPROVE, REJECT |
| `usuario_id` | UUID | FK, NOT NULL | Usuario que realizó la acción |
| `ip_address` | VARCHAR(45) | NULL | Dirección IP del usuario |
| `valores_anteriores` | JSON | NULL | Estado anterior (para UPDATE) |
| `valores_nuevos` | JSON | NULL | Estado nuevo |
| `observaciones` | TEXT | NULL | Observaciones adicionales |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de la acción |

**Índices:**
- `idx_tabla_registro` (tabla, registro_id)
- `idx_usuario` (usuario_id)
- `idx_created_at` (created_at DESC)

**Reglas de negocio:**
- Registro inmutable (no puede modificarse ni eliminarse)
- Se registran automáticamente todas las acciones en tablas críticas
- Retención: 7 años

---

### 5.14. Entidad: NOTIFICACIÓN

**Tabla:** `notificaciones`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | UUID | PK, NOT NULL | Identificador único |
| `usuario_id` | UUID | FK, NOT NULL | Usuario destinatario |
| `tipo` | ENUM | NOT NULL | INFO, ALERTA, CRITICA |
| `titulo` | VARCHAR(200) | NOT NULL | Título de la notificación |
| `mensaje` | TEXT | NOT NULL | Mensaje detallado |
| `referencia_tabla` | VARCHAR(100) | NULL | Tabla relacionada |
| `referencia_id` | UUID | NULL | ID del registro relacionado |
| `leida` | BOOLEAN | DEFAULT FALSE | ¿Notificación leída? |
| `fecha_leida` | TIMESTAMP | NULL | Fecha/hora de lectura |
| `enviada_email` | BOOLEAN | DEFAULT FALSE | ¿Email enviado? |
| `fecha_envio_email` | TIMESTAMP | NULL | Fecha/hora de envío de email |
| `created_at` | TIMESTAMP | NOT NULL | Fecha/hora de creación |

**Índices:**
- `idx_usuario_leida` (usuario_id, leida)
- `idx_created_at` (created_at DESC)

**Reglas de negocio:**
- Notificaciones se marcan como `leida = TRUE` cuando el usuario las visualiza
- Las notificaciones se envían también por email

---

## 6. REGLAS DE NEGOCIO

### 6.1. Reglas del Catálogo de Productos/Servicios

**RN-CAT-001: Validación de No Duplicidad**
- Al solicitar alta de un nuevo ítem, el sistema debe buscar ítems similares (descripción + categoría)
- Si encuentra coincidencias >80%, debe alertar al solicitante
- El Administrador del Catálogo debe verificar manualmente antes de aprobar

**RN-CAT-002: Estructura Contable Obligatoria**
- Todo ítem debe tener estructura contable completa (Cuenta Mayor + Subcuenta + Subsubcuenta)
- La estructura debe existir y estar activa en el Catálogo de Cuentas Institucional
- Si hay duda, el Administrador debe consultar con el Departamento de Contabilidad

**RN-CAT-003: Estados y Transiciones**
- Estado inicial de toda solicitud: `PENDIENTE DE APROBACIÓN`
- Solo el Administrador del Catálogo puede cambiar estados
- Transiciones permitidas:
  - PENDIENTE → ACTIVO (aprobación)
  - PENDIENTE → RECHAZADO (rechazo con motivo)
  - ACTIVO → INACTIVO (desactivación, solo si no hay requisiciones activas)
- Ítems RECHAZADOS pueden corregirse y volver a solicitarse (nuevo registro)

**RN-CAT-004: Modificación de Ítems**
- Modificaciones de ítems ACTIVOS NO afectan requisiciones ya autorizadas
- Solo aplican para nuevas requisiciones
- Debe mantenerse historial completo de cambios

**RN-CAT-005: Desactivación vs. Eliminación**
- DESACTIVACIÓN: Cambia estado a INACTIVO, ítem ya no aparece en búsquedas pero se mantiene en el sistema
- ELIMINACIÓN: Baja definitiva, solo permitida si el ítem NUNCA fue usado en una requisición
- ELIMINACIÓN requiere aprobación del Departamento de Finanzas

---

### 6.2. Reglas de Requisiciones

**RN-REQ-001: Niveles de Autorización por Monto**
- El sistema debe asignar automáticamente el autorizador según el monto total:
  - **≤ $50,000**: Jefe de Área (Nivel 1)
  - **≤ $150,000**: Gerente de Departamento (Nivel 2)
  - **≤ $500,000**: Director de Área (Nivel 3)
  - **> $500,000**: Director General / CEO (Nivel 4)
- **SOLO UNA PERSONA** autoriza (no hay autorización en cascada)

**RN-REQ-002: Ítems Pausados**
- Si el solicitante intenta agregar un ítem que no existe en el catálogo:
  - La requisición pasa a estado `PAUSADA`
  - Se debe solicitar el alta del ítem
  - La requisición permanece pausada hasta que el ítem sea dado de alta y aprobado
  - Una vez aprobado el ítem, la requisición se desbloquea automáticamente

**RN-REQ-003: Validación Presupuestal Automática**
- La validación presupuestal se ejecuta **INMEDIATAMENTE** después de la aprobación del autorizador
- Es completamente automática (no requiere intervención humana)
- Si NO hay presupuesto disponible:
  - Estado: `RECHAZADA` automáticamente
  - Tipo de rechazo: `SIN_PRESUPUESTO`
  - Notifica a solicitante Y autorizador que aprobó
  - NO compromete presupuesto

**RN-REQ-004: Compromiso Presupuestal**
- Al cambiar una requisición a estado `AUTORIZADA`:
  - Se crea automáticamente un registro de compromiso presupuestal
  - Se reduce el presupuesto disponible del centro de costo
  - El compromiso permanece `VIGENTE` hasta que:
    - Se ejecute la compra → pasa a `EJECUTADO`, incrementa consumo
    - Se cancele la requisición → pasa a `CANCELADO`, libera presupuesto

**RN-REQ-005: Cálculo de Montos**
- `monto_total` de requisición = Suma de (`cantidad * precio_unitario`) de todos los ítems
- Al agregar, modificar o eliminar un ítem, recalcular automáticamente
- Si el monto cambia después de enviar la requisición, reasignar autorizador si aplica

**RN-REQ-006: Edición de Requisiciones**
- Estado `BORRADOR`: puede editarse libremente
- Estado `PAUSADA`: puede editarse, pero sigue en espera del ítem faltante
- Estados `PENDIENTE`, `AUTORIZADA`, `RECHAZADA`: NO pueden editarse

---

### 6.3. Reglas de Centros de Costo y Presupuestos

**RN-CCP-001: Tipos de Centros de Costo**
- **Centros Anuales (~90%)**:
  - Presupuesto asignado anualmente (enero-diciembre)
  - Distribución mensual por categorías de gasto
  - Validación presupuestal mensual
  - Sobrantes NO se acumulan automáticamente al mes siguiente

- **Centros de Consumo Libre (~10%)**:
  - Monto global autorizado sin límite temporal (no mensual/anual)
  - Requieren autorización expresa del Director General
  - Aplican para gastos de uso continuo, obras o proyectos especiales
  - Una vez agotado el monto, requieren nueva autorización para incremento
  - NO aplican restricciones mensuales/anuales

**RN-CCP-002: Planificación Anual (Octubre-Noviembre)**
- Proceso:
  1. Departamento de Finanzas solicita propuestas a cada área
  2. Jefes de Área entregan propuestas con justificación
  3. Finanzas consolida y analiza viabilidad
  4. Director General revisa y aprueba presupuesto consolidado
  5. Presupuesto formalmente establecido para el ejercicio siguiente

**RN-CCP-003: Distribución Mensual**
- Presupuesto anual se divide en 12 meses
- Distribución puede ser:
  - **Uniforme**: Monto igual cada mes
  - **Ajustada**: Según estacionalidad operativa
- Asignación específica por categorías de gasto
- Revisión trimestral de la distribución

**RN-CCP-004: Cálculo de Presupuesto Disponible**
```
Presupuesto Disponible = Presupuesto Asignado - Consumo Ejecutado - Compromisos

Donde:
- Presupuesto Asignado: Monto asignado para el mes/categoría (o monto global para consumo libre)
- Consumo Ejecutado: Compras ya realizadas
- Compromisos: Requisiciones autorizadas pendientes de ejecutar
```

**RN-CCP-005: Alertas de Consumo**
- El sistema debe generar alertas automáticas cuando el consumo alcanza:
  - 70% del presupuesto mensual → Alerta temprana
  - 90% del presupuesto mensual → Alerta crítica
  - 100% del presupuesto mensual → Alerta de agotamiento
- Notificar a: Jefe de Área responsable y Departamento de Finanzas

**RN-CCP-006: Ajustes Presupuestales**
- Cualquier ajuste de presupuesto requiere autorización del Director General
- Debe registrarse justificación del ajuste
- Se mantiene historial completo de todos los ajustes
- Los sobrantes mensuales NO se acumulan automáticamente al mes siguiente
- Si un área necesita usar sobrantes, debe solicitar ajuste formal

**RN-CCP-007: Creación de Centros de Consumo Libre**
- Solo el Departamento de Finanzas puede crear centros de consumo libre
- Requiere:
  - Monto global autorizado
  - Justificación detallada (obra, proyecto, uso continuo)
  - Autorización expresa del Director General
- Debe registrarse fecha de autorización y autorizador

---

### 6.4. Reglas de Validación Presupuestal

**RN-VAL-001: Validación para Centros de Costo Anuales**
1. Obtener subcategoría del ítem de la requisición
2. Obtener presupuesto mensual asignado para esa subcategoría
3. Calcular presupuesto disponible:
   ```
   Disponible = Asignado - Consumo Ejecutado - Compromisos
   ```
4. Si `Monto Requisición ≤ Disponible`:
   - Autorizar requisición
   - Comprometer presupuesto
5. Si `Monto Requisición > Disponible`:
   - Rechazar requisición automáticamente
   - Motivo: `SIN_PRESUPUESTO`
   - Notificar a solicitante y autorizador

**RN-VAL-002: Validación para Centros de Consumo Libre**
1. Obtener monto global autorizado del centro de costo
2. Calcular disponible:
   ```
   Disponible = Monto Global - Consumo Ejecutado - Compromisos
   ```
3. Si `Monto Requisición ≤ Disponible`:
   - Autorizar requisición
   - Comprometer contra monto global
4. Si `Monto Requisición > Disponible`:
   - Rechazar requisición automáticamente
   - Motivo: `SIN_PRESUPUESTO`
   - Notificar a solicitante y autorizador

**RN-VAL-003: Prevención de Condiciones de Carrera**
- La validación presupuestal debe usar transacciones atómicas
- Implementar lock pesimista al validar presupuesto disponible
- Garantizar que múltiples requisiciones simultáneas no comprometan más presupuesto del disponible

**RN-VAL-004: Validación de Estructura Contable**
- Al validar presupuestalmente, verificar que la estructura contable del ítem sigue vigente
- Si la estructura fue modificada o desactivada, alertar y requiere revisión manual

---

### 6.5. Reglas de Seguridad y Auditoría

**RN-SEG-001: Trazabilidad Total**
- Todas las operaciones críticas deben registrarse en la tabla `auditoria`:
  - Creación, modificación, eliminación de ítems del catálogo
  - Creación, aprobación, rechazo de requisiciones
  - Cambios en presupuestos
  - Creación/modificación de centros de costo
  - Cambios de estado
- Registros de auditoría son inmutables (no pueden modificarse ni eliminarse)

**RN-SEG-002: Separación de Responsabilidades**
- Un usuario NO puede aprobar sus propias requisiciones
- Un usuario NO puede aprobar solicitudes de alta de ítems que él mismo solicitó
- El Administrador del Catálogo NO puede ser la misma persona que el solicitante

**RN-SEG-003: Control de Acceso**
- Validar permisos en cada operación según el rol del usuario
- Un usuario solo puede ver/editar requisiciones de su centro de costo (excepto autorizadores y Finanzas)
- Administrador del Catálogo tiene acceso global al catálogo
- Departamento de Finanzas tiene acceso global a todos los centros de costo y presupuestos

**RN-SEG-004: Notificaciones Obligatorias**
- Toda acción crítica debe generar notificación:
  - Requisición enviada a autorizador
  - Requisición aprobada/rechazada
  - Solicitud de alta de ítem
  - Ítem aprobado/rechazado
  - Alerta de consumo presupuestal
  - Requisición rechazada por falta de presupuesto
- Las notificaciones deben enviarse tanto in-app como por email

---

### 6.6. Reglas de Integridad de Datos

**RN-INT-001: Soft Delete**
- Las entidades críticas usan soft delete (no se eliminan físicamente):
  - Productos/Servicios del catálogo
  - Requisiciones
  - Centros de costo
  - Usuarios
- Al "eliminar", se marca `deleted_at` y `deleted_by`
- Los registros eliminados no aparecen en consultas normales
- Se mantienen para trazabilidad histórica y auditoría

**RN-INT-002: Integridad Referencial**
- No se permite eliminar (soft delete) un centro de costo si tiene:
  - Requisiciones en estados `BORRADOR`, `PAUSADA`, `PENDIENTE`, `AUTORIZADA`
  - Presupuestos activos del año en curso
- No se permite eliminar un ítem del catálogo si:
  - Tiene requisiciones en cualquier estado
  - En este caso, solo se permite desactivación (estado `INACTIVO`)

**RN-INT-003: Validación de Montos**
- Todos los montos deben ser ≥ 0
- Montos de requisiciones deben ser > 0
- Precio estimado de ítems debe ser > 0
- Presupuestos deben ser > 0

**RN-INT-004: Timestamps Obligatorios**
- Toda tabla crítica debe incluir:
  - `created_at`: timestamp de creación (NOT NULL)
  - `updated_at`: timestamp de última modificación (NULL si no ha sido modificado)
  - `created_by`: usuario que creó (NOT NULL)
  - `updated_by`: usuario que modificó (NULL si no ha sido modificado)

---

### 6.7. Reglas de Reportes

**RN-REP-001: Confidencialidad**
- Los usuarios solo pueden ver reportes de sus propios centros de costo
- Excepciones:
  - Autorizadores pueden ver reportes de centros bajo su responsabilidad
  - Departamento de Finanzas puede ver reportes globales
  - Director General puede ver todos los reportes

**RN-REP-002: Consistencia Temporal**
- Los reportes de consumo vs. presupuesto deben mostrar datos actualizados en tiempo real
- Incluir timestamp de generación del reporte
- Los compromisos pendientes deben mostrarse separados del consumo ejecutado

**RN-REP-003: Exportación**
- Todos los reportes deben poder exportarse en:
  - Excel (.xlsx) con formato y fórmulas
  - PDF para impresión
  - CSV para análisis externo

---

## 7. CASOS DE USO CRÍTICOS

### 7.1. CU-001: Solicitar Alta de Producto en Catálogo

**Actor Principal:** Solicitante
**Precondiciones:** Usuario autenticado con rol SOLICITANTE

**Flujo Principal:**
1. Solicitante identifica necesidad de un producto/servicio no existente
2. Solicitante accede al sistema y selecciona "Solicitar Alta de Producto"
3. Sistema muestra formulario (FORM-FIN-001-A)
4. Solicitante completa:
   - Descripción técnica (≥20 caracteres)
   - Categoría y subcategoría
   - Centro de costo
   - Empresa
   - Precio estimado
   - Justificación
   - Estructura contable propuesta
5. Solicitante envía la solicitud
6. Sistema valida automáticamente:
   - Completitud de campos obligatorios
   - Formato de datos
   - Existencia de estructura contable en catálogo institucional
7. Sistema registra solicitud en estado `PENDIENTE DE APROBACIÓN`
8. Sistema notifica a Administrador del Catálogo

**Flujos Alternativos:**
- 6a. Si la validación falla:
  1. Sistema muestra errores al solicitante
  2. Solicitante corrige y reenvía
  3. Volver al paso 6

**Postcondiciones:**
- Solicitud registrada en el sistema
- Administrador del Catálogo notificado

---

### 7.2. CU-002: Aprobar/Rechazar Solicitud de Alta de Producto

**Actor Principal:** Administrador del Catálogo
**Precondiciones:** Solicitud de alta en estado `PENDIENTE DE APROBACIÓN`

**Flujo Principal:**
1. Administrador recibe notificación de nueva solicitud
2. Administrador accede al sistema y revisa la solicitud
3. Administrador verifica:
   - No duplicidad en el catálogo
   - Coherencia entre descripción y categoría
   - Correcta estructura contable
   - Pertinencia de la justificación
4. Si hay duda contable, consulta con Departamento de Contabilidad
5. Administrador decide: APROBAR
6. Sistema cambia estado del ítem a `ACTIVO`
7. Sistema registra fecha y usuario de aprobación
8. Sistema notifica al solicitante
9. Ítem queda disponible para requisiciones

**Flujos Alternativos:**
- 5a. Administrador decide: RECHAZAR
  1. Administrador ingresa motivo del rechazo
  2. Sistema cambia estado a `RECHAZADO`
  3. Sistema registra motivo, fecha y usuario
  4. Sistema notifica al solicitante con el motivo
  5. Solicitante puede corregir y volver a solicitar (nuevo registro)

**Postcondiciones:**
- Ítem aprobado (estado `ACTIVO`) o rechazado (estado `RECHAZADO`)
- Solicitante notificado

---

### 7.3. CU-003: Crear Requisición

**Actor Principal:** Solicitante
**Precondiciones:** Usuario autenticado, centro de costo activo

**Flujo Principal:**
1. Solicitante identifica necesidad operativa
2. Solicitante accede al sistema y selecciona "Nueva Requisición"
3. Sistema crea requisición en estado `BORRADOR`
4. Solicitante selecciona centro de costo
5. Sistema identifica tipo de centro (Anual o Consumo Libre)
6. Solicitante busca producto/servicio en el catálogo
7. Sistema muestra solo ítems con estado `ACTIVO`
8. Solicitante agrega ítem(s), especifica cantidad y precio unitario
9. Sistema calcula monto total automáticamente
10. Solicitante adjunta documentos de soporte
11. Solicitante ingresa justificación
12. Solicitante envía requisición
13. Sistema cambia estado a `PENDIENTE`
14. Sistema determina autorizador según monto total
15. Sistema notifica al autorizador

**Flujos Alternativos:**
- 6a. El producto/servicio no existe en el catálogo:
  1. Sistema sugiere "Solicitar Alta de Producto"
  2. Solicitante solicita alta del ítem (ver CU-001)
  3. Sistema cambia estado de requisición a `PAUSADA`
  4. Requisición permanece pausada hasta que ítem sea aprobado
  5. Una vez aprobado, sistema notifica al solicitante
  6. Solicitante puede retomar la requisición
  7. Volver al paso 6

**Postcondiciones:**
- Requisición creada en estado `PENDIENTE` o `PAUSADA`
- Autorizador notificado (si estado es `PENDIENTE`)

---

### 7.4. CU-004: Aprobar/Rechazar Requisición

**Actor Principal:** Autorizador (Jefe de Área, Gerente, Director, Director General)
**Precondiciones:** Requisición en estado `PENDIENTE`, usuario es el autorizador asignado

**Flujo Principal:**
1. Autorizador recibe notificación de nueva requisición
2. Autorizador accede al sistema y revisa la requisición
3. Autorizador verifica:
   - Necesidad operativa justificada
   - Correcta selección de ítems del catálogo
   - Documentos de soporte adjuntos
   - Tipo de centro de costo y reglas aplicables
   - Clasificación contable correcta
4. Autorizador decide: APROBAR
5. Sistema registra aprobación (fecha, hora, usuario)
6. Sistema ejecuta validación presupuestal automática (ver CU-005)
7. Si validación presupuestal es exitosa:
   - Sistema cambia estado a `AUTORIZADA`
   - Sistema compromete presupuesto
   - Sistema notifica al solicitante
8. Si validación presupuestal falla:
   - Sistema cambia estado a `RECHAZADA`
   - Motivo: `SIN_PRESUPUESTO`
   - Sistema notifica al solicitante y autorizador

**Flujos Alternativos:**
- 4a. Autorizador decide: RECHAZAR
  1. Autorizador ingresa motivo del rechazo
  2. Sistema cambia estado a `RECHAZADA`
  3. Sistema registra motivo, fecha y usuario
  4. Presupuesto NO se compromete
  5. Sistema notifica al solicitante

**Postcondiciones:**
- Requisición en estado `AUTORIZADA` o `RECHAZADA`
- Si AUTORIZADA: presupuesto comprometido
- Solicitante notificado

---

### 7.5. CU-005: Validación Presupuestal Automática

**Actor Principal:** Sistema
**Precondiciones:** Requisición aprobada por autorizador

**Flujo Principal:**
1. Sistema obtiene tipo de centro de costo de la requisición
2. **Si es Centro de Costo Anual:**
   - Sistema obtiene subcategoría del ítem
   - Sistema obtiene presupuesto mensual asignado para esa subcategoría
   - Sistema calcula: `Disponible = Asignado - Consumo - Compromisos`
   - Si `Monto Requisición ≤ Disponible`: ir a paso 8
   - Si `Monto Requisición > Disponible`: ir a paso 9
3. **Si es Centro de Consumo Libre:**
   - Sistema obtiene monto global autorizado del centro
   - Sistema calcula: `Disponible = Monto Global - Consumo - Compromisos`
   - Si `Monto Requisición ≤ Disponible`: ir a paso 8
   - Si `Monto Requisición > Disponible`: ir a paso 9
4. Sistema valida estructura contable del ítem
5. **[CONTINUAR SEGÚN RESULTADO]**

**Flujo 8: HAY PRESUPUESTO DISPONIBLE**
8. Sistema cambia estado de requisición a `AUTORIZADA`
9. Sistema crea compromiso presupuestal:
   - `estado = VIGENTE`
   - `monto_comprometido = monto total requisición`
   - Registra fecha, hora
10. Sistema reduce presupuesto disponible
11. Sistema registra en auditoría
12. Sistema notifica al solicitante: "Requisición AUTORIZADA"
13. FIN

**Flujo 9: NO HAY PRESUPUESTO DISPONIBLE**
9. Sistema cambia estado de requisición a `RECHAZADA`
10. Sistema registra:
   - `tipo_rechazo = SIN_PRESUPUESTO`
   - `motivo_rechazo = "No hay presupuesto disponible en [categoría] para [mes]"` (o para monto global)
11. Presupuesto NO se compromete
12. Sistema registra en auditoría
13. Sistema notifica:
   - Al solicitante: "Requisición RECHAZADA por falta de presupuesto"
   - Al autorizador que aprobó: "La requisición fue rechazada automáticamente por falta de presupuesto"
14. FIN

**Postcondiciones:**
- Requisición en estado `AUTORIZADA` (con presupuesto comprometido) o `RECHAZADA` (sin compromiso)

---

### 7.6. CU-006: Planificar Presupuesto Anual

**Actor Principal:** Departamento de Finanzas
**Precondiciones:** Período de planificación (Octubre-Noviembre)

**Flujo Principal:**
1. Finanzas solicita propuestas presupuestales a cada área
2. Jefes de Área preparan y entregan propuestas con justificación
3. Finanzas consolida todas las propuestas en el sistema
4. Finanzas analiza viabilidad y coherencia con objetivos estratégicos
5. Finanzas prepara presupuesto consolidado
6. Finanzas envía presupuesto al Director General para aprobación
7. Director General revisa presupuesto consolidado
8. Director General aprueba
9. Sistema registra presupuesto aprobado para el ejercicio siguiente
10. Sistema crea registros de presupuesto anual para cada centro de costo
11. Sistema genera distribución mensual por categorías
12. Sistema activa presupuestos para el 1° de enero del año siguiente

**Flujos Alternativos:**
- 8a. Director General solicita ajustes:
  1. Finanzas ajusta propuesta
  2. Volver al paso 6

**Postcondiciones:**
- Presupuesto anual aprobado y registrado
- Distribución mensual establecida
- Centros de costo listos para operar en el nuevo ejercicio

---

### 7.7. CU-007: Crear Centro de Costo con Consumo Libre

**Actor Principal:** Departamento de Finanzas
**Precondiciones:** Necesidad identificada de centro con consumo libre

**Flujo Principal:**
1. Finanzas identifica necesidad (obra, proyecto, uso continuo)
2. Finanzas accede al sistema y selecciona "Crear Centro de Costo"
3. Finanzas completa información:
   - Código y nombre del centro
   - Tipo: `CONSUMO_LIBRE`
   - Empresa
   - Responsable (Jefe de Área)
   - Monto global autorizado
   - Justificación detallada
4. Sistema valida completitud de datos
5. Finanzas solicita autorización al Director General
6. Director General revisa:
   - Justificación
   - Monto solicitado
   - Impacto financiero
7. Director General autoriza
8. Sistema registra:
   - Centro de costo creado
   - `tipo = CONSUMO_LIBRE`
   - `monto_global`
   - `autorizado_por = Director General`
   - `fecha_autorizacion`
9. Sistema activa centro de costo
10. Sistema notifica a Finanzas y al Responsable

**Flujos Alternativos:**
- 7a. Director General rechaza:
  1. Sistema registra rechazo con motivo
  2. Sistema notifica a Finanzas
  3. Finanzas puede ajustar y volver a solicitar

**Postcondiciones:**
- Centro de Costo con Consumo Libre creado y activo
- Listo para recibir requisiciones sin límite temporal

---

## 8. PRIORIZACIÓN DE DESARROLLO

### Fase 1: MVP (Mínimo Producto Viable) - 8 semanas
**Objetivo:** Sistema funcional para gestión básica de catálogo, requisiciones y control presupuestal

**Módulos:**
1. **Autenticación y Usuarios** (1 semana)
   - Login/Logout
   - Gestión de usuarios y roles básicos
   - Control de acceso

2. **Catálogo de Productos/Servicios** (2 semanas)
   - RF-CAT-001: Gestión de ítems
   - RF-CAT-002: Estados de ítems
   - RF-CAT-003: Atributos obligatorios
   - RF-CAT-004: Validación de estructura contable

3. **Requisiciones** (3 semanas)
   - RF-REQ-001: Creación de requisiciones
   - RF-REQ-002: Estados de requisiciones
   - RF-REQ-003: Flujo de autorización
   - RF-REQ-004: Validación presupuestal automática
   - RF-REQ-005: Compromiso presupuestal

4. **Centros de Costo y Presupuestos (básico)** (2 semanas)
   - RF-CCP-001: Gestión de centros de costo (solo anuales)
   - RF-CCP-003: Categorías de gasto
   - RF-CCP-004: Seguimiento básico

### Fase 2: Funcionalidad Completa - 4 semanas
**Objetivo:** Completar funcionalidades avanzadas y reportes

**Módulos:**
5. **Centros de Costo con Consumo Libre** (1 semana)
   - RF-CCP-001: Centros de consumo libre
   - Autorización Director General

6. **Planificación Anual** (1 semana)
   - RF-CCP-002: Planificación de presupuestos
   - Proceso completo Octubre-Noviembre

7. **Reportes y Dashboards** (2 semanas)
   - RF-REP-001: Reportes operativos
   - RF-REP-002: Dashboard ejecutivo
   - Exportación Excel/PDF/CSV

### Fase 3: Optimización y Mejoras - 2 semanas
**Objetivo:** Optimizaciones, notificaciones avanzadas, auditoría completa

**Módulos:**
8. **Notificaciones y Alertas** (1 semana)
   - RF-ADM-003: Notificaciones completas
   - Alertas de consumo presupuestal
   - Emails automáticos

9. **Auditoría y Trazabilidad** (1 semana)
   - RF-ADM-002: Sistema completo de auditoría
   - Logs inmutables
   - Reportes de auditoría

---

## 9. TECNOLOGÍAS RECOMENDADAS

### Backend
- **Framework:** Node.js + Express.js (TypeScript) o Laravel (PHP) o Django (Python)
- **Base de Datos:** PostgreSQL 14+ (soporta JSON, UUIDs, transacciones robustas)
- **ORM:** Sequelize (Node.js) o Eloquent (Laravel) o Django ORM
- **Validaciones:** Joi o Yup (Node.js) o Laravel Validation
- **Autenticación:** JWT + Passport.js o Laravel Sanctum

### Frontend
- **Framework:** React.js + TypeScript o Vue.js 3
- **UI Library:** Material-UI (MUI) o Ant Design o Vuetify
- **State Management:** Redux Toolkit o Zustand o Pinia (Vue)
- **Forms:** React Hook Form + Yup validation
- **Tablas:** React Table o AG Grid
- **Grafos/Charts:** Recharts o Chart.js

### Infraestructura
- **Servidor:** Node.js 18+ LTS o PHP 8.2+ o Python 3.11+
- **Web Server:** Nginx o Apache
- **Contenedores:** Docker + Docker Compose
- **CI/CD:** GitHub Actions o GitLab CI
- **Backup:** pg_dump automatizado + almacenamiento en S3/Azure

### Herramientas de Desarrollo
- **Control de Versiones:** Git + GitHub/GitLab
- **Documentación API:** Swagger/OpenAPI
- **Testing:** Jest + React Testing Library (Frontend), Supertest (Backend API)
- **Linting:** ESLint + Prettier
- **Monitoreo:** Sentry (errores), Grafana + Prometheus (métricas)

---

## 10. CONSIDERACIONES DE IMPLEMENTACIÓN

### 10.1. Estrategia de Base de Datos

**Índices Críticos:**
- Todos los campos de búsqueda frecuente deben tener índices
- Índices compuestos para consultas con múltiples filtros
- Índices en foreign keys para mejorar JOINs

**Transacciones:**
- Usar transacciones para operaciones críticas:
  - Validación presupuestal y compromiso
  - Aprobación de requisiciones
  - Modificación de presupuestos
- Nivel de aislamiento: READ COMMITTED

**Particionamiento:**
- Particionar tabla `auditoria` por año para mejorar rendimiento
- Considerar particionamiento de `requisiciones` si el volumen es muy alto

### 10.2. Seguridad

**Encriptación:**
- HTTPS obligatorio en todas las conexiones
- Contraseñas hasheadas con bcrypt (cost factor ≥ 12)
- Datos sensibles en BD encriptados

**Validaciones:**
- Validación en cliente (UX) y servidor (seguridad)
- Sanitización de inputs para prevenir SQL Injection
- Validación de permisos en cada endpoint

**Protección contra ataques:**
- Rate limiting en endpoints de autenticación
- CSRF tokens en formularios
- Cabeceras de seguridad (Helmet.js)

### 10.3. Escalabilidad

**Caché:**
- Redis para caché de catálogo (ítems activos)
- Caché de sesiones de usuario
- Invalidación de caché al modificar ítems

**Load Balancing:**
- Preparar para múltiples instancias del backend
- Sesiones en Redis (no en memoria del servidor)

**Optimizaciones:**
- Lazy loading en listados largos
- Paginación server-side
- Compresión gzip de respuestas

### 10.4. Monitoreo

**Métricas Clave:**
- Tiempo de respuesta de endpoints críticos
- Tasa de errores
- Requisiciones creadas/autorizadas por día
- Consumo presupuestal en tiempo real
- Usuarios concurrentes

**Alertas:**
- Error rate > 5%
- Response time > 3 segundos
- Caída del servicio
- Uso de disco/memoria > 80%

---

## 11. GLOSARIO

| Término | Definición |
|---------|------------|
| **Administrador del Catálogo** | Responsable designado por Finanzas para gestionar altas, bajas y modificaciones en el catálogo de productos/servicios |
| **Atributos Obligatorios** | Conjunto mínimo de información requerida para registrar un ítem en el catálogo |
| **Autorizador** | Usuario con permiso para aprobar o rechazar requisiciones según rangos de monto establecidos |
| **Catálogo de Productos y Servicios** | Base maestra centralizada que contiene todos los productos y servicios autorizados para adquisición |
| **Centro de Costo** | Unidad organizacional o proyecto al cual se asignan gastos específicos para control presupuestal |
| **Centro de Costo Anual** | Centro con presupuesto asignado por ejercicio fiscal sujeto a validación mensual (~90% de centros) |
| **Centro de Costo con Consumo Libre** | Centro con monto global establecido sin restricciones temporales, autorizado por Director General (~10% de centros) |
| **Compromiso Presupuestal** | Reserva automática de presupuesto cuando una requisición es autorizada |
| **Desviación Presupuestal** | Diferencia entre el presupuesto asignado y el consumo real, expresada en porcentaje |
| **Estructura Contable** | Composición jerárquica de Cuenta Mayor, Subcuenta y Subsubcuenta que clasifica contablemente cada ítem |
| **Ítem** | Producto o servicio individual registrado en el catálogo con sus atributos completos |
| **Presupuesto Anual** | Monto total autorizado para un centro de costo durante el ejercicio fiscal |
| **Presupuesto Disponible** | Monto restante en un centro de costo luego de descontar el consumo ejecutado y los compromisos realizados |
| **Presupuesto Mensual** | Distribución del presupuesto anual en doce partes iguales o ajustadas según estacionalidad |
| **RBAC** | Role-Based Access Control - Control de acceso basado en roles |
| **Requisición** | Solicitud formal de compra sometida a validación y autorización |
| **Soft Delete** | Eliminación lógica de registros (marcados como eliminados pero no borrados físicamente) |
| **Solicitante** | Usuario que identifica necesidades y genera requisiciones o solicitudes de alta de productos |

---

## 12. HISTORIAL DE CAMBIOS DEL DOCUMENTO

| Versión | Fecha | Descripción | Autor |
|---------|-------|-------------|-------|
| 1.0 | 20/11/2025 | Emisión inicial del documento de especificaciones técnicas | Sistema |

---

**FIN DEL DOCUMENTO DE ESPECIFICACIONES TÉCNICAS**
