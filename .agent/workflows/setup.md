---
description: # Setup - Configuración Inicial y Verificación  **Comando**: `/setup`   **Descripción**: Configuración inicial del proyecto, verificación de entorno y personalización de skills **También**: `/config`, `/init`
---

# Setup - Configuración y Verificación

**Comando**: `/setup`  
**Descripción**: Configuración inicial o verificación de proyecto existente

---

## 🎯 Detección Automática
```
🔧 INICIANDO SETUP...

Detectando tipo de proyecto...
```

**Verifica:**
- ¿Existe `.env`?
- ¿Existe `vendor/`?
- ¿Existe `node_modules/`?
- ¿Existe `.agent/skills/`?

### Si es Proyecto EXISTENTE:
```
✅ PROYECTO EXISTENTE DETECTADO

Encontré:
✓ .env configurado
✓ Dependencias instaladas
✓ Base de datos conectada

¿Qué quieres hacer?

1. ✅ Verificar y actualizar (recomendado)
2. 🤖 Solo instalar skills Antigravity
3. 🔄 Actualizar dependencias
4. 🔧 Configuración completa
5. ❌ Cancelar

Opción: _______
```

### Si es Proyecto NUEVO:
```
🆕 PROYECTO NUEVO DETECTADO

Configuración completa necesaria.
¿Comenzamos? (sí/no)
```

---

## 📋 Modo 1: Verificación (Proyecto Existente)
```
🔍 VERIFICACIÓN RÁPIDA

[1/5] Versiones... ✅ PHP 8.2 | Laravel 12 | Node 20
[2/5] Base de datos... ✅ SQL Server conectado
[3/5] Dependencias... ✅ Actualizadas
[4/5] Permisos... ✅ storage/ OK
[5/5] Skills... ❌ No instalados

¿Instalar skills Antigravity? (sí/no)
```

**Si dice "sí":**
```
📥 INSTALANDO SKILLS...

Presets:
1. Completo (todos)
2. Fullstack (sin QA)
3. Backend only
4. Frontend only

Preset: _______

[Instalando...]
✅ Skills instalados en .agent/skills/
✅ Workflows: /coordinate /plan /crud /debug /setup

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚙️ PREFERENCIAS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Idioma: (es/en) _______
Zona horaria: (America/Chihuahua) _______
CLI vendor: (gemini/claude/gpt) _______

✅ Guardado en .agent/config/user-preferences.yaml

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ SETUP COMPLETADO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Tu proyecto existente ahora tiene:
✅ Skills instalados
✅ Workflows disponibles
✅ Preferencias configuradas

Comandos:
/coordinate - Módulo completo
/crud - CRUD rápido
/plan - Planificación
/debug - Resolver problemas
```

---

## 📋 Modo 2: Configuración Completa (Proyecto Nuevo)

### Paso 1: Verificar Software
```
🔧 SETUP - Paso 1/6

VERIFICANDO SOFTWARE...

✅ PHP 8.2.*
✅ Composer 2.6.5
✅ Laravel 12.0.0
✅ Node.js 20.10.0
✅ SQL Server driver
✅ Git 2.43.0

Todo OK ✓
```

**Si algo falla:**
```
❌ [Software] no instalado
Solución: [Instrucciones instalación]
¿Ya lo instalaste? (Enter para verificar)
```

---

### Paso 2: Dependencias
```
🔧 SETUP - Paso 2/6

DEPENDENCIAS PHP
✅ laravel/framework: ^12.0
✅ yajra/laravel-datatables: ^11.0
❌ barryvdh/laravel-debugbar: Faltante

¿Instalar faltantes? (sí/no)
```

---

### Paso 3: Configurar .env

**Si no existe .env:**
```
Creando .env...
✅ .env creado
✅ APP_KEY generado

CONFIGURAR SQL SERVER

Host: _____________ (localhost)
Puerto: _____________ (1433)
Database: _____________ (totalgas_dev)
Usuario: _____________ (sa)
Password: _____________

Probando conexión...
✅ CONEXIÓN EXITOSA
```

