---
description: Genera un CRUD completo de manera rápida con configuración mínima
---

# CRUD - Generación Rápida de CRUD

**Comando**: `/crud [nombre]`  
**Descripción**: Genera un CRUD completo de manera rápida con configuración mínima
**Ejemplo**: `/crud proveedores`

---

## 🎯 Objetivo

Crear CRUD funcional en el menor tiempo posible usando convenciones estándar de TotalGas. Ideal para:
- Catálogos simples
- Tablas de configuración
- Módulos sin lógica compleja
- Prototipado rápido

---

## 📋 Proceso

### Paso 1: Validar Entrada
```
🚀 CRUD RÁPIDO

Generando CRUD para: [NOMBRE]

Validando nombre...
```

**Validaciones:**
- Nombre en minúsculas
- Sin espacios (usar guiones o underscore)
- Singular (automáticamente pluraliza para tabla)
- No debe existir ya

Si hay error:
```
❌ Error: "[NOMBRE]" no es válido

Ejemplos correctos:
✓ proveedor
✓ producto
✓ orden-compra
✓ tipo_pago

Ejemplos incorrectos:
✗ Proveedor (mayúscula)
✗ orden de compra (espacios)
✗ proveedores (plural)

Corrige el nombre: _______
```

---

### Paso 2: Configuración de Campos
```
🚀 CRUD RÁPIDO - Paso 1/2

📋 Configuración de campos para: [NOMBRE]

Campos estándar incluidos por defecto:
✓ id (autoincremental)
✓ created_at (fecha creación)
✓ updated_at (fecha actualización)

Configuración base (campos comunes):

Opción 1: Estándar Simple
  - nombre (string 255, requerido, único)
  - descripcion (text, opcional)
  - activo (boolean, default true)

Opción 2: Estándar con Contacto
  - nombre (string 255, requerido, único)
  - contacto (string 255, opcional)
  - telefono (string 20, opcional)
  - email (string 255, opcional)
  - activo (boolean, default true)

Opción 3: Personalizado
  - Defines tus propios campos

¿Qué opción prefieres? (1/2/3)
```

**Si elige Opción 1 o 2:**
```
Perfecto, usando campos estándar.

¿Necesitas soft deletes (papelera)? (sí/no)
[Si dice sí, agrega deleted_at]
```

**Si elige Opción 3:**
```
Define tus campos (formato: nombre:tipo:reglas)

Ejemplos:
  rfc:string:required|size:13|unique
  precio:decimal:required|min:0
  cantidad:integer:required|min:1
  fecha_entrega:date:nullable
  archivo:string:nullable

Escribe tus campos (uno por línea):
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
_____________________________________________
_____________________________________________
_____________________________________________
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

(Presiona Enter dos veces cuando termines)
```

Valida campos:
```
🔍 Validando campos...

✓ rfc: string(13), required, unique
✓ precio: decimal(10,2), required, min:0
✓ cantidad: integer, required, min:1

¿Confirmas estos campos? (sí/no/modificar)
```

---

### Paso 3: Relaciones (Opcional)
```
🚀 CRUD RÁPIDO - Paso 2/2

🔗 ¿Este módulo tiene relaciones con otros?

Ejemplo: "Productos" pertenece a "Proveedores"

Relaciones disponibles:
1. belongsTo (N:1) - Muchos registros pertenecen a uno
2. hasMany (1:N) - Un registro tiene muchos
3. belongsToMany (N:M) - Muchos a muchos
4. Ninguna

Opción: _______
```

**Si elige belongsTo:**
```
¿A qué tabla pertenece?
Ejemplo: proveedores, categorias, usuarios

Tabla padre: _______

Esto agregará:
- Campo: [tabla_singular]_id (foreign key)
- Relación: belongsTo([Modelo])
- Validación: required|exists:[tabla],id

¿Correcto? (sí/no)
```

**Si elige hasMany:**
```
¿Qué tabla tiene muchos de estos?
Ejemplo: Un proveedor tiene muchos productos

Tabla hija: _______

Esto agregará:
- Relación en modelo: hasMany([Modelo])
- No modifica esta tabla, sino la tabla hija

¿Correcto? (sí/no)
```

---

### Paso 4: Generación Automática

