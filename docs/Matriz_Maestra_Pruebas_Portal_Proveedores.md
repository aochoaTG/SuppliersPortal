# Matriz Maestra de Pruebas - Portal de Proveedores

## 1. Control del documento

| Campo | Valor |
|---|---|
| Sistema | Portal de Proveedores |
| Organizacion | TotalGas |
| Version | 1.0 |
| Fecha | 2026-05-20 |
| Responsable | Codex |
| Estado | Borrador operativo para QA |
| Base de revision | Codigo fuente real del repositorio |

## 2. Objetivo

Definir una matriz de pruebas funcionales y no funcionales exhaustiva para todo el proyecto, cubriendo interfaces web, rutas internas, portal proveedor, procesos batch, integraciones, reglas de negocio, seguridad, rendimiento, confiabilidad y trazabilidad.

La matriz se construyo con evidencia de:

- `routes/web.php`, `routes/auth.php`, `routes/api.php`, `routes/console.php`
- `app/Http/Controllers/*`
- `app/Http/Requests/*`
- `app/Livewire/*`
- `app/Models/*`
- `app/Services/*`
- `app/Console/Commands/*`
- `app/Jobs/*`
- `app/Notifications/*`
- `database/migrations/*`
- `database/seeders/*`
- `tests/Feature/*`
- `tests/Unit/*`

## 3. Estandares aplicados

- `IEEE 29119 / IEEE 829`: identificacion, trazabilidad, precondiciones, resultados esperados y estado de cobertura.
- `ISTQB`: separacion por nivel de prueba, tipo de prueba, prioridad y riesgo.
- `ISO/IEC 25010`: clasificacion de pruebas no funcionales por seguridad, rendimiento, compatibilidad, usabilidad, confiabilidad, mantenibilidad y portabilidad operativa.

## 4. Alcance bajo prueba

### 4.1 Superficies funcionales identificadas

| Dominio | Componentes principales |
|---|---|
| Acceso base | login, logout, reset de password, verificacion de email, lock screen, perfil, notificaciones |
| Portal proveedor | registro, documentos, RFQ, historial de cotizaciones, entregas, facturas, comunicados |
| Administracion de usuarios | usuarios staff, usuarios proveedor, roles, empresas, centros de costo |
| Revision documental | queue de documentos, aceptacion, rechazo, feedback, banca, REPSE, SIROC |
| Catalogos | companies, stations, taxes, departments, categories, cost centers, receiving locations, cat suppliers, sat retenciones |
| Empleados | listado, importacion externa, foto, promocion, resolucion de lideres |
| Presupuesto | annual budgets, distribuciones mensuales, expense categories, budget movements, disponibilidad, importaciones 2026 |
| Requisiciones | CRUD, Livewire, validacion, hold, rechazo, consumo, bandejas |
| Cotizaciones | quotation planner, RFQ, inbox, comparador, aprobaciones, catalogo de productos/servicios |
| Compras | direct purchase orders, purchase orders, aprobaciones, compromisos, cierres automaticos |
| Recepciones | overview, pendientes, recepcion parcial/completa, remisiones, evidencia de no conformidad |
| Finanzas | facturas, provisiones financieras, conciliacion, ajustes |
| Integraciones y batch | EFOS, tipo de cambio, TRESS, scheduler, jobs de alertas, notificaciones |
| Herramientas y soporte | CFDI generator, visor de logs, storage file proxy |

### 4.2 Cobertura automatizada actual detectada

| Area | Evidencia automatizada localizada | Estado |
|---|---|---|
| Autenticacion y perfil | `tests/Feature/Auth/AuthenticationTest.php`, `tests/Feature/ProfileTest.php` | Parcial |
| Matriz de acceso por roles | `tests/Feature/ModuleAccessMatrixTest.php` | Parcial |
| Registro de proveedor y notificacion | `tests/Feature/SupplierRegistrationNotificationTest.php`, `tests/Feature/AllowedEmailDomainTest.php` | Parcial |
| Presupuesto anual y distribucion | `tests/Feature/AnnualBudgetStoreTest.php`, `tests/Feature/BudgetCedulaDistributionTest.php` | Parcial |
| Centros de costo | `tests/Feature/CostCenterCrudTest.php`, `tests/Feature/CostCenterImportTest.php`, `tests/Feature/CostCenterPurchaseTypeFilterTest.php` | Parcial |
| Empleados | `tests/Feature/EmployeeCatalogTest.php`, `tests/Feature/EmployeePromoteTest.php` | Parcial |
| EFOS y tipo de cambio | `tests/Feature/EfosSyncTest.php`, `tests/Feature/ExchangeRateSyncTest.php` | Parcial |
| Recepciones | `tests/Feature/ReceptionFlowTest.php`, `tests/Unit/OrderReceptionStatusTest.php` | Parcial |
| CFDI y XML | `tests/Feature/CfdiGeneratorControllerTest.php`, `tests/Unit/CfdiGeneratorServiceXmlTest.php`, `tests/Unit/CfdiGeneratorServicePdfTest.php`, `tests/Unit/CfdiXmlParserTest.php` | Parcial |
| Provisiones financieras | `tests/Unit/FinancialProvisionServiceTest.php` | Parcial |
| Notificaciones de bienvenida | `tests/Feature/StaffWelcomeNotificationTest.php` | Parcial |

### 4.3 Huecos mayores de cobertura actual

- No se localizaron pruebas automatizadas amplias para `RFQ`, `quotation planner`, `quotation approvals`, `direct purchase orders`, `purchase orders`, `supplier deliveries`, `supplier invoices`, `finance invoices`, `document review`, `announcements`, `incidents`, `receiving locations`, `sat retenciones`, `cat suppliers`, `budget movements`, `bank details`, `SIROC`, `TRESS sync`, `close inactive purchase orders`, `notification center`, `log viewer` y varios permisos finos por actor.

