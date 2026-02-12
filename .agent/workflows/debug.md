---
description: # Debug - Solución Guiada de Problemas  **Comando**: `/debug`   **Descripción**: Proceso estructurado para diagnosticar y resolver bugs **También**: `/fix`, `/help-me`
---

# Debug - Solución Guiada de Problemas

**Comando**: `/debug`  
**Descripción**: Proceso estructurado para diagnosticar y resolver bugs
**También**: `/fix`, `/help-me`

---

## 🎯 Objetivo

Diagnosticar y resolver problemas de manera sistemática, evitando "prueba y error" desorganizado. Incluye:
- Análisis de logs
- Reproducción del error
- Root cause analysis
- Solución aplicada
- Tests de regresión

---

## 📋 Proceso

### Paso 1: Recopilar Información
```
🐛 DEBUG GUIADO - Paso 1/6

Para ayudarte mejor, necesito entender el problema completo.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. ¿QUÉ INTENTABAS HACER?
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Describe la acción que ejecutaste:
Ejemplo: "Intentaba guardar un nuevo proveedor con RFC 'ABC123456XYZ'"

Tu respuesta:
_____________________________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
2. ¿QUÉ ESPERABAS QUE PASARA?
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Ejemplo: "Que se guardara el registro y mostrara mensaje de éxito"

Tu respuesta:
_____________________________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
3. ¿QUÉ PASÓ EN REALIDAD?
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Ejemplo: "Apareció error 500 y no se guardó nada"

Tu respuesta:
_____________________________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
4. ¿HAY MENSAJE DE ERROR?
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Copia el mensaje COMPLETO (incluyendo stack trace):
Ejemplo:
"SQLSTATE[42S02]: Base table or view not found: 1146 Table 
'database.proveedores' doesn't exist"

Tu respuesta:
_____________________________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
5. ¿CUÁNDO EMPEZÓ?
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Ejemplo: "Después de agregar validación de RFC"

Tu respuesta:
_____________________________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
6. ¿ES CONSISTENTE?
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

¿Pasa siempre o solo a veces?
¿Con todos los datos o solo algunos específicos?

Tu respuesta:
_____________________________________________

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### Paso 2: Clasificación Automática del Error
```
🐛 DEBUG GUIADO - Paso 2/6

🔍 Analizando información...

Tipo de error detectado: [CATEGORÍA]
Severidad: [Crítico/Alto/Medio/Bajo]
```

**Categorías posibles:**
```
1. 🗄️ ERROR DE BASE DE DATOS
   - Tabla no existe
   - Foreign key violation
   - Syntax error en query
   - Connection timeout

2. 🔐 ERROR DE VALIDACIÓN
   - Datos inválidos
   - Required fields missing
   - Unique constraint violation
   - Regex validation failed

3. 🎨 ERROR DE FRONTEND
   - JavaScript error
   - DataTable no inicializa
   - Modal no abre
   - AJAX request failed

4. ⚙️ ERROR DE LÓGICA
   - Cálculo incorrecto
   - Condición mal evaluada
   - Loop infinito
   - Null pointer exception

5. 🔒 ERROR DE PERMISOS
   - 403 Forbidden
   - 401 Unauthorized
   - File permissions

6. 🌐 ERROR DE CONFIGURACIÓN
   - .env mal configurado
   - Missing dependencies
   - Route not found
   - Middleware issue
```

---

### Paso 3: Análisis de Logs
```
🐛 DEBUG GUIADO - Paso 3/6

📊 ANALIZANDO LOGS...

[1] Revisando logs de Laravel...
Archivo: storage/logs/laravel.log
```

**Para cada tipo de error:**

**Error de BD:**
```
🔍 Análisis de logs SQL:

Última query ejecutada:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SELECT * FROM proveedores WHERE rfc = 'ABC123456XYZ'
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Error SQL Server:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SQLSTATE[42S22]: Column not found: 207 Invalid column name 'rfc'
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Causa probable: Migración no ejecutada o columna mal nombrada

