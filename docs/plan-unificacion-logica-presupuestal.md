# Plan de Unificación de la Lógica Presupuestal

## Objetivo
Unificar la lógica presupuestal del portal para que exista una sola fuente de verdad sobre disponibilidad, compromiso, liberación y consumo de presupuesto, evitando cálculos divergentes entre módulos.

## Problema Actual
Hoy existen tres mecanismos presupuestales superpuestos:

1. `AnnualBudget` + `BudgetMonthlyDistribution`
   - Modelo operativo más consistente con el negocio actual.
   - Ya controla presupuesto por centro de costo, año, mes y categoría.

2. `BudgetCommitment`
   - Registra compromisos por OC/OCD.
   - Su información puede servir para trazabilidad, pero no debe definir la disponibilidad real por sí sola.

3. `BudgetService`
   - Usa otra semántica (`COMMIT`, `RELEASE`, `CONSUME`) y asume campos que no están alineados con el resto del sistema.
   - En su estado actual no es confiable como base del dominio presupuestal.

## Decisión Arquitectónica
La fuente de verdad debe ser:

- `AnnualBudget`
- `BudgetMonthlyDistribution`

Reglas base:

- `assigned_amount`: presupuesto asignado o ajustado.
- `committed_amount`: presupuesto apartado por órdenes aprobadas aún no consumidas.
- `consumed_amount`: presupuesto ya ejercido en el punto de negocio definido.

`BudgetMovement` debe conservarse solo para movimientos estructurales del presupuesto:

- `TRANSFERENCIA`
- `AMPLIACION`
- `REDUCCION`

`BudgetCommitment` debe pasar a ser una tabla de trazabilidad operativa, no de cálculo primario.

## Política Contable a Cerrar Antes de Implementar
Estas reglas deben validarse con negocio antes del refactor:

- Cuándo se compromete presupuesto: al aprobar OCD/OC.
- Cuándo se libera: rechazo, cancelación o devolución.
- Cuándo se consume: recepción parcial/total o carga/aprobación de factura.
- Cómo tratar recepciones parciales.
- Si OC regular y OCD deben comportarse exactamente igual.

## Diseño Propuesto
Crear un único servicio de dominio, por ejemplo `BudgetAllocationService`, responsable de:

- `checkAvailability()`
- `commit()`
- `release()`
- `consume()`
- `transfer()`
- `increase()`
- `decrease()`

Ese servicio debe actualizar únicamente `BudgetMonthlyDistribution` para disponibilidad real.

## Estrategia por Fases

### Fase 1: Alineación funcional
- Confirmar reglas contables y eventos que afectan presupuesto.
- Definir el momento exacto de compromiso, liberación y consumo.
- Documentar ejemplos reales de OCD, OC regular y recepción parcial.

### Fase 2: Introducción del servicio unificado
- Crear `BudgetAllocationService`.
- Implementar operaciones atómicas con transacciones.
- Agregar validaciones de idempotencia para evitar doble compromiso o doble consumo.

### Fase 3: Migración de consumidores
Migrar primero los puntos con mayor impacto operativo:

1. `DirectPurchaseOrderController`
2. `BudgetMovementController`
3. Endpoints de disponibilidad:
   - `AnnualBudgetController`
   - `ExpenseCategoryController`
4. Flujos restantes de requisiciones, OC regular y recepciones

### Fase 4: Congelar legado
- Marcar `BudgetService` como obsoleto.
- Eliminar nuevas dependencias hacia ese servicio.
- Dejar `BudgetCommitment` solo como bitácora operativa o preparar su retiro.

### Fase 5: Limpieza final
- Eliminar lógica duplicada de disponibilidad.
- Consolidar cálculos en un solo servicio.
- Depurar código muerto y rutas no usadas.

## Impacto por Archivo
Archivos candidatos a refactor prioritario:

- `app/Services/BudgetService.php`
- `app/Models/BudgetCommitment.php`
- `app/Models/BudgetMonthlyDistribution.php`
- `app/Http/Controllers/DirectPurchaseOrderController.php`
- `app/Http/Controllers/BudgetMovementController.php`
- `app/Http/Controllers/AnnualBudgetController.php`
- `app/Http/Controllers/ExpenseCategoryController.php`
- `app/Console/Commands/CloseInactivePurchaseOrders.php`

## Riesgos
- Comprometer presupuesto en un momento incorrecto del flujo.
- Inconsistencias entre OCD y OC regular.
- Descuadres en recepciones parciales.
- Dashboards o validaciones que dependan de lógica legacy.

## Pruebas Obligatorias
- Aprobar OCD con presupuesto suficiente.
- Rechazar OCD y liberar compromiso.
- Devolver OCD emitida y liberar compromiso.
- Recepción parcial y recepción total.
- Transferencia presupuestal con monto comprometido existente.
- Centro de costo `FREE_CONSUMPTION`.
- Categoría sin distribución mensual.
- Idempotencia: evitar doble compromiso o doble consumo.

## Orden Recomendado de Implementación
1. Acordar reglas de negocio con finanzas/compras.
2. Crear el servicio unificado.
3. Migrar OCD.
4. Migrar consultas de disponibilidad.
5. Migrar OC regular y recepción.
6. Retirar legado.

## Criterio de Éxito
La unificación estará completa cuando:

- Toda validación de disponibilidad use una sola API de dominio.
- Todo compromiso/liberación/consumo actualice la misma capa de datos.
- No existan cálculos presupuestales operativos dispersos en controladores.
- `BudgetService` deje de ser necesario.
