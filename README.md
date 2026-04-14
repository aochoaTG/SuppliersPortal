# Portal de Proveedores - TotalGas

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Sistema integral de gestión de proveedores, requisiciones, presupuestos y flujos de trabajo para **TotalGas**.

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Stack Tecnológico](#-stack-tecnológico)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Roles y Permisos](#-roles-y-permisos)
- [Desarrollo](#-desarrollo)
- [Testing](#-testing)
- [Documentación](#-documentación)
- [Seguridad](#-seguridad)
- [Licencia](#-licencia)

---

## ✨ Características

### Gestión de Proveedores
- ✅ Registro y onboarding de proveedores
- ✅ Carga y revisión de documentación (RFC, constancias, certificaciones)
- ✅ Validación SAT (EFOS 69B)
- ✅ Gestión de información bancaria
- ✅ Registro SIROC (Sistema de Registro de Operadores de Combustibles)
- ✅ Control de vencimiento de documentos

### Requisiciones de Compra
- ✅ Creación y seguimiento de requisiciones
- ✅ Flujos de aprobación configurables
- ✅ Planificador de cotización (Quotation Planner)
- ✅ Solicitud de cotización (RFQ)
- ✅ Comparador de cotizaciones
- ✅ Órdenes de compra directas

### Gestión Presupuestal
- ✅ Presupuestos anuales
- ✅ Distribución mensual porcentual
- ✅ Movimientos y ajustes presupuestales
- ✅ Validación de disponibilidad en tiempo real
- ✅ Centros de costo y categorías de gasto

### Catálogos
- ✅ Productos y servicios
- ✅ Empresas y estaciones
- ✅ Departamentos y centros de costo
- ✅ Impuestos
- ✅ Categorías

### Comunicación
- ✅ Anuncios y comunicados
- ✅ Gestión de incidencias
- ✅ Notificaciones del sistema

### Seguridad y Auditoría
- ✅ Autenticación con bloqueo de pantalla
- ✅ Roles y permisos granulares (Spatie)
- ✅ Bitácora de actividades (Activity Log)
- ✅ Log viewer integrado

---

## 🛠️ Stack Tecnológico

### Backend
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **PHP** | ^8.2 | Lenguaje de programación |
| **Laravel** | 12.0 | Framework MVC |
| **SQL Server** | 2019 | Base de datos |
| **Livewire** | ^3.7 | Componentes reactivos |

### Frontend
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **Zircos Template** | - | UI Admin Template |
| **Bootstrap 5** | 5.x | Framework CSS |
| **TailwindCSS** | 3.1.0 | Utility-first CSS |
| **AlpineJS** | 3.4.2 | JavaScript ligero |
| **DataTables** | - | Tablas dinámicas |
| **SweetAlert2** | - | Alertas y confirmaciones |
| **Vite** | 7.0.4 | Build tool |

### Paquetes Principales
```json
{
  "spatie/laravel-permission": "^6.21",
  "spatie/laravel-activitylog": "^4.10",
  "yajra/laravel-datatables-oracle": "^12.6",
  "barryvdh/laravel-dompdf": "^3.1",
  "barryvdh/laravel-debugbar": "^4.0",
  "laravel/breeze": "^2.3"
}
```

---

## 📦 Requisitos

- PHP >= 8.2
- Composer
- SQL Server 2019 o superior
- Node.js >= 18.x
- NPM >= 9.x

### Extensiones PHP Requeridas
```
pdo_sqlsrv
sqlsrv
mbstring
tokenizer
curl
gd
xml
zip
```

---

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
git clone <repository-url>
cd SuppliersPortal
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Instalar dependencias de Node.js
```bash
npm install
```

### 4. Configurar variables de entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurar base de datos
Editar `.env` con las credenciales de SQL Server:
```env
DB_CONNECTION=sqlsrv
DB_HOST=192.168.0.6
DB_PORT=1433
DB_DATABASE=suppliersPortalDB
DB_USERNAME=cguser
DB_PASSWORD=your_password
```

### 6. Ejecutar migraciones y seeders
```bash
php artisan migrate --seed
```

### 7. Compilar assets
```bash
# Desarrollo (con hot reload)
npm run dev

# Producción
npm run build
```

### 8. Iniciar el servidor
```bash
# Opción 1: Servidor de desarrollo completo
composer dev

# Opción 2: Servidor Laravel
php artisan serve
```

Acceder a: `http://localhost:8000`

---

## ⚙️ Configuración

### Variables de Entorno Principales

```env
# Aplicación
APP_NAME="Portal de Proveedores"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Base de Datos (SQL Server)
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=suppliersPortalDB
DB_USERNAME=sa
DB_PASSWORD=secret

# Sesión
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Cola
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@totalgas.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuración de SQL Server

La conexión incluye configuración específica para UTF-8:
```php
// config/database.php
'options' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
],
```

---

## 📁 Estructura del Proyecto

```
SuppliersPortal/
├── app/
│   ├── Console/Commands/       # Comandos Artisan personalizados
│   ├── Enum/                   # Enumeraciones (Status, Currency, etc.)
│   ├── Events/                 # Eventos del sistema
│   ├── Http/
│   │   ├── Controllers/        # Controladores (42 controllers)
│   │   ├── Middleware/         # Middleware (LockScreen, etc.)
│   │   └── Requests/           # Validaciones (Form Requests)
│   ├── Models/                 # Modelos Eloquent (43 modelos)
│   ├── Policies/               # Políticas de autorización
│   └── Services/               # Lógica de negocio
├── bootstrap/                  # Bootstrap de la aplicación
├── config/                     # Archivos de configuración
├── database/
│   ├── factories/              # Factories para testing
│   ├── migrations/             # Migraciones de BD
│   └── seeders/                # Seeders de datos iniciales
├── docs/                       # Documentación del proyecto
├── lang/                       # Traducciones y localization
├── public/
│   └── assets/                 # CSS, JS, vendors
├── resources/
│   ├── css/                    # Estilos personalizados
│   ├── js/                     # JavaScript personalizado
│   └── views/                  # Vistas Blade
├── routes/
│   ├── web.php                 # Rutas web (539 líneas)
│   └── api.php                 # Rutas API
├── storage/                    # Archivos generados
├── tests/                      # Tests PHPUnit
└── .env                        # Variables de entorno
```

---

## 🏗️ Módulos del Sistema

| Módulo | Controlador | Descripción |
|--------|-------------|-------------|
| **Auth** | `Auth/*` | Login, registro, recuperación de contraseña |
| **Usuarios** | `UserController` | Gestión de usuarios, roles, empresas asignadas |
| **Proveedores** | `SupplierController` | CRUD de proveedores, información fiscal |
| **Documentos** | `SupplierDocumentController` | Carga, revisión y aprobación de documentos |
| **SIROC** | `SupplierSirocController` | Gestión de registros SIROC |
| **Bancos** | `SupplierBankController` | Información bancaria y cuentas CLABE |
| **Requisiciones** | `RequisitionController` | Creación y seguimiento de requisiciones |
| **Workflow** | `RequisitionWorkflowController` | Aprobaciones y transiciones de estado |
| **RFQ** | `RfqController` | Solicitud y comparación de cotizaciones |
| **Presupuestos** | `AnnualBudgetController` | Presupuestos anuales y distribuciones |
| **Movimientos** | `BudgetMovementController` | Ajustes y transferencias presupuestales |
| **Catálogos** | `ProductServiceController` | Productos, servicios y categorías |
| **Empresas** | `CompanyController` | Empresas del grupo TotalGas |
| **Estaciones** | `StationController` | Estaciones de servicio |
| **Centros de Costo** | `CostCenterController` | Centros de costo por empresa |
| **Anuncios** | `AnnouncementController` | Comunicados y noticias |
| **Incidencias** | `IncidentController` | Reporte y seguimiento de incidencias |
| **SAT EFOS** | `SatEfos69bController` | Consulta de contribuyentes EFOS |
| **Órdenes de Compra** | `PurchaseOrderController` | Gestión de órdenes de compra |
| **Recepción** | `ReceptionController` | Recepción de mercancías |

---

## 🔐 Roles y Permisos

El sistema utiliza **Spatie Laravel Permission** para gestión de acceso.

### Roles Principales
| Rol | Descripción | Permisos Típicos |
|-----|-------------|------------------|
| `admin` | Administrador del sistema | Acceso completo |
| `manager` | Gerente | Aprobaciones, reportes |
| `user` | Usuario interno | Requisiciones, consultas |
| `supplier` | Proveedor | Portal de autogestión |

### Permisos por Módulo
- `users.*` - Gestión de usuarios
- `suppliers.*` - Gestión de proveedores
- `requisitions.*` - Gestión de requisiciones
- `budgets.*` - Gestión presupuestal
- `documents.*` - Gestión documental
- `reports.*` - Reportes y auditoría

### Asignación de Permisos
```php
// Asignar rol
$user->assignRole('admin');

// Asignar permiso
$user->givePermissionTo('requisitions.create');

// Verificar
$user->hasRole('admin');
$user->can('requisitions.create');
```

---

## 💻 Desarrollo

### Comandos Disponibles

```bash
# Desarrollo (servidor + queue + logs + vite)
composer dev

# Servidor Laravel
php artisan serve

# Vite (hot reload)
npm run dev

# Build de producción
npm run build

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Code Style
```bash
# Formatear código con Laravel Pint
php artisan pint

# Ejecutar tests
php artisan test
```

---

## 🧪 Testing

```bash
# Ejecutar suite de tests
php artisan test

# Con coverage (requiere Xdebug)
php artisan test --coverage

# Test específico
php artisan tests/Feature/RequisitionTest.php
```

---

## 📖 Documentación

| Documento | Descripción |
|-----------|-------------|
| [ANALISIS_PORTAL_PROVEEDORES.md](ANALISIS_PORTAL_PROVEEDORES.md) | Análisis funcional del sistema |
| [ESTUDIO_TECNICO_PORTAL_PROVEEDORES.md](ESTUDIO_TECNICO_PORTAL_PROVEEDORES.md) | Estudio técnico y arquitectura |
| [docs/](docs/) | Documentación adicional |

### Documentación Externa
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Livewire Docs](https://livewire.laravel.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Zircos Template](https://coderthemes.com/zircos/)

---

## 🔒 Seguridad

### Medidas Implementadas
- ✅ Protección CSRF en formularios
- ✅ Validación de entrada con Form Requests
- ✅ Autenticación con email verificado
- ✅ Roles y permisos granulares
- ✅ Encriptación de datos sensibles
- ✅ Soft deletes en modelos críticos
- ✅ Activity log para auditoría
- ✅ Bloqueo de pantalla por inactividad

### Vulnerabilidades
Si descubres una vulnerabilidad, por favor repórtala a: **security@totalgas.com**

---

## 📄 Licencia

El Portal de Proveedores es software propietario de **TotalGas**. Todos los derechos reservados.

El framework Laravel es open-source bajo la [licencia MIT](https://opensource.org/licenses/MIT).

---

## 👥 Equipo

**Desarrollado por:** Equipo de Desarrollo - TotalGas  
**Última actualización:** Abril 2026  
**Versión:** 1.0.0

---

## 🙏 Agradecimientos

- [Laravel](https://laravel.com) - Framework PHP
- [Spatie](https://spatie.be) - Paquetes de permisos y activity log
- [Yajra](https://github.com/yajra) - Laravel DataTables
- [Zircos](https://coderthemes.com) - Admin Template
- [Tabler Icons](https://tabler-icons.io) - Iconos

---

<p align="center">
  <img src="https://img.shields.io/badge/Hecho_con-Laravel-red?style=for-the-badge&logo=laravel" alt="Made with Laravel">
</p>
