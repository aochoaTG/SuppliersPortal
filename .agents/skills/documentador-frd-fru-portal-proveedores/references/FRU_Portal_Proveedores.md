# Referencia FRD / FRU del Portal de Proveedores

## Proposito del sistema

El `Portal de Proveedores` de `TotalGas` centraliza y automatiza compras, proveedores y presupuestos para estaciones de servicio a nivel nacional.

Objetivos principales:

- Control financiero.
- Eficiencia en pagos.
- Trazabilidad de compras.
- Seguimiento de requisiciones y ordenes de compra.
- Cumplimiento fiscal.
- Prevencion de operaciones con proveedores en listas `EFOS` del `SAT`.

## Problemas de negocio que resuelve

- Perdida de control operativo entre multiples estaciones.
- Riesgo legal y fiscal por proveedores en listas EFOS.
- Procesos de pago ineficientes.
- Falta de seguimiento oportuno a compras.
- Falta de trazabilidad entre requisiciones, cotizaciones, ordenes y recepciones.
- Dependencia de validaciones manuales.

## Modulos dentro del alcance funcional

Documenta como alcance actual:

1. Gestion Integral de Proveedores.
2. Gestion Presupuestal.
3. Gestion de Requisiciones.
4. Cotizacion / RFQ.
5. Ordenes de Compra.
6. Logistica de Recepcion.
7. Dashboard y Metricas.
8. Flujos de Aprobacion.
9. Auditoria de Cambios.
10. Exportacion de Reportes.
11. Modulos base: autenticacion, usuarios, roles, permisos, centros de costo, archivos y control de acceso.

## Modulos existentes en codigo pero no cerrados funcionalmente

Si aparecen en el codigo, documentalos en la seccion:

`Modulos existentes en codigo con alcance pendiente de validacion`

Lista inicial:

- Sistema de Comunicados.
- Gestion de Incidentes.
- Bloqueo de Ubicaciones.
- Comparador de Cotizaciones.
- Historial de Cotizaciones.
- Recuperacion de contrasena.

Para cada uno documenta:

- Nombre.
- Funcionalidad aparente.
- Estado observado.
- Dependencias.
- Riesgos de tratarlo como terminado.
- Preguntas pendientes.
- Recomendacion.

## Actores y roles esperados

Roles principales:

- `requester`: crea requisiciones y consulta estatus.
- `buyer`: valida proveedores, gestiona RFQ, cotizaciones y ordenes de compra.
- `supplier`: proveedor externo que se registra, cotiza y consulta ordenes.
- `receiver`: registra y valida recepciones parciales o totales.
- `purchasing_manager`: aprueba excepciones y operaciones de alto monto.
- `superadmin`: administra usuarios, roles, permisos, centros de costo y flujos.

Roles operativos adicionales:

- Finanzas / Contabilidad.
- Jefe de estacion.

## Integraciones externas a considerar

- `SAT`: consulta o procesamiento de listas EFOS.
- `CRON` o scheduler: ejecucion de tareas recurrentes.
- `TRESS`: sincronizacion diaria de empleados via endpoint.
- Correo electronico: notificaciones automaticas.

## Reglas obligatorias de documentacion

- No inventar funcionalidad.
- Marcar huecos como `Pendiente de confirmacion` o `No identificado en la revision actual`.
- Distinguir entre implementado, parcial, existente no validado, esperado por negocio y fuera de alcance.
- Confirmar siglas o modulos ambiguos antes de documentarlos como definitivos.
- Usar consistentemente los nombres `Portal de Proveedores`, `TotalGas`, `SAT`, `EFOS`, `TRESS`, `RFQ`, `Orden de Compra`, `Requisicion`, `Centro de costo`.

## Revision tecnica minima

Inspeccionar segun aplique:

- `routes/`
- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Models`
- `app/Services`
- `app/Livewire`
- `database/migrations`
- `database/seeders`
- `app/Jobs`
- `app/Console`
- `app/Events`
- `app/Listeners`
- `app/Notifications`
- `resources/views`
- `config/`
- `tests/`
- `composer.json`
- `package.json`

## Plantilla obligatoria del FRD / FRU

```markdown
# Documento de Requerimientos Funcionales - Portal de Proveedores

## 1. Control del documento
## 2. Introduccion
### 2.1 Proposito
### 2.2 Alcance
### 2.3 Problema de negocio
### 2.4 Definiciones, acronimos y abreviaturas
## 3. Contexto del sistema
## 4. Actores y usuarios
## 5. Modulos dentro del alcance
## 6. Modulos existentes con alcance pendiente de validacion
## 7. Requisitos funcionales
## 8. Requisitos no funcionales
## 9. Reglas de negocio
## 10. Flujos principales
## 11. Integraciones externas
## 12. Restricciones
## 13. Supuestos
## 14. Riesgos funcionales
## 15. Pendientes de definicion
## 16. Anexos
```

## Formato base para requisitos

```markdown
| ID | Modulo | Requisito | Prioridad | Actor principal | Estado |
|---|---|---|---|---|---|
| RF-001 | Proveedores | El sistema debe permitir que un proveedor se registre de forma autonoma mediante un formulario de alta. | Alta | supplier | Implementado / Pendiente de confirmar |
```

Convenciones de IDs:

- `RF-001`, `RF-002`, ...
- `RNF-001`, `RNF-002`, ...
- `RN-001`, `RN-002`, ...
- `INT-001`, `INT-002`, ...
- `SEG-001`, `SEG-002`, ...

## Reglas de negocio base

```markdown
| ID | Regla de negocio | Modulo | Estado |
|---|---|---|---|
| RN-001 | Un proveedor no debe operar si aparece en la lista EFOS del SAT. | Proveedores / EFOS | Pendiente de confirmar en codigo |
```

## Casos de uso base

```markdown
## CU-001: Crear requisicion de compra

| Campo | Descripcion |
|---|---|
| Actor principal | requester |
| Actores secundarios | buyer, purchasing_manager |
| Objetivo | Registrar una solicitud de compra para un centro de costo. |
| Precondiciones | El usuario debe estar autenticado y contar con permisos de solicitante. |
| Flujo principal | 1. El usuario ingresa al modulo de requisiciones. 2. Captura la informacion requerida. 3. Adjunta documentos si aplica. 4. Envia la requisicion a validacion. |
| Flujos alternos | Informacion incompleta, presupuesto insuficiente, usuario sin permisos. |
| Postcondiciones | La requisicion queda registrada y disponible para validacion o aprobacion. |
| Estado | Implementado / Pendiente de confirmar |
```

## Consideraciones especiales

### EFOS

Identifica:

- Fuente de datos EFOS.
- Frecuencia de actualizacion.
- Criterio para marcar proveedor como riesgoso.
- Acciones automaticas y manuales.
- Evidencia o bitacora de validacion.

### Presupuestos

Identifica:

- Si el presupuesto es anual, mensual o ambos.
- Relacion con centro de costo.
- Calculo de disponible, comprometido y ejercido.
- Comportamiento cuando se excede presupuesto.
- Usuarios que pueden aprobar excepciones.
- Indicadores mostrados en dashboard.
