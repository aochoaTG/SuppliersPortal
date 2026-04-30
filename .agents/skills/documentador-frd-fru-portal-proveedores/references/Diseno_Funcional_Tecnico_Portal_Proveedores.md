# Referencia de Diseno Funcional y Tecnico

## Objetivo

Usa esta referencia cuando la tarea necesite explicar como esta construido tecnicamente el Portal de Proveedores y no solo que requisitos funcionales cubre.

## Enfoque de analisis tecnico

Prioriza estos frentes:

1. Arquitectura general Laravel.
2. Modulos funcionales principales.
3. Modelo de datos y relaciones.
4. Autenticacion, autorizacion y permisos.
5. Integraciones `SAT` / `EFOS`, `TRESS`, correo y procesos programados.
6. Jobs, commands, listeners y notificaciones.
7. Riesgos y deuda tecnica.

## Modulos tecnicos prioritarios

- Proveedores.
- Validacion EFOS.
- Presupuestos.
- Requisiciones.
- RFQ / Cotizaciones.
- Ordenes de Compra.
- Recepciones.
- Aprobaciones.
- Dashboard.
- Auditoria.
- Reportes.
- Usuarios, roles y permisos.
- Integracion con TRESS.
- Notificaciones por correo.
- Scheduler / CRON.

## Preguntas tecnicas que debe responder el documento

- Que version y stack Laravel usa el proyecto.
- Cuales son las rutas y controladores principales.
- Que modelos y tablas soportan cada modulo.
- Que reglas de negocio estan implementadas en requests, services, policies o modelos.
- Que procesos corren en background o scheduler.
- Como se protegen rutas, archivos y acciones criticas.
- Que integraciones externas existen y como se disparan.
- Que modulos lucen parciales o sin cierre formal.

## Estructura sugerida del documento tecnico

```markdown
# Documento de Diseno Funcional y Tecnico - Portal de Proveedores

## 1. Control del documento
## 2. Resumen ejecutivo tecnico
## 3. Vista general de arquitectura
## 4. Tecnologias utilizadas
## 5. Estructura del proyecto Laravel
## 6. Componentes principales
## 7. Modelo de datos
## 8. Flujos tecnicos principales
## 9. Decisiones tecnicas clave
## 10. Seguridad
## 11. Jobs, comandos y procesos programados
## 12. APIs e integraciones
## 13. Interfaz de usuario
## 14. Manejo de archivos
## 15. Auditoria y trazabilidad
## 16. Reportes y exportaciones
## 17. Modulos parciales o fuera de alcance cerrado
## 18. Riesgos tecnicos
## 19. Deuda tecnica detectada
## 20. Recomendaciones
## 21. Pendientes de validacion
## 22. Anexos tecnicos
```

## Plantilla base para componente tecnico

```markdown
## Componente tecnico: Nombre

| Elemento | Valor |
|---|---|
| Tipo | Controller / Model / Migration / Job / Command / Service / Middleware / Policy |
| Archivo | Ruta del archivo |
| Modulo funcional relacionado | Modulo |
| Responsabilidad | Descripcion |
| Dependencias | Modelos, servicios, librerias |
| Observaciones | Hallazgos relevantes |
```

## Hallazgos especiales a extraer

### EFOS

- Fuente de datos.
- Frecuencia de actualizacion.
- Command o job relacionado.
- Tabla de almacenamiento.
- Relacion con proveedores.
- Criterio de riesgo fiscal.
- Acciones automaticas y manuales.

### TRESS

- Endpoint consumido.
- Frecuencia de sincronizacion.
- Campos recibidos.
- Proceso de altas, bajas y cambios.
- Logs, errores y dependencias.

### Ordenes de Compra

- Si provienen de requisicion o son directas.
- Estados del flujo.
- Validacion presupuestal.
- Reglas de aprobacion.
- Relacion con recepciones, cotizaciones y PDF/exportaciones.

### Recepciones

- Quién puede registrar recepcion.
- Soporte de recepcion parcial o total.
- Relacion con remisiones y evidencias.
- Validaciones contra Orden de Compra.
- Impacto en facturacion o pago.