**Si existe .env:**
```
✅ .env ya existe
¿Verificar conexión DB? (sí/no)
```

---

### Paso 4: Email (Opcional)
```
🔧 SETUP - Paso 3/6

¿Necesitas enviar emails? (sí/no)
```

**Si sí:**
```
Proveedor: 1.Gmail 2.Outlook 3.Mailtrap 4.Otro

[Configura según proveedor elegido]
✅ Email configurado
```

---

### Paso 5: Frontend
```
🔧 SETUP - Paso 4/6

DEPENDENCIAS FRONTEND
❌ node_modules no instalado

¿Instalar? (sí/no)

[Si sí]
npm install... ✅
npm run build... ✅
```

---

### Paso 6: Skills y Preferencias
```
🔧 SETUP - Paso 5/6

SKILLS ANTIGRAVITY

Presets:
1. Completo - Todo
2. Fullstack - Frontend + Backend
3. Backend Only
4. Frontend Only

Preset: _______

[Instalando...]
✅ Skills instalados
✅ Workflows instalados
✅ Recursos compartidos

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PREFERENCIAS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Idioma: (es/en) _______
Zona horaria: (America/Chihuahua) _______
Formato fecha: 1.DD/MM/YYYY 2.MM/DD/YYYY 3.YYYY-MM-DD
Decimal: 1.Punto(1,234.56) 2.Coma(1.234,56)
CLI vendor: (gemini/claude/gpt) _______

✅ Guardado en .agent/config/user-preferences.yaml
```

---

### Paso 7: Resumen Final
```
🔧 SETUP - Paso 6/6

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ SETUP COMPLETADO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Software: ✅ Todo instalado
Base de datos: ✅ Conectada
Dependencias: ✅ Instaladas
Skills: ✅ Configurados
Assets: ✅ Compilados

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 LISTO PARA USAR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Servidor:
php artisan serve          → http://localhost:8000
npm run dev                → Watch mode

Workflows:
/coordinate                → Módulo completo
/crud [nombre]             → CRUD rápido
/plan                      → Planificación
/debug                     → Resolver problemas

Base de datos:
php artisan migrate        → Ejecutar migraciones
php artisan db:seed        → Datos de prueba

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SIGUIENTE PASO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. Crear primer módulo: /crud proveedores
2. Iniciar servidor
3. Tutorial interactivo
4. Listo

Opción: _______
```

---

## 🔄 Reconfiguración

Si ejecutas `/setup` en proyecto ya configurado:
```
⚙️ YA CONFIGURADO

1. Verificar configuración actual
2. Actualizar dependencias
3. Reinstalar skills
4. Cambiar preferencias
5. Reset completo (⚠️ cuidado)

Opción: _______
```

---

## 🚨 Troubleshooting

### Puerto ocupado
```
❌ Puerto 8000 ocupado
1. Usar otro puerto: --port=8001
2. Matar proceso
3. Buscar libre automáticamente
```

### SQL Server no conecta
```
❌ Error conexión SQL Server

Diagnóstico:
[1] ¿SQL Server corriendo? sqlcmd -S localhost
[2] ¿Firewall OK? netstat -an | findstr 1433
[3] ¿Credenciales correctas? [Reintentar]
```

### Composer lento
```
⚠️ Composer tardando mucho
1. Usar mirror latino
2. Limpiar cache: composer clear-cache
3. Incrementar memory_limit
```

---

## ✅ Checklist Completo

- [x] PHP 8.2+ instalado
- [x] Laravel 12 instalado
- [x] SQL Server conectado
- [x] .env configurado
- [x] Dependencias instaladas
- [x] Assets compilados
- [x] Skills instalados
- [x] Workflows disponibles
- [x] Preferencias guardadas