¿Ejecutaste la migración? (sí/no)
```

**Error JavaScript:**
```
🔍 Análisis de consola del navegador:

Errores JavaScript detectados:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Uncaught ReferenceError: $ is not defined
    at index.js:15
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Causa probable: jQuery no cargado o cargado después del script

Archivos verificados:
✓ jQuery en layout: <script src="jquery.min.js">
✗ Tu script se carga ANTES de jQuery

Solución: Mover tu script al final del body o usar defer
```

**Error de Validación:**
```
🔍 Análisis de validación:

Request recibido:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
{
  "nombre": "Proveedor Test",
  "rfc": "ABC123",    ← Solo 6 caracteres
  "activo": "true"
}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Regla de validación:
'rfc' => 'required|size:13'

Falla: RFC tiene 6 caracteres, se requieren 13

¿El RFC es correcto o la validación está mal? (RFC/validación)
```

---

### Paso 4: Diagnóstico y Root Cause
```
🐛 DEBUG GUIADO - Paso 4/6

🎯 DIAGNÓSTICO COMPLETO

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CAUSA RAÍZ IDENTIFICADA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Problema: [Descripción técnica]

Ubicación:
Archivo: [ruta/archivo.php]
Línea: [N]
Método: [nombre_metodo()]

Código problemático:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[Muestra snippet del código con error resaltado]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Por qué falla:
[Explicación clara del problema]

Impacto:
- Severidad: [Crítico/Alto/Medio/Bajo]
- Afecta a: [Funcionalidades afectadas]
- Usuarios impactados: [Todos/Admin/Ninguno]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SOLUCIONES POSIBLES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Opción 1: [Solución recomendada] ⭐
  Pasos:
  1. [Paso 1]
  2. [Paso 2]
  3. [Paso 3]
  
  Pros: [Ventajas]
  Contras: [Desventajas]
  Tiempo: [X minutos]

Opción 2: [Solución alternativa]
  Pasos:
  1. [Paso 1]
  2. [Paso 2]
  
  Pros: [Ventajas]
  Contras: [Desventajas]
  Tiempo: [Y minutos]

Opción 3: [Workaround temporal]
  [Descripción]
  
  ⚠️ No resuelve la raíz, solo mitiga

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

¿Qué solución prefieres? (1/2/3/mostrar-código)
```

---

### Paso 5: Aplicar Solución
```
🐛 DEBUG GUIADO - Paso 5/6

🔧 APLICANDO SOLUCIÓN [N]...

[1/4] Backup de archivos afectados...
✓ app/Http/Controllers/ProveedorController.php → .backup

[2/4] Aplicando cambios...

Archivo: app/Http/Controllers/ProveedorController.php
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ANTES (línea 45):
-  $proveedor = Proveedor::where('rfc', $request->rfc)->first();

DESPUÉS (línea 45):
+  $proveedor = Proveedor::where('rfc', strtoupper($request->rfc))->first();
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Explicación del cambio:
SQL Server es case-sensitive para WHERE clauses. Convertimos el 
RFC a mayúsculas antes de buscar para asegurar coincidencia.

[3/4] Validando sintaxis...
✓ No hay errores de sintaxis PHP

[4/4] Limpiando cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear

✅ Solución aplicada correctamente
```

---

### Paso 6: Verificación y Testing
```
🐛 DEBUG GUIADO - Paso 6/6

🧪 VERIFICACIÓN Y TESTS

[1] Test manual...
Por favor, reproduce el error original:

Paso 1: [Acción que causaba el error]
Paso 2: [...]

¿Funciona ahora? (sí/no)
```

**Si dice "sí":**
```
✅ ¡PROBLEMA RESUELTO!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RESUMEN DEL FIX
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Problema: [Descripción breve]
Causa: [Root cause]
Solución: [Fix aplicado]