## 5. Datos base recomendados para ejecucion

Para que la matriz sea repetible, QA debe preparar al menos estos fixtures:

- Usuario `superadmin`
- Usuario `buyer`
- Usuario `staff/requester`
- Usuario `supplier`
- Usuario `receiver`
- Usuario `authorizer`
- Usuario `accounting`
- Empresa activa e inactiva
- Station activa e inactiva
- Cost center `ANNUAL`
- Cost center `FREE_CONSUMPTION`
- Receiving location habilitada para portal y bloqueada para portal
- Supplier limpio, supplier con REPSE, supplier en EFOS, supplier inactivo
- Requisition en `DRAFT`, `PENDING`, `PAUSED`, `REJECTED`, `IN_QUOTATION`
- RFQ en `DRAFT`, `SENT`, `RESPONSES_RECEIVED`, `CANCELLED`
- Direct purchase order en `DRAFT`, `PENDING_APPROVAL`, `ISSUED`, `RETURNED`, `REJECTED`
- Purchase order en `ISSUED`, `PARTIALLY_RECEIVED`, `DELIVERED_PENDING_RECEPTION`, `CLOSED_BY_INACTIVITY`
- Reception parcial y completa
- Financial provision en `PENDING_INVOICE` y `DISCREPANCY_REVIEW`
- XML/PDF validos e invalidos para CFDI y facturas

## 6. Matriz de pruebas funcionales

Columnas:

- `Nivel`: UI, Feature HTTP, Service, Job, Command, Scheduler, API externa, Integracion.
- `Cobertura actual`: `Automatizada`, `Parcial`, `No identificada`.

