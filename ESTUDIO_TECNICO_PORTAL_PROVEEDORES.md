# Estudio Técnico - Portal de Proveedores

## Información General del Proyecto

### Nombre del Proyecto
**Portal de Proveedores** - Sistema de gestión de proveedores y requisiciones para la empresa TotalGas

### Stack Tecnológico Principal

#### Backend
- **Framework**: Laravel 12.0
- **Versión PHP**: ^8.2
- **Base de Datos**: SQL Server 2019
- **Driver**: sqlsrv con configuración específica para UTF-8

#### Frontend
- **Template**: Zircos Admin Template
- **Framework CSS**: Bootstrap 5
- **Framework Utility**: TailwindCSS 3.1.0
- **Iconos**: Tabler Icons (integrados en Zircos)
- **Alertas**: SweetAlert2
- **Tablas**: DataTables.net
- **Build Tool**: Vite 7.0.4
- **JavaScript**: AlpineJS 3.4.2

#### Librerías PHP Principales
- **Livewire**: ^3.7 (componentes reactivos)
- **Spatie Laravel Permission**: ^6.21 (gestión de roles y permisos)
- **Spatie Laravel Activitylog**: ^4.10 (auditoría)
- **Yajra Laravel DataTables**: ^12.6 (tablas dinámicas)
- **Barryvdh Laravel DomPDF**: ^3.1 (generación PDF)

## Arquitectura del Sistema

### Estructura de Directorios
```
/var/www/suppliersPortal/
├── app/
│   ├── Console/Commands/
│   ├── Enum/
│   ├── Events/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   └── Models/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── vendor/
│   └── images/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
└── storage/
```

### Configuración de Base de Datos

#### SQL Server 2019
- **Host**: 192.168.0.6
- **Puerto**: 1433
- **Base de Datos**: suppliersPortalDB
- **Usuario**: cguser
- **Charset**: UTF-8
- **Trust Certificate**: true
- **Encoding**: PDO::SQLSRV_ENCODING_UTF8

#### Modos SQL Server configurados:
- ANSI_NULLS
- ANSI_PADDING
- ANSI_WARNINGS
- ARITHABORT
- CONCAT_NULL_YIELDS_NULL
- QUOTED_IDENTIFIER

## Modelos de Datos Principales

### Entidades del Sistema

#### Gestión de Usuarios
- **User**: Gestión de usuarios del sistema
- **Employee**: Información de empleados
- **Company**: Empresas proveedoras

#### Gestión de Proveedores
- **Supplier**: Proveedores registrados
- **SupplierDocument**: Documentación de proveedores
- **SupplierBank**: Información bancaria
- **SupplierSiroc**: Integración con SIROC
- **CatSupplier**: Catálogo de proveedores
- **SatEfos69b**: Validación SAT EFOS

#### Gestión de Requisiciones
- **Requisition**: Requisiciones de compra
- **RequisitionItem**: Ítems de requisición
- **ProductService**: Catálogo de productos y servicios
- **Category**: Categorías de productos

#### Gestión Presupuestal
- **AnnualBudget**: Presupuestos anuales
- **BudgetMovement**: Movimientos presupuestarios
- **BudgetMovementDetail**: Detalles de movimientos
- **BudgetMonthlyDistribution**: Distribución mensual

#### Organización
- **Department**: Departamentos
- **CostCenter**: Centros de costo
- **Station**: Estaciones
- **ExpenseCategory**: Categorías de gasto

#### Comunicación
- **Announcement**: Anuncios del sistema
- **AnnouncementSupplier**: Anuncios por proveedor
- **Incident**: Gestión de incidentes

### Enumeraciones
- **RequisitionStatus**: Estados de requisición
- **ProductServiceStatus**: Estados de productos/servicios
- **Currency**: Tipos de moneda

## Controladores Principales

### Gestión de Autenticación
- **AuthenticatedSessionController**: Login/Logout
- **RegisteredUserController**: Registro de usuarios
- **SupplierRegistrationController**: Registro de proveedores
- **PasswordController**: Gestión de contraseñas

### Gestión del Sistema
- **UserController**: Administración de usuarios
- **ProfileController**: Perfiles de usuario
- **LockScreenController**: Bloqueo de pantalla

