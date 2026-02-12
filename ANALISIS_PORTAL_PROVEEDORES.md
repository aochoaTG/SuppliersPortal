# Análisis del Portal de Proveedores - TotalGas

## 1. Propósito del Sistema

El Portal de Proveedores de TotalGas es una plataforma integral que facilita la gestión de proveedores, documentación, presupuestos, requisiciones y flujos de trabajo asociados. El sistema está diseñado para:

- Gestionar el ciclo de vida completo de los proveedores (alta, documentación, validación)
- Administrar la documentación requerida de los proveedores con revisión y aprobación
- Gestionar presupuestos anuales y su distribución mensual
- Controlar el flujo de requisiciones de compra
- Administrar catálogos de productos y servicios
- Gestionar centros de costo, departamentos y categorías
- Proporcionar un portal de autogestión para proveedores
- Gestionar el cumplimiento regulatorio (REPSE, SIROC, etc.)

## 2. Estructura de la Base de Datos

### Tablas Principales

#### Usuarios y Autenticación
- `users`: Almacena información de usuarios del sistema (tanto personal interno como proveedores)
- `model_has_roles`: Asigna roles a los usuarios (usando Spatie Permissions)
- `permissions` y `model_has_permissions`: Permisos del sistema

#### Gestión de Proveedores
- `suppliers`: Información detallada de los proveedores
- `supplier_documents`: Documentos cargados por los proveedores
- `supplier_sirocs`: Registros SIROC (Sistema de Registro de Operadores de Combustibles)
- `cat_suppliers`: Catálogo de proveedores
- `sat_efos69b`: Registros del SAT para validación fiscal

#### Gestión de Empresas y Ubicaciones
- `companies`: Empresas del grupo TotalGas
- `stations`: Estaciones de servicio
- `departments`: Departamentos de la organización

#### Gestión Presupuestal
- `annual_budgets`: Presupuestos anuales
- `budget_monthly_distributions`: Distribución mensual del presupuesto
- `budget_movements`: Movimientos del presupuesto
- `budget_movement_details`: Detalle de los movimientos presupuestales
- `cost_centers`: Centros de costo
- `expense_categories`: Categorías de gasto

#### Gestión de Requisiciones
- `requisitions`: Cabecera de las requisiciones
- `requisition_items`: Líneas de detalle de las requisiciones
- `products_services`: Catálogo de productos y servicios
- `categories`: Categorías para clasificar productos/servicios

#### Comunicaciones
- `announcements`: Comunicados/noticias
- `announcement_suppliers`: Relación entre comunicados y proveedores
- `incidents`: Incidencias reportadas por usuarios

### Relaciones Principales

1. **Usuarios y Proveedores**
   - Un `User` puede tener un `Supplier` (relación 1:1)
   - Un `User` puede tener múltiples `Company` (relación muchos a muchos)
   - Un `User` puede tener múltiples roles (usando Spatie Permissions)

2. **Documentos del Proveedor**
   - Un `Supplier` tiene muchos `SupplierDocument`
   - Los documentos pueden tener diferentes estados: pendiente, aprobado, rechazado

3. **Gestión Presupuestal**
   - Un `AnnualBudget` tiene muchas `BudgetMonthlyDistribution`
   - Un `BudgetMovement` está relacionado con un `CostCenter` y una `ExpenseCategory`

4. **Requisiciones**
   - Una `Requisition` tiene muchos `RequisitionItem`
   - Un `RequisitionItem` está relacionado con un `ProductService`

## 3. Lógica de Negocio

### Validaciones

1. **Validación de Proveedores**
   - Validación de RFC con el SAT
   - Validación de documentos requeridos
   - Validación de información bancaria
   - Validación de fechas de vencimiento de documentos

2. **Validación de Presupuestos**
   - Validación de montos disponibles
   - Validación de períodos presupuestales
   - Validación de distribución mensual (debe sumar 100%)

3. **Validación de Requisiciones**
   - Validación de disponibilidad presupuestal
   - Validación de flujos de aprobación
   - Validación de montos y cantidades

### Form Requests

El sistema utiliza Form Requests de Laravel para la validación de datos. Algunos ejemplos incluyen:

- `StoreSupplierRequest`: Para la creación/actualización de proveedores
- `DocumentUploadRequest`: Para la carga de documentos
- `RequisitionRequest`: Para la creación/actualización de requisiciones
- `BudgetMovementRequest`: Para movimientos presupuestales

### Flujos de Trabajo

1. **Onboarding de Proveedores**
   - Registro inicial
   - Carga de documentación requerida
   - Revisión y aprobación
   - Activación del proveedor

2. **Proceso de Requisiciones**
   - Creación de requisición
   - Aprobaciones según el flujo definido
   - Ejecución y seguimiento
   - Cierre y facturación

3. **Gestión Presupuestal**
   - Creación del presupuesto anual
   - Distribución mensual
   - Ajustes y movimientos
   - Seguimiento y reportes

## 4. Arquitectura Técnica

### Tecnologías Principales
- **Backend**: PHP 8.2.x con Laravel 12.x
- **Frontend**: Blade con Zircos Template
- **Framework CSS**: Bootstrap 5
- **Base de Datos**: SQL Server
- **Autenticación**: Sistema de autenticación personalizado con roles y permisos (Spatie)
- **Almacenamiento**: Sistema de archivos local

### Estructura de Directorios

```
app/
  ├── Http/
  │   ├── Controllers/    # Controladores de la aplicación
  │   └── Requests/       # Form Requests para validación
  ├── Models/             # Modelos Eloquent
  ├── Policies/           # Políticas de autorización
  └── Services/           # Lógica de negocio

resources/
  ├── views/             # Vistas Blade
  └── js/                # JavaScript/TypeScript

database/
  ├── migrations/        # Migraciones de la base de datos
  └── seeders/           # Seeders para datos iniciales

routes/
  ├── web.php           # Rutas web
  └── api.php           # Rutas de API (si aplica)
```

## 5. Estado Actual y Próximos Pasos

### Módulos Implementados
- [x] Gestión de Usuarios y Roles
- [x] Gestión de Proveedores
- [x] Carga y Revisión de Documentos
- [x] Gestión de Presupuestos
- [x] Catálogo de Productos/Servicios
- [x] Requisiciones y Flujos de Aprobación
- [x] Comunicaciones (Anuncios)

### Próximas Mejoras
- [ ] Implementación completa de la API REST
- [ ] Integración con sistemas contables
- [ ] Reportes avanzados y dashboards
- [ ] Notificaciones en tiempo real
- [ ] Mejoras en la experiencia móvil

## 6. Consideraciones de Seguridad

1. **Autenticación y Autorización**
   - Autenticación con verificación de correo electrónico
   - Roles y permisos granulares
   - Protección CSRF en formularios

2. **Protección de Datos**
   - Encriptación de datos sensibles
   - Validación de entrada
   - Protección contra inyección SQL

3. **Seguridad de Archivos**
   - Validación de tipos de archivo
   - Escaneo de malware
   - Control de acceso a documentos

## 7. Documentación Adicional

- [Documentación de API](ruta/a/documentacion-api)
- [Guía de Usuario](ruta/a/guia-usuario)
- [Procedimientos de Backup](ruta/a/backup-procedures)

---

*Última actualización: 6 de enero de 2026*
