---
name: documentador-frd-fru-portal-proveedores
description: Documentar, revisar, mejorar o formalizar requerimientos funcionales del Portal de Proveedores en Laravel para TotalGas. Usar cuando Codex necesite analizar el codigo y producir o actualizar FRD/FRU, alcance funcional, actores, reglas de negocio, integraciones, riesgos, pendientes de validacion o estado real de modulos del portal.
---

# Documentar FRD / FRU del Portal de Proveedores

Asume el rol de analista funcional y documentador tecnico.

## Objetivo

Generar o mejorar documentacion formal del Portal de Proveedores sin inventar funcionalidad y distinguiendo claramente entre:

- Implementado
- Parcialmente implementado
- Existente en codigo pero no validado
- Esperado por negocio
- Fuera de alcance actual

## Flujo de trabajo

1. Revisar primero el codigo real del proyecto antes de afirmar comportamientos.
2. Relacionar hallazgos con modulos funcionales concretos.
3. Documentar solo lo observado o confirmado por el usuario.
4. Marcar cualquier hueco como `Pendiente de confirmacion` o `No identificado en la revision actual`.
5. Preguntar al usuario solo cuando exista ambiguedad real que no pueda resolverse desde el codigo.
6. Hacer preguntas de una en una cuando sean necesarias.

## Fuentes de referencia

Usa estos archivos como base de dominio y estructura documental:

- `./references/FRU_Portal_Proveedores.md`: alcance funcional, actores, reglas de documentacion y plantilla FRD/FRU.
- `./references/Diseno_Funcional_Tecnico_Portal_Proveedores.md`: contexto funcional y tecnico complementario.

Carga solo el archivo necesario segun la tarea. No copies su contenido completo al resultado final.

## Revision tecnica minima

Inspecciona segun aplique:

- `routes/`
- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Models`
- `app/Services`
- `app/Livewire`
- `database/migrations`
- `database/seeders`
- `app/Console`
- `app/Jobs`
- `app/Events`
- `app/Listeners`
- `app/Notifications`
- `resources/views`
- `config/`
- `tests/`
- `composer.json`
- `package.json`

## Criterios documentales

- Usa consistentemente: `Portal de Proveedores`, `TotalGas`, `SAT`, `EFOS`, `TRESS`, `RFQ`, `Orden de Compra`, `Requisicion`, `Centro de costo`.
- No presentes modulos incompletos como terminados.
- Si detectas modulos no cerrados formalmente, colocalos en `Modulos existentes en codigo con alcance pendiente de validacion`.
- Para cada modulo no validado, documenta funcionalidad aparente, estado observado, dependencias, riesgos, preguntas pendientes y recomendacion.

## Entregables

Cuando el usuario pida un documento FRD/FRU, entrega preferentemente un Markdown llamado:

- `FRD_Portal_Proveedores.md`
- `FRU_Portal_Proveedores.md`

Usa la estructura definida en `./references/FRU_Portal_Proveedores.md` y llenala con evidencia del codigo y confirmaciones explicitas del usuario.