Una vez confirmado:
```
⚡ GENERANDO CRUD...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[1/8] Creando migración...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

php artisan make:migration create_[tabla]_table

✓ database/migrations/YYYY_MM_DD_HHMMSS_create_[tabla]_table.php

Contenido generado:
- id (bigIncrements)
- [campos del paso 2]
- [foreign keys si aplica]
- timestamps
- [softDeletes si aplica]
- índices en campos de búsqueda

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[2/8] Creando modelo Eloquent...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

php artisan make:model [Modelo]

✓ app/Models/[Modelo].php

Incluye:
- $fillable (todos los campos editables)
- $casts (tipos correctos)
- Relaciones definidas
- Scope activos()
- Accessor nombre_completo

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[3/8] Creando Form Requests...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

php artisan make:request Store[Modelo]Request
php artisan make:request Update[Modelo]Request

✓ app/Http/Requests/Store[Modelo]Request.php
✓ app/Http/Requests/Update[Modelo]Request.php

Validaciones configuradas:
- Reglas según campos definidos
- Mensajes en español
- prepareForValidation() para normalizar datos

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[4/8] Creando Controller...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

php artisan make:controller [Modelo]Controller --resource

✓ app/Http/Controllers/[Modelo]Controller.php

Métodos implementados:
- index() con DataTable server-side
- store() con transacciones
- show() JSON response
- update() con validación
- destroy() con verificación de relaciones

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[5/8] Registrando rutas...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ routes/web.php

Agregado:
Route::resource('[ruta]', [Modelo]Controller::class);

Rutas generadas:
GET    /[ruta]           → index
POST   /[ruta]           → store
GET    /[ruta]/{id}      → show
PUT    /[ruta]/{id}      → update
DELETE /[ruta]/{id}      → destroy

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[6/8] Creando vistas Blade...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ resources/views/[ruta]/index.blade.php
✓ resources/views/[ruta]/partials/modal-form.blade.php

Características:
- Layout Zircos integrado
- Breadcrumb automático
- DataTable responsive
- Modal Bootstrap 5
- Formulario con todos los campos
- Validación HTML5

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[7/8] Creando JavaScript...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ resources/js/[ruta]/index.js

Funcionalidades:
- DataTable Yajra configurado
- AJAX CRUD completo
- Validación jQuery
- SweetAlert2 confirmaciones
- Manejo de errores 422/500
- Loading states

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[8/8] Ejecutando migración...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

php artisan migrate

✓ Tabla [tabla] creada en SQL Server
✓ Índices aplicados
✓ Foreign keys configuradas
```

---

### Paso 5: Verificación y Testing
```
✅ CRUD GENERADO EXITOSAMENTE

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📦 ARCHIVOS CREADOS (9 archivos)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Backend (5 archivos):
✓ database/migrations/YYYY_MM_DD_create_[tabla]_table.php
✓ app/Models/[Modelo].php
✓ app/Http/Requests/Store[Modelo]Request.php
✓ app/Http/Requests/Update[Modelo]Request.php
✓ app/Http/Controllers/[Modelo]Controller.php

Frontend (3 archivos):
✓ resources/views/[ruta]/index.blade.php
✓ resources/views/[ruta]/partials/modal-form.blade.php
✓ resources/js/[ruta]/index.js

Configuración (1 archivo):
✓ routes/web.php (modificado)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎯 CARACTERÍSTICAS IMPLEMENTADAS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ CRUD completo (Create, Read, Update, Delete)
✓ DataTable server-side con Yajra
✓ Búsqueda en tiempo real
✓ Ordenamiento por columnas
✓ Paginación automática
✓ Modal crear/editar
✓ Validación frontend (HTML5 + jQuery)
✓ Validación backend (Form Requests)
✓ Confirmación eliminar (SweetAlert2)
✓ Mensajes de éxito/error
✓ Loading states en botones
✓ Manejo de errores AJAX
✓ Responsive (Bootstrap 5)
✓ Iconos Tabler
✓ CSRF protection
✓ Transacciones DB
✓ Logging de operaciones

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 LISTO PARA USAR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Abre en tu navegador:
http://localhost/[ruta]

Prueba lo siguiente:
1. Ver listado vacío
2. Crear primer registro
3. Editar registro
4. Buscar en DataTable
5. Eliminar registro

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⏱️ TIEMPO TOTAL: ~2 minutos
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### Paso 6: Testing Automático (Opcional)
```
🧪 ¿Quieres que ejecute pruebas automáticas? (sí/no)
```

**Si dice sí:**
```
🧪 EJECUTANDO TESTS...