| ID | Modulo | Flujo / componente | Nivel | Casos obligatorios a ejecutar | Prioridad | Cobertura actual |
|---|---|---|---|---|---|---|
| F-001 | Acceso base | Login | UI + Feature HTTP | acceso con credenciales validas; rechazo por password invalido; rechazo por usuario inactivo; sesion creada; redireccion por rol | Critica | Automatizada |
| F-002 | Acceso base | Logout | UI + Feature HTTP | cierre correcto de sesion; invalidacion de token CSRF previo; no acceso a vistas protegidas despues de logout | Alta | Parcial |
| F-003 | Acceso base | Forgot password | UI + Feature HTTP | solicitud con correo valido; rechazo con correo inexistente; throttle; mensaje de confirmacion | Alta | No identificada |
| F-004 | Acceso base | Reset password | UI + Feature HTTP | token valido; token expirado; password policy; confirmacion requerida; inicio de sesion posterior | Alta | No identificada |
| F-005 | Acceso base | Verificacion de email | UI + Feature HTTP | aviso al usuario; envio de nueva notificacion; enlace firmado valido; enlace alterado o expirado | Media | No identificada |
| F-006 | Acceso base | Lock screen | UI + Feature HTTP | bloqueo manual; pantalla bloqueada aun con sesion valida; desbloqueo con password correcta; rechazo con password incorrecta | Alta | No identificada |
| F-007 | Acceso base | Dashboard | UI | visibilidad por rol; widgets sin error cuando no hay datos; carga con datos minimos y volumetricos | Alta | Parcial |
| F-008 | Acceso base | Perfil de usuario | UI + Feature HTTP | editar nombre/email; validaciones; cambio de password; persistencia; mensajes de exito y error | Alta | Automatizada |
| F-009 | Acceso base | Eliminacion de cuenta | Feature HTTP | borrado del usuario propietario; cierre de sesion; proteccion contra borrado sin password correcta si aplica | Media | Parcial |
| F-010 | Acceso base | Centro de notificaciones | UI + Feature HTTP | listado; abrir notificacion redirige correctamente; marcar una como leida; marcar todas como leidas | Alta | No identificada |
| F-011 | Soporte | Storage file proxy | Feature HTTP | descarga de archivo existente; mime correcto; 404 en path inexistente; rechazo a path traversal | Alta | No identificada |
| F-012 | Soporte | Log viewer | UI + Feature HTTP | visualizacion de logs; limpieza de logs; restriccion de acceso; comportamiento sin archivo de log | Critica | No identificada |
| F-013 | Seguridad funcional | Matriz de acceso por modulo | Feature HTTP | cada rol accede solo a modulos permitidos por `config/module_access.php`; alias `super_admin` y `accountant` resuelven correctamente | Critica | Automatizada |
| F-020 | Proveedores | Registro publico de proveedor | UI + Feature HTTP | alta completa; campos obligatorios; datos fiscales validos; persistencia de usuario y supplier; asignacion de rol `supplier` | Critica | Automatizada |
| F-021 | Proveedores | Dominio de email permitido | Feature HTTP + Rule | aceptacion de dominio permitido; rechazo de dominio no permitido; normalizacion de mayusculas/minusculas | Alta | Automatizada |
| F-022 | Proveedores | Validacion de RFC | Feature HTTP + Rule | RFC valido persona moral/fisica; RFC invalido; formato con espacios; duplicidad | Critica | No identificada |
| F-023 | Proveedores | Validacion EFOS en registro | Feature HTTP + Rule | proveedor limpio; proveedor `Presunto`; proveedor `Definitivo`; mensaje funcional esperado | Critica | Parcial |
| F-024 | Proveedores | REPSE condicionado | UI + Feature HTTP | captura obligatoria cuando proveedor es especializado; no requerida cuando no aplica; edicion posterior | Alta | No identificada |
| F-025 | Proveedores | Duplicidad de proveedor | Feature HTTP | rechazo por email duplicado; rechazo por RFC duplicado; manejo de colisiones de company_name | Alta | No identificada |
| F-026 | Proveedores | Notificacion a compradores por alta | Feature HTTP + Notification | envio a usuarios `buyer`; contenido minimo; no envio a roles no destinatarios | Alta | Automatizada |
| F-027 | Proveedores | Documentos del proveedor - upload | UI + Feature HTTP | carga exitosa; validacion de tipo/size; reemplazo; multiples documentos; permisos por proveedor | Critica | No identificada |
| F-028 | Proveedores | Document review queue | UI + Feature HTTP | cola de pendientes; filtros; acceso a detalle del supplier; documentos visibles por estado | Alta | No identificada |
| F-029 | Proveedores | Aceptacion de documento | Feature HTTP | aceptar documento pendiente; actualizacion de estado; traza/auditoria; reintento de aceptacion invalido | Alta | No identificada |
| F-030 | Proveedores | Rechazo de documento y feedback | Feature HTTP + Notification/Mail | rechazo con motivo; feedback entregado; estado visible para proveedor; validacion de motivo | Alta | No identificada |
| F-031 | Proveedores | Eliminacion de documento | Feature HTTP | proveedor elimina propio documento; admin elimina documento asociado; rechazo de eliminacion no autorizada | Alta | No identificada |
| F-032 | Proveedores | Banca del proveedor | UI + Feature HTTP | alta/edicion de datos bancarios; validaciones; eliminacion; ocultamiento de datos sensibles al rol incorrecto | Critica | No identificada |
| F-033 | Proveedores | Actualizacion REPSE administrativa | Feature HTTP | edicion por buyer/superadmin; persistencia; rechazo de actor sin permiso | Alta | No identificada |
| F-034 | Proveedores | SIROC CRUD | UI + Feature HTTP | index/create/show/edit/delete; adjuntos si aplican; proveedor correcto; permisos admin | Alta | No identificada |
| F-035 | Proveedores | Administracion de usuario proveedor | UI + Feature HTTP | listado; editar; activar/desactivar; borrar; sincronizacion con entidad `Supplier` | Alta | No identificada |
| F-036 | Proveedores | Busqueda de suppliers | API interna | busqueda por nombre/RFC; filtrado por activos/aprobados si aplica; respuesta vacia; caracteres especiales | Media | No identificada |
| F-040 | Usuarios | Staff users CRUD | UI + Feature HTTP | crear staff; editar; activar/desactivar; eliminar; validaciones; listado datatable | Critica | No identificada |
| F-041 | Usuarios | Asignacion de roles | UI + Feature HTTP | asignar multiples roles; reemplazo de roles; persistencia; impacto inmediato en acceso | Critica | No identificada |
| F-042 | Usuarios | Asignacion de empresas | UI + Feature HTTP | asociar una o varias empresas; quitar asociaciones; usuario sin empresas; efecto en visibilidad | Alta | No identificada |
| F-043 | Usuarios | Asignacion de centros de costo | UI + Feature HTTP | asociar multiples centros; revocar; filtros posteriores por usuario | Alta | No identificada |
| F-044 | Usuarios | Staff welcome notification | Feature HTTP + Notification | alta de usuario staff envia notificacion o mail correspondiente; payload correcto | Media | Automatizada |
| F-045 | Comunicados | CRUD admin de announcements | UI + Feature HTTP | crear; editar; eliminar; listado; datatable; contenido vacio; fechas; destinatarios | Alta | No identificada |
| F-046 | Comunicados | Inbox del proveedor | UI + Feature HTTP | listado; apertura de anuncio; PDF; marcar visto; dismiss; no acceso a anuncios ajenos | Alta | No identificada |
| F-047 | Incidentes | CRUD basico de incidentes | UI + Feature HTTP | alta; validaciones; listado; eliminacion; adjuntos si aplica; permisos | Media | No identificada |
| F-050 | Catalogos | Companies CRUD | UI + Feature HTTP | index/create/update/delete; datatable; validaciones de unicidad y estado | Alta | No identificada |
| F-051 | Catalogos | Stations CRUD | UI + Feature HTTP | alta/edicion/borrado; datatable; activacion/desactivacion; relacion con company | Alta | No identificada |
| F-052 | Catalogos | Stations - link company | Feature HTTP | asociar estacion a company; re-asociar; validar company inexistente; reglas de integridad | Alta | No identificada |
| F-053 | Catalogos | Taxes CRUD | UI + Feature HTTP | altas con porcentaje; edicion; listado; borrado restringido si esta en uso | Alta | No identificada |
| F-054 | Catalogos | Departments CRUD | UI + Feature HTTP | create/update/delete/datatable; validaciones; comportamiento sin registros | Media | No identificada |
| F-055 | Catalogos | Categories CRUD | UI + Feature HTTP | create/update/delete/datatable; filtros por uso presupuestal si aplican | Alta | No identificada |
| F-056 | Catalogos | Cost centers CRUD | UI + Feature HTTP | create/update/delete/datatable; campos obligatorios; estados; tipo de centro | Alta | Automatizada |
| F-057 | Catalogos | Cost centers por company | API interna | respuesta filtrada por company; company sin centros; formato del payload | Media | Parcial |
| F-058 | Catalogos | Importacion de cost centers | UI + Feature HTTP + Service | descarga de template; preview; errores de parseo; confirmacion; no duplicados; rollback en importacion invalida | Critica | Automatizada |
| F-059 | Catalogos | Filtro por purchase type | Service/Feature HTTP | comportamiento de centros `ANNUAL` vs `FREE_CONSUMPTION`; disponibilidad en formularios | Alta | Automatizada |
| F-060 | Catalogos | Receiving locations CRUD | UI + Feature HTTP | alta/edicion/show/delete; data endpoint; validaciones; asociacion con estaciones | Alta | No identificada |
| F-061 | Catalogos | Receiving locations bloqueo portal | Feature HTTP | block/unblock portal; efecto visible en portal proveedor; actor sin permiso es rechazado | Critica | No identificada |
| F-062 | Catalogos | Cat suppliers | UI + Feature HTTP | listado; datatable; edicion; validaciones | Media | No identificada |
| F-063 | Catalogos | SAT retenciones | UI + Feature HTTP | CRUD completo; datatable; valores SAT validos; rechazo de duplicados | Media | No identificada |
| F-064 | Empleados | Catalogo de empleados | UI + Feature HTTP | listado; datatable; filtros; render sin foto; datos de jerarquia | Alta | Automatizada |
| F-065 | Empleados | Promocion de empleado | UI + Feature HTTP | promocion valida; cambio de puesto/lider; validaciones; traza en `employee_events` | Alta | Automatizada |
| F-066 | Empleados | Carga de foto | UI + Feature HTTP | upload permitido; validacion de tipo y size; reemplazo de foto; rechazo sin permiso | Media | No identificada |
| F-067 | API empleados | Recepcion externa de empleados | API externa | proteccion por `api.key`; payload valido; payload incompleto; idempotencia por empleado; respuesta HTTP correcta | Critica | No identificada |
| F-068 | API empleados | Resolver lideres pendientes | API externa | asignacion correcta de lideres; rechazo sin api key; ejecucion repetida segura | Alta | No identificada |
| F-070 | Presupuesto | Annual budgets CRUD | UI + Feature HTTP | create/show/edit/delete/index/datatable; ejercicio duplicado; centro invalido; estado de presupuesto | Critica | Parcial |
| F-071 | Presupuesto | Aprobacion de annual budget | UI + Feature HTTP | aprobar presupuesto; rechazo si faltan distribuciones; solo actor autorizado; persistencia de estado | Critica | Parcial |
| F-072 | Presupuesto | Available categories por mes | API interna | categorias disponibles por presupuesto y mes; sin categorias; respuesta consistente con distribuciones | Alta | No identificada |
| F-073 | Presupuesto | Check availability | API interna + Service | suficiente presupuesto; insuficiente presupuesto; centro `FREE_CONSUMPTION`; categorias no configuradas | Critica | Parcial |
| F-074 | Presupuesto | Budget monthly distributions | UI + Feature HTTP | index/datatable/create/store/matrix/show/edit/update; validacion de totales; meses faltantes | Critica | Automatizada |
| F-075 | Presupuesto | Expense categories | UI + API interna | store; select; by-budget; by-cost-center; respuesta correcta a filtros | Alta | No identificada |
| F-076 | Presupuesto | Budget movements CRUD | UI + Feature HTTP | crear traslado; editar; listar; ver detalle; eliminar segun estado; validaciones de montos | Critica | No identificada |
| F-077 | Presupuesto | Budget movement approve | Feature HTTP + Service | aprobacion; afectacion de saldos; auditoria; rechazo de doble aprobacion | Critica | No identificada |
| F-078 | Presupuesto | Budget movement reject | Feature HTTP + Service | rechazo con motivo; restauracion de disponibilidad; auditoria | Alta | No identificada |
| F-079 | Presupuesto | Critical dashboard | UI | mostrar movimientos criticos; filtros; sin datos; consistencia de montos | Media | No identificada |
| F-080 | Presupuesto | Importaciones 2026 por comando | Command + Service | TSA, SMA, GASOMEX y DGA; archivo faltante; formato invalido; upsert correcto; log de resultados | Alta | No identificada |
| F-081 | Presupuesto | Integridad de compromiso/consumo | Service | compromiso al aprobar/emitir orden; consumo al estado final esperado; liberacion en cancelacion/rechazo | Critica | Parcial |
| F-090 | Requisiciones | Requisition CRUD clasico | UI + Feature HTTP | crear; editar; ver; eliminar borrador; cancelar; validaciones de partidas, company, cost center, location | Critica | No identificada |
| F-091 | Requisiciones | Requisition Livewire | UI + Livewire | create-livewire; edit-livewire; agregar/quitar partidas; recalculos; manejo de errores en tiempo real | Alta | No identificada |
| F-092 | Requisiciones | DataTables e inbox de aprobacion | UI + Feature HTTP | listado del solicitante; approval_datatable; filtros; ordenamientos; permisos | Alta | No identificada |
| F-093 | Requisiciones | Review data y validacion tecnica | Feature HTTP | payload de review-data; validate-technical con datos validos e invalidos; persistencia de observaciones | Alta | No identificada |
| F-094 | Requisiciones | Submit a compras | Feature HTTP + Notification | solo desde `DRAFT`; con al menos una partida; cambio a `PENDING`; notificacion a compras y solicitante | Critica | No identificada |
| F-095 | Requisiciones | Hold / pause | Feature HTTP | pausa desde estado permitido; motivo visible; bloqueo de edicion no permitida; reactivacion posterior por listener o flujo | Alta | No identificada |
| F-096 | Requisiciones | Rechazo y cancelacion | Feature HTTP + Notification | rechazo con motivo; cancelacion por flujo CRUD y workflow; actor autorizado; mensajes correctos | Critica | No identificada |
| F-097 | Requisiciones | Validacion para cotizacion | Feature HTTP + Notification | aprobar para quotation; cambio a `IN_QUOTATION`; notificaciones; no duplicidad | Critica | No identificada |
| F-098 | Requisiciones | Consumo manual de requisicion | Feature HTTP + Service | `consume` desde estado valido; impacto en saldos/estado; bloqueo en estados invalidos | Alta | No identificada |
| F-099 | Requisiciones | Reactivacion por listener | Event/Listener | cuando se aprueba producto asociado, la requisicion pausada se reactiva si corresponde | Alta | No identificada |
| F-100 | Cotizaciones | Quotation planner - estrategia | UI + Feature HTTP | abrir planner; guardar estrategia; persistencia por requisicion; reabrir y ver datos previos | Critica | No identificada |
| F-101 | Cotizaciones | Quotation planner - grupos | UI + Feature HTTP | crear grupo; eliminar grupo; no duplicar estructura invalida; borrar grupo con items | Alta | No identificada |
| F-102 | Cotizaciones | Quotation planner - items | UI + Feature HTTP | agregar items a grupo; remover items; evitar items duplicados; mantener trazabilidad | Alta | No identificada |
| F-103 | Cotizaciones | Sugerencias de proveedor | Feature HTTP + Service | sugerencias por grupo; sin sugerencias; proveedores no aprobados excluidos si aplica | Media | No identificada |
| F-104 | RFQ | Creacion de RFQ desde requisicion | UI + Feature HTTP | seleccionar suppliers; generar RFQs; crear multiples por grupo; validacion de fecha limite | Critica | No identificada |
| F-105 | RFQ | Wizard summary y datatable | UI + Feature HTTP | resumen del wizard; datatable; analisis de datos; consistencia con grupos/items | Alta | No identificada |
| F-106 | RFQ | Bandejas received/pending | UI + Feature HTTP | inbox pending y received; data endpoints; modales RFQ y requisicion; filtros | Alta | No identificada |
| F-107 | RFQ | Envio single y envio bulk | Feature HTTP + Notification | `send-single`; `send`; actualizacion de estado; sellado de fecha; no reenvio invalido | Critica | No identificada |
| F-108 | RFQ | Vista interna de RFQ | UI + Feature HTTP | detalle por RFQ; restricciones por actor; no acceso a RFQ ajena | Alta | No identificada |
| F-109 | RFQ | Cancelacion de RFQ | Feature HTTP + Notification | cancelar RFQ; notificar supplier y requester; no cancelar estados cerrados | Alta | No identificada |
| F-110 | RFQ | Portal proveedor - visualizar RFQ | UI + Feature HTTP | supplier abre solo su RFQ; no abre RFQ ajena; datos de partidas y adjuntos correctos | Critica | No identificada |
| F-111 | RFQ | Portal proveedor - guardar cotizacion | UI + Feature HTTP | guardar draft; actualizar propuesta; adjuntar archivo; validaciones de precio/plazo; respuesta final | Critica | No identificada |
| F-112 | RFQ | Portal proveedor - historial y borrado draft | UI + Feature HTTP | historial de cotizaciones; descarga de attachment; delete draft solo del owner y solo si aplica | Alta | No identificada |
| F-113 | RFQ | Comparador de cotizaciones | UI + Feature HTTP | pantalla de comparacion; criterios de seleccion; sin respuestas; multiples grupos/proveedores | Critica | No identificada |
| F-114 | RFQ | Seleccion de ganador | Feature HTTP + Service | seleccionar ganador; actualizar estados; generar resumen o siguiente etapa; no seleccionar supplier no respondio | Critica | No identificada |
| F-115 | RFQ | Reaward y cancel-rejected | Feature HTTP | re-asignar; cancelar rechazados; consistencia de estados y notificaciones | Alta | No identificada |
| F-116 | Aprobaciones | Quotation approvals | UI + Feature HTTP | listado de aprobaciones; `handle` approve/reject; comentarios; actor autorizado; doble accion rechazada | Critica | No identificada |
| F-117 | Productos/servicios | Catalogo CRUD | UI + Feature HTTP | create/index/show/edit/delete/datatable; validaciones; relacion con categorias y unidades | Alta | No identificada |
| F-118 | Productos/servicios | Aprobar/rechazar/desactivar/reactivar | Feature HTTP + Notification/Event | transiciones validas; permisos; reactivacion; notificaciones asociadas | Alta | No identificada |
| F-119 | Productos/servicios | Alta desde requisicion | Feature HTTP | crear producto desde requisicion; vincular con flujo de compra; rechazo de payload invalido | Alta | No identificada |
| F-120 | Compras | Direct purchase order - create/edit/update | UI + Feature HTTP | crear OCD; editar borrador; actualizar items; validaciones de supplier, category, monto, justificacion | Critica | No identificada |
| F-121 | Compras | OCD - categorias disponibles | API interna | endpoint devuelve categorias compatibles por contexto; respuesta vacia controlada | Media | No identificada |
| F-122 | Compras | OCD - submit a aprobacion | Feature HTTP + Service | cambio a `PENDING_APPROVAL`; calculo de nivel; no submit sin datos completos | Critica | No identificada |
| F-123 | Compras | OCD - approve | Feature HTTP + Service + Notification | aprobacion por actor autorizado; compromiso presupuestal; cambio a `ISSUED`; notificacion al proveedor | Critica | No identificada |
| F-124 | Compras | OCD - reject / return | Feature HTTP + Notification | rechazo con motivo; devolucion para correccion; reenvio posterior; auditoria | Critica | No identificada |
| F-125 | Compras | Purchase orders index y detail | UI + Feature HTTP | index general; datatable regular/direct; show regular; showDirect; render con relaciones faltantes | Alta | No identificada |
| F-126 | Compras | Estados de OC/OCD | Service | transiciones permitidas entre `DRAFT`, `PENDING_APPROVAL`, `ISSUED`, `PARTIALLY_RECEIVED`, `RECEIVED`, `CANCELLED`, `CLOSED_BY_INACTIVITY`, `DELIVERED_PENDING_RECEPTION` | Critica | Parcial |
| F-127 | Entregas proveedor | Registro de entregas | UI + Feature HTTP | index; create; store; remision obligatoria; orden correcta; bloqueo si locacion portal bloqueada | Critica | No identificada |
| F-128 | Recepciones | Overview y pendientes | UI + Feature HTTP | overview; pending; datatable regular/direct; filtros por locacion y estado | Alta | No identificada |
| F-129 | Recepciones | Recepcion contra PO regular | UI + Feature HTTP + Service | create/store; recepcion parcial; recepcion total; cantidades excedidas; folio generado | Critica | Automatizada |
| F-130 | Recepciones | Recepcion contra OCD | UI + Feature HTTP + Service | createDirect/storeDirect; parcial/completa; mismo control de cantidades y estados | Critica | Parcial |
| F-131 | Recepciones | No conformidad | UI + Feature HTTP | item no conforme; tipo obligatorio; descripcion amplia; evidencia fotografica; rechazo si falta evidencia | Critica | Parcial |
| F-132 | Recepciones | Show y descarga de remision | UI + Feature HTTP | detalle de recepcion; descarga de remision; 404 si archivo falta; no acceso sin permiso | Alta | No identificada |
| F-133 | Recepciones | Notificacion de recepcion completada | Feature HTTP + Notification | al completar recepcion se notifica a actores esperados; payload correcto | Media | No identificada |
| F-134 | Compras batch | Cierre automatico por inactividad | Command + Scheduler | OC regular a 10 dias; OCD a 7 dias; no cerrar orden activa reciente; sin duplicar notificaciones | Critica | No identificada |
| F-135 | Compras batch | Alertas de entrega dia 0/2/3 | Job + Mail | job genera correo correcto; solo ordenes elegibles; no duplicidad; manejo de orden sin email | Media | No identificada |
| F-140 | Finanzas proveedor | Upload de invoice por supplier | UI + Feature HTTP + Service | listado; create; store; xml/pdf validos; orden/supplier compatible; validaciones de uuid y parseo | Critica | No identificada |
| F-141 | Finanzas internas | Upload de invoice por finanzas | UI + Feature HTTP + Service | create/store/show; seleccion de order `standard` o `direct`; mismatch supplier/order produce 422 | Critica | No identificada |
| F-142 | Finanzas internas | Rechazo de invoice | Feature HTTP + Service | rechazo con motivo; solo `UPLOADED`; bloqueo si ya vinculada; mensaje funcional | Alta | No identificada |
| F-143 | Provisiones | Index y show de financial provisions | UI + Feature HTTP | paginacion; relaciones cargadas; invoices compatibles; datos de receivable correctos | Alta | No identificada |
| F-144 | Provisiones | Link invoice a provision | Feature HTTP + Service | solo provision `PENDING_INVOICE`; invoice compatible; cambio de estado; conciliacion correcta | Critica | Parcial |
| F-145 | Provisiones | Autorizar ajuste | Feature HTTP + Service | solo `DISCREPANCY_REVIEW`; amount y reason validos; cierre de provision; registro de authorizer | Critica | Parcial |
| F-146 | Provisiones | Notificaciones financieras | Notification | `FinancialProvisionPendingInvoiceNotification` y `FinancialProvisionDiscrepancyNotification` disparan cuando corresponde | Alta | No identificada |
| F-147 | Herramientas | CFDI generator - UI | UI + Feature HTTP | acceso solo superadmin; form con datos validos; errores de validacion; descarga XML/PDF | Alta | Automatizada |
| F-148 | Herramientas | CFDI generator service | Service | estructura XML; sello/datos demo; representacion PDF; nombres de archivo; no persistencia en disco | Alta | Automatizada |
| F-149 | Herramientas | CFDI XML parser | Service | parseo de XML valido; rechazo de XML invalido; campos faltantes; montos y RFC bien extraidos | Alta | Automatizada |
| F-150 | Integraciones | SAT EFOS - index/data | UI + Feature HTTP | listado; paginacion/datatable; sin datos; consulta por job status | Media | Parcial |
| F-151 | Integraciones | SAT EFOS - sync manual | Feature HTTP + Job + Command | dispara job; guarda jobId; procesa CSV; actualiza tabla; maneja URL invalida | Critica | Automatizada |
| F-152 | Integraciones | Tipo de cambio - sync | Command + Scheduler | obtiene USD/MXN; inserta o actualiza; no duplica dia/hora; maneja API key faltante | Alta | Automatizada |
| F-153 | Integraciones | TRESS - sync users | Command | lee CSV; crea empleados; actualiza empleados; crea usuarios si hay email; maneja CSV corrupto | Critica | No identificada |
| F-154 | Integraciones | Scheduler registrado | Scheduler | `purchase-orders:close-inactive` a las 00:30; `exchange-rates:sync` hourly weekdays 8-18; `withoutOverlapping` configurado | Alta | Parcial |