### Gestión de Proveedores
- **SupplierController**: CRUD de proveedores
- **SupplierDocumentController**: Gestión documental
- **SupplierBankController**: Datos bancarios
- **DocumentReviewController**: Revisión de documentos

### Gestión de Requisiciones
- **RequisitionController**: Gestión de requisiciones
- **RequisitionWorkflowController**: Flujo de aprobación
- **ProductServiceController**: Catálogo de productos

### Gestión Presupuestal
- **AnnualBudgetController**: Presupuestos anuales
- **BudgetMovementController**: Movimientos
- **BudgetMonthlyDistributionController**: Distribución mensual

## Sistema de Permisos y Roles

### Roles Implementados
- **supplier**: Proveedor
- **admin**: Administrador
- **manager**: Gerente
- **user**: Usuario básico

### Permisos por Módulo
- Gestión de usuarios
- Gestión de proveedores
- Gestión de requisiciones
- Gestión presupuestal
- Reportes y auditoría

## Frontend y UI

### Template Zircos
- **Layout Base**: `layouts/zircos.blade.php`
- **Sistema de Breadcrumbs**: Navegación jerárquica
- **Sidebar**: Menú contextual por rol
- **Header**: Información de usuario y notificaciones

### Componentes UI
- **DataTables**: Tablas con paginación, filtros y exportación
- **SweetAlert2**: Alertas y confirmaciones
- **Bootstrap 5**: Componentes UI responsivos
- **Tabler Icons**: Iconos modernos y consistentes

### Assets Organización
```
public/assets/
├── css/
│   ├── vendor.min.css
│   ├── app.min.css
│   └── icons.min.css
├── js/
│   ├── vendor.min.js
│   ├── app.min.js
│   └── config.js
└── vendor/
    ├── datatables.net-bs5/
    ├── sweetalert2/
    └── [otros vendors]
```

## Características Técnicas

### Seguridad
- **Autenticación**: Laravel Breeze + Spatie Permission
- **CSRF Protection**: Token de sesión
- **Soft Deletes**: Eliminación lógica en modelos críticos
- **Auditoría**: Activity Log para cambios importantes

### Rendimiento
- **Caching**: Configuración para Redis
- **Queue System**: Procesamiento asíncrono
- **Lazy Loading**: Carga optimizada de relaciones

### Internacionalización
- **Idioma Principal**: Español (es)
- **Configuración Regional**: Formatos de fecha y moneda

## Configuración de Desarrollo

### Entorno
- **APP_ENV**: local/production
- **APP_DEBUG**: true/false
- **APP_URL**: Dominio de la aplicación

### Servicios Externos
- **Redis**: Cache y sesiones
- **Mail**: Configuración SMTP
- **Filesystem**: Almacenamiento local

## Flujo de Trabajo Principal

### Registro de Proveedores
1. Registro inicial de usuario proveedor
2. Completar información de empresa
3. Cargar documentación requerida
4. Revisión por administrador
5. Aprobación/rechazo

### Gestión de Requisiciones
1. Creación de requisición
2. Asignación de productos/servicios
3. Flujo de aprobación
4. Procesamiento y seguimiento

### Control Presupuestal
1. Definición de presupuesto anual
2. Distribución mensual
3. Control de movimientos
4. Reportes y análisis

## Consideraciones Técnicas

### Base de Datos
- **Migraciones**: Control de versiones de esquema
- **Seeders**: Datos iniciales de prueba
- **Relaciones**: Modelo E-ORM optimizado

### Frontend
- **Vite**: Build tool moderno y rápido
- **TailwindCSS**: Utility-first CSS
- **AlpineJS**: Interactividad sin complejidad

### Backend
- **Livewire**: Componentes reactivos sin API separada
- **Events**: Sistema de eventos desacoplado
- **Jobs**: Procesamiento en segundo plano

## Recursos Adicionales

### Documentación
- **Laravel 12**: Framework principal
- **Zircos Template**: Documentación UI
- **Spatie Packages**: Gestión de permisos y auditoría

### Herramientas de Desarrollo
- **Laravel Pint**: Formato de código
- **PHPUnit**: Testing unitario
- **Laravel Sail**: Docker para desarrollo

---

**Fecha del Estudio**: Enero 2026  
**Versión del Documento**: 1.0  
**Estado**: Activo y en producción