[1/5] Verificando rutas...
✓ 5 rutas registradas correctamente

[2/5] Probando endpoints...
✓ GET  /[ruta] → 200 OK
✓ POST /[ruta] → 201 Created (con datos válidos)
✓ POST /[ruta] → 422 Unprocessable (sin datos)
✓ GET  /[ruta]/1 → 200 OK
✓ PUT  /[ruta]/1 → 200 OK
✓ DELETE /[ruta]/1 → 200 OK

[3/5] Verificando base de datos...
✓ Tabla existe
✓ Columnas correctas
✓ Índices aplicados
✓ Foreign keys válidas

[4/5] Validando frontend...
✓ Vista index renderiza sin errores
✓ No hay errores JavaScript en consola
✓ DataTable se inicializa correctamente
✓ Modal se abre y cierra

[5/5] Verificando archivos...
✓ Todos los archivos existen
✓ No hay errores de sintaxis
✓ PSR-12 compliant

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ TODOS LOS TESTS PASARON
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### Paso 7: Opciones Post-Generación
```
💡 ¿Qué más quieres agregar?

1. Exportar a Excel/PDF
2. Importar desde Excel
3. Filtros avanzados
4. Permisos por rol
5. Auditoría de cambios
6. Soft deletes
7. Nada más, está perfecto

Opción: _______
(o escribe "ayuda" para ver detalles de cada opción)
```

**Si elige alguna opción:**
```
🔧 Agregando [funcionalidad]...

[Genera código adicional según la opción]

✅ Funcionalidad agregada

Archivos modificados:
- [lista de archivos]

Prueba la nueva funcionalidad en:
http://localhost/[ruta]
```

---

## 🎯 Casos de Uso

### Caso 1: Catálogo Simple
```
/crud categoria

[Opción 1: Estándar Simple]
[Sin relaciones]

→ CRUD listo en 2 minutos
```

### Caso 2: Con Relación
```
/crud producto

[Opción 2: Estándar con Contacto]
[belongsTo: proveedores]

→ CRUD con dropdown de proveedores
```

### Caso 3: Personalizado
```
/crud orden-compra

[Opción 3: Personalizado]
Campos:
  numero:string:required|unique
  fecha:date:required
  total:decimal:required|min:0
  estado:enum:required

[belongsTo: proveedores]

→ CRUD completo con campos custom
```

---

## 📝 Convenciones Automáticas

El workflow `/crud` aplica automáticamente:

**Nombres:**
- Tabla: plural (proveedores)
- Modelo: singular PascalCase (Proveedor)
- Controller: [Modelo]Controller (ProveedorController)
- Request: Store[Modelo]Request / Update[Modelo]Request
- Vista: carpeta singular (proveedor/)

**Validaciones:**
- string: max:255
- unique: en campos "nombre", "codigo", "rfc"
- email: formato válido
- boolean: default true para "activo"
- foreign keys: required|exists:[tabla],id

**UI:**
- Botón "Nuevo": esquina superior derecha
- Tabla: full width, striped, hover
- Modal: tamaño lg, backdrop static
- Iconos: Tabler Icons
- Colores: Bootstrap 5 defaults

**Backend:**
- Transacciones en create/update/delete
- Logging de operaciones críticas
- Manejo de excepciones
- Eager loading para relaciones
- Soft deletes opcional

---

## ⚡ Optimizaciones

**Para tablas grandes (>10,000 registros):**
- Automáticamente usa server-side DataTable
- Agrega índices en columnas de búsqueda
- Implementa cursor pagination en API

**Para relaciones complejas:**
- Eager loading automático
- Previene N+1 queries
- Cache de queries frecuentes

**Para performance:**
- Compila assets con Vite
- Minifica JavaScript
- Lazy load de modales

---

## 🚨 Limitaciones

`/crud` es para casos estándar. NO usar para:

❌ Wizards multi-paso (usa `/coordinate`)
❌ Lógica de negocio compleja (usa `/plan`)
❌ Múltiples tablas relacionadas (usa `/coordinate`)
❌ Integraciones externas (usa `/plan` + `/coordinate`)
❌ Reportes avanzados (usa módulo específico)

Para casos complejos, usa `/plan` o `/coordinate` en su lugar.