## 7. Matriz de pruebas no funcionales

Columnas:

- `Categoria`: alineada a `ISO 25010`.
- `Metodo`: carga, seguridad, resiliencia, accesibilidad, auditoria, configuracion, compatibilidad o recuperacion.

| ID | Categoria | Alcance | Prueba no funcional obligatoria | Metodo | Criterio de aceptacion | Cobertura actual |
|---|---|---|---|---|---|---|
| NF-001 | Seguridad | Autenticacion | intentar acceso a rutas protegidas sin sesion y con sesion expirada | Seguridad | toda ruta protegida responde redirect/login o 401/403 segun corresponda | Parcial |
| NF-002 | Seguridad | Autorizacion por rol | intentar acceso cruzado entre `supplier`, `buyer`, `receiver`, `accounting`, `superadmin`, `authorizer` | Seguridad | no existe fuga de datos ni acceso a acciones ajenas | Parcial |
| NF-003 | Seguridad | API externa empleados | invocar `routes/api.php` sin `api.key`, con key invalida y con key valida | Seguridad | solo requests con key valida ejecutan la accion | No identificada |
| NF-004 | Seguridad | Password reset y verification | evaluar throttle, expiracion y reuso de tokens | Seguridad | no se acepta token expirado/reutilizado; throttle activo | No identificada |
| NF-005 | Seguridad | Upload de archivos | subir XML, PDF, imagenes y documentos con mime falso, extension alterada y tamanos maximos | Seguridad | sistema rechaza archivos invalidos y registra error controlado | No identificada |
| NF-006 | Seguridad | Descarga de archivos | probar acceso directo a remisiones, attachments RFQ, storage proxy y documentos proveedor de terceros | Seguridad | acceso solo al actor autorizado; path traversal bloqueado | No identificada |
| NF-007 | Seguridad | Inyeccion y sanitizacion | busquedas datatable, comentarios, motivos, nombres de archivos y campos libres con payloads de SQLi/XSS | Seguridad | no se ejecuta script ni se rompe la consulta; salida escapada | No identificada |
| NF-008 | Seguridad | Integridad de sesiones | validar cambio de password, lock/unlock, logout y cierre de cuenta | Seguridad | rotacion o invalidacion de sesion cuando aplique | No identificada |
| NF-009 | Seguridad | Datos sensibles | revisar visibilidad de RFC, datos bancarios, UUID, remisiones y correos | Seguridad | minima exposicion por rol y pagina | No identificada |
| NF-010 | Seguridad | Dev logs | validar que visor de logs no sea publico ni indexable | Seguridad | acceso totalmente restringido | No identificada |
| NF-011 | Seguridad | EFOS/TRESS/API keys | ejecutar comandos sin variables requeridas | Configuracion | falla controlada, mensaje claro, sin stacktrace sensible | No identificada |
| NF-012 | Seguridad | CSRF | intentar POST/PUT/DELETE sin token en flujos web | Seguridad | rechazo consistente en operaciones mutables | No identificada |
| NF-020 | Rendimiento | Listados principales | medir `users`, `suppliers`, `employees`, `purchase-orders`, `receptions`, `invoices`, `annual_budgets` con dataset grande | Carga | p95 aceptable definido por QA; sin timeouts ni memory leaks | No identificada |
| NF-021 | Rendimiento | Requisition Livewire | medir latencia al agregar/quitar partidas y recalcular formularios | Carga | interaccion fluida con dataset nominal y alto | No identificada |
| NF-022 | Rendimiento | RFQ comparison | comparar RFQ con muchas partidas y proveedores | Carga | pagina usable y consultas estables | No identificada |
| NF-023 | Rendimiento | Budget availability | ejecutar check-availability concurrente desde requisiciones y OCD | Carga | respuesta consistente y sin sobregiros por carrera | No identificada |
| NF-024 | Rendimiento | Recepciones | registrar recepcion con multiples partidas y evidencias | Carga | respuesta sin timeout; archivos persistidos correctamente | No identificada |
| NF-025 | Rendimiento | Sync EFOS | procesar CSV grande del SAT | Carga | job concluye dentro de ventana operativa y sin agotar memoria | No identificada |
| NF-026 | Rendimiento | Sync TRESS | procesar CSV grande de empleados | Carga | tiempo de importacion controlado; sin duplicados ni bloqueos | No identificada |
| NF-027 | Rendimiento | Scheduler nocturno | cierre masivo de OC/OCD y envio de alertas | Carga | sin solapamientos; finaliza dentro de la ventana de cron | No identificada |
| NF-030 | Confiabilidad | Transacciones criticas | inducir falla intermedia en registro de supplier, RFQ, OCD, recepcion e invoice | Resiliencia | rollback completo; no quedan registros huerfanos | No identificada |
| NF-031 | Confiabilidad | Idempotencia de commands/jobs | ejecutar sync EFOS, sync tipo de cambio, TRESS y cierre inactivo dos veces seguidas | Resiliencia | segunda ejecucion no rompe datos ni duplica efectos | Parcial |
| NF-032 | Confiabilidad | Concurrencia | doble submit, doble approve, doble reception, doble link-invoice | Resiliencia | una sola operacion prevalece; la otra falla controladamente | No identificada |
| NF-033 | Confiabilidad | Queue y notificaciones | simular falla de envio de mail o job fallido | Resiliencia | error observable, reintento controlado, sistema no queda inconsistente | No identificada |
| NF-034 | Confiabilidad | Integridad de estados | forzar transiciones invalidas entre estados de requisicion, RFQ, OC/OCD y recepcion | Resiliencia | bloqueo consistente y mensaje funcional | Parcial |
| NF-035 | Confiabilidad | Scheduler `withoutOverlapping` | lanzar dos instancias del mismo command programado | Resiliencia | no hay ejecucion paralela del mismo proceso | No identificada |
| NF-036 | Confiabilidad | Archivos faltantes | remision/documento/adjunto inexistente despues de haber sido referenciado | Recuperacion | UX controlada; 404 o mensaje claro; no error 500 | No identificada |
| NF-040 | Usabilidad | Formularios criticos | revisar claridad de mensajes y validaciones en registro, requisicion, RFQ, OCD, recepcion e invoice | Usabilidad | mensajes comprensibles y accionables | No identificada |
| NF-041 | Usabilidad | Navegacion | menu, breadcrumbs, acciones primarias y estados vacios en modulos clave | Usabilidad | flujo entendible para usuario objetivo | No identificada |
| NF-042 | Usabilidad | Responsive | supplier dashboard, RFQ supplier, deliveries, invoices, receptions pending y forms principales en movil/tablet | Compatibilidad | operable en resoluciones objetivo sin clipping critico | No identificada |
| NF-043 | Accesibilidad | Formularios y tablas | labels, foco, contraste, keyboard nav, mensajes de error asociados | Accesibilidad | cumplimiento minimo WCAG AA en pantallas criticas | No identificada |
| NF-044 | Compatibilidad | Navegadores | Chrome, Edge y Firefox en flujos staff y supplier | Compatibilidad | sin regresiones visuales o funcionales criticas | No identificada |
| NF-045 | Compatibilidad | Descargas | XML, PDF, remisiones, attachments | Compatibilidad | archivos descargan con nombre, mime y contenido correctos | Parcial |
| NF-050 | Mantenibilidad | Observabilidad de negocio | revisar activity log y campos de auditoria en rechazos, aprobaciones, recepciones, promociones y ajustes | Auditoria | toda accion critica deja huella util para soporte | Parcial |
| NF-051 | Mantenibilidad | Logs operativos | revisar logs de exchange rate y close inactive; errores en jobs/commands | Auditoria | mensajes suficientes para diagnostico y soporte | No identificada |
| NF-052 | Mantenibilidad | Seeders y migraciones | levantar entorno desde cero con migraciones y seeders | Configuracion | entorno reproducible sin intervencion manual oculta | No identificada |
| NF-053 | Mantenibilidad | Paridad de motor DB | ejecutar flujos criticos en SQLite de tests y SQL Server/staging | Compatibilidad tecnica | reglas SQL especificas no rompen en entorno real | No identificada |
| NF-054 | Mantenibilidad | Cobertura automatizada | medir cobertura real por modulo y detectar huecos de regresion | Calidad interna | tablero de cobertura alineado a esta matriz | No identificada |
| NF-055 | Portabilidad operativa | Filesystems configurados | `public`, `tress` y almacenamiento de uploads | Configuracion | rutas, permisos y enlaces funcionan en todos los ambientes | No identificada |
| NF-060 | Integridad de datos | Relaciones y borrados | probar deletes con entidades en uso: suppliers, categories, stations, cost centers, budgets, orders | Integridad | no quedan huerfanos ni cascadas no deseadas | No identificada |
| NF-061 | Integridad de datos | Precision monetaria | revisar redondeo en presupuestos, RFQ, OC/OCD, recepciones y provisiones | Integridad | sumas y diferencias cuadran a precision esperada | No identificada |
| NF-062 | Integridad de datos | Fechas y zonas horarias | validacion de `dailyAt`, `between`, fechas de recepcion, vencimientos y trazas | Integridad | no hay desfaces de horario ni cierre fuera de ventana | No identificada |
| NF-063 | Cumplimiento | CFDI e invoice parsing | UUID, RFC, total, xml/pdf asociados y mensajes de rechazo | Cumplimiento | parseo y validacion fiscal consistentes | Parcial |
| NF-064 | Cumplimiento | EFOS | fuentes inaccesibles, CSV alterado, columnas faltantes | Cumplimiento | falla controlada y no corrompe catalogo EFOS local | Parcial |
| NF-065 | Disponibilidad | Dependencias externas | caida de API de tipo de cambio, SMTP, storage o SAT | Recuperacion | degradacion controlada, alertas y reintentos definidos | No identificada |
| NF-066 | Recuperacion | Backup funcional de evidencia | restaurar docs, remisiones e invoices desde respaldo de staging | Recuperacion | archivos recuperables y rutas vigentes | No identificada |