Archivos modificados:
- [archivo 1] (+ cambios preventivos)
- [archivo 2]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PREVENCIÓN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Para evitar este error en el futuro:

1. [Recomendación 1]
2. [Recomendación 2]
3. [Recomendación 3]

¿Quieres que agregue un test automatizado para evitar regresión? (sí/no)
```

**Si dice "no" (aún falla):**
```
🔍 El problema persiste. Vamos más profundo...

¿Cambió algo respecto al error original?
- Mismo error: [Profundizar en análisis]
- Error diferente: [Analizar nuevo error]
- Comportamiento distinto: [Describir]

Respuesta: _______
```

---

### Paso 7: Test de Regresión (Opcional)
```
🧪 CREANDO TEST AUTOMATIZADO...

php artisan make:test ProveedorRfcTest

✓ tests/Feature/ProveedorRfcTest.php

Test creado:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
/** @test */
public function rfc_debe_ser_case_insensitive()
{
    $proveedor = Proveedor::factory()->create(['rfc' => 'ABC123456XYZ']);
    
    // Debe encontrar con minúsculas
    $found = Proveedor::where('rfc', 'abc123456xyz')->first();
    
    $this->assertNotNull($found);
    $this->assertEquals($proveedor->id, $found->id);
}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Ejecutando test...
php artisan test --filter=ProveedorRfcTest

✅ Test PASSED

Este test evitará que el bug reaparezca en el futuro.
```

---

### Paso 8: Documentación
```
📝 DOCUMENTANDO FIX...

Creando entrada en bug log:
✓ .agent/logs/bugs/[YYYY-MM-DD]-rfc-case-sensitivity.md

Contenido:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Bug: RFC case-sensitivity en búsqueda

**Fecha**: 2026-01-28
**Módulo**: Proveedores
**Severidad**: Media
**Status**: Resuelto

## Descripción
Búsquedas de RFC fallaban cuando el usuario ingresaba
minúsculas, ya que SQL Server es case-sensitive.

## Causa
WHERE clause sin normalización de texto.

## Solución
Convertir RFC a mayúsculas antes de buscar usando 
strtoupper() en PHP.

## Archivos Modificados
- app/Http/Controllers/ProveedorController.php (línea 45)

## Test Agregado
- tests/Feature/ProveedorRfcTest.php

## Prevención
- Siempre normalizar RFC a mayúsculas en validaciones
- Usar mutators en modelo para auto-uppercase
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ Bug documentado para referencia futura
```

---

## 🎯 Casos Especiales

### Error Crítico en Producción
```
🚨 MODO EMERGENCIA ACTIVADO

Error crítico detectado en producción.
Prioridad: Resolver AHORA

Acciones inmediatas:
1. ✅ Rollback automático iniciado
2. ⏳ Analizando logs de producción...
3. 🔍 Comparando con versión estable...

[Análisis acelerado con menos preguntas]

¿Necesitas hotfix inmediato? (sí/no)
```

### Múltiples Errores Relacionados
```
⚠️ Detecté que reportaste varios errores similares.

Posible problema sistemático:
- [Error 1]
- [Error 2]  
- [Error 3]

Todos parecen derivar de: [Root cause común]

¿Quieres que investigue la causa raíz general? (sí/no)
```

### Error Intermitente
```
🔄 ERROR INTERMITENTE DETECTADO

Este tipo de error requiere estrategia diferente:

1. Agregar logging detallado
2. Monitorear por 24-48 horas
3. Analizar patrones (horario, carga, datos específicos)
4. Reproducir en ambiente controlado

¿Procedemos con estrategia de monitoreo? (sí/no)
```

---

## 📝 Notas

- `/debug` es para problemas existentes, no para prevención
- Siempre guarda backups antes de aplicar fixes
- Si no estás seguro, usa opción 3 (workaround) temporalmente
- Un bug bien documentado raramente reaparece
- Los tests de regresión son opcionales pero altamente recomendados