## 8. Priorizacion recomendada de ejecucion

### 8.1 Oleada 1 - bloqueo de salida a produccion

- F-001 a F-010
- F-020 a F-034
- F-070 a F-081
- F-090 a F-099
- F-104 a F-116
- F-120 a F-145
- F-150 a F-154
- NF-001 a NF-012
- NF-030 a NF-036
- NF-060 a NF-065

### 8.2 Oleada 2 - estabilizacion operativa

- F-040 a F-068
- F-100 a F-103
- F-117 a F-135
- NF-020 a NF-027
- NF-040 a NF-055
- NF-066

## 9. Backlog de automatizacion recomendado

Orden sugerido para ampliar la suite automatizada:

1. Requisiciones end-to-end: crear, enviar, pausar, rechazar, aprobar para quotation.
2. RFQ end-to-end: planner, envio, respuesta supplier, comparador, seleccion, cancelacion.
3. OCD y purchase orders: submit, approve, reject, return, compromiso presupuestal.
4. Supplier deliveries + receptions: portal supplier, locacion bloqueada, parcial/completa, no conformidad.
5. Invoices y financial provisions: upload supplier/finance, link, reject, adjustment.
6. Document review + supplier docs + banca + SIROC.
7. Commands y jobs faltantes: TRESS sync, close inactive, alertas dia 0/2/3.
8. Announcements, incidents, receiving locations, sat retenciones y catalogos restantes.
9. Seguridad no funcional automatizable: access control, uploads, path traversal, auth throttling.

## 10. Criterios de salida sugeridos para QA formal

- 100% de los casos `Critica` ejecutados.
- 0 defectos abiertos `Criticos`.
- 0 defectos abiertos `Altos` en flujos de compra, recepcion, presupuesto o facturacion.
- 100% de las integraciones externas validadas con caso feliz y fallo controlado.
- 100% de los permisos de roles validados al menos en rutas criticas.
- Evidencia archivada por caso: request/response, screenshot, log y resultado de DB cuando aplique.

## 11. Observaciones de la revision

- La matriz cubre todo lo observable en el codigo actual, incluyendo modulos maduros y modulos con alcance aun pendiente de validacion funcional.
- La cobertura automatizada actual es util pero todavia parcial frente al tamano real del sistema.
- Los mayores riesgos de regresion hoy estan en `RFQ`, `compras`, `portal proveedor`, `facturacion/provisiones` y `jobs/commands` con efectos de negocio.
