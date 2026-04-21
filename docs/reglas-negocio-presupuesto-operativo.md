# Reglas de Negocio Propuestas para Presupuesto Operativo

## Objetivo
Definir cuándo el sistema debe validar, comprometer, liberar y consumir presupuesto dentro del flujo de compras del portal. Este documento sirve como base funcional antes de implementar la unificación técnica.

## Principios
- La disponibilidad real debe calcularse por `centro de costo + año fiscal + mes de aplicación + categoría de gasto`.
- La fuente de verdad operativa debe ser `BudgetMonthlyDistribution`.
- `assigned_amount` representa el presupuesto autorizado.
- `committed_amount` representa presupuesto apartado para compras aprobadas aún no consumidas.
- `consumed_amount` representa presupuesto ya ejecutado.
- Los centros de costo `FREE_CONSUMPTION` no bloquean operaciones por disponibilidad.

## Momento de Validación
La validación presupuestal debe ocurrir antes de permitir una operación que aparte presupuesto.

### Regla 1: Requisición
- La requisición puede capturarse sin comprometer presupuesto.
- La requisición puede consultar disponibilidad para informar al usuario.
- La requisición no debe mover montos presupuestales.

### Regla 2: RFQ y comparación
- La etapa de RFQ no debe comprometer ni consumir presupuesto.
- La comparación de cotizaciones puede revalidar disponibilidad como control preventivo, pero sin afectar saldos.

### Regla 3: OCD
- La OCD debe validar disponibilidad antes de aprobarse.
- La creación en borrador o pendiente de aprobación no debe comprometer presupuesto.

### Regla 4: OC regular
- La OC regular debe validar disponibilidad inmediatamente antes de generarse o aprobarse de forma definitiva.

## Momento de Compromiso

### Regla 5: Compromiso en OCD
- El presupuesto se compromete cuando la OCD cambia a estado emitido/aprobado para ejecución.
- El compromiso se registra por cada categoría de gasto involucrada.
- El mes de aplicación es el mes operativo definido en la orden.

### Regla 6: Compromiso en OC regular
- El presupuesto se compromete cuando la adjudicación queda aprobada y se genera la OC regular.
- El compromiso se registra por categoría y mes de aplicación.

### Regla 7: No compromiso en etapas previas
- No se compromete presupuesto al crear requisición.
- No se compromete presupuesto al enviar a Compras.
- No se compromete presupuesto al crear o enviar RFQ.
- No se compromete presupuesto al guardar borradores de OCD.

## Momento de Liberación

### Regla 8: Rechazo
- Si una OCD u OC comprometida es rechazada antes de consumo, el compromiso debe liberarse completo.

### Regla 9: Cancelación
- Si una OCD u OC comprometida es cancelada antes de consumo, el compromiso debe liberarse por el saldo pendiente.

### Regla 10: Devolución para corrección
- Si una OCD emitida regresa a revisión y aún no tiene recepciones, el compromiso debe liberarse completo.

### Regla 11: Cierre por inactividad
- Si una orden comprometida se cierra por inactividad y no hubo consumo, el compromiso debe liberarse por el saldo pendiente.

## Momento de Consumo

### Regla 12: Consumo por entrega física
- El consumo debe ocurrir al momento de registrar la remisión o entrega física del proveedor.
- La entrega registrada es el hito operativo que ejerce presupuesto.
- La captura posterior de recepción en estación ya no debe volver a consumir presupuesto.

### Regla 13: Recepción no conforme
- La recepción no conforme no debe impedir el registro del consumo si el bien o servicio fue recibido y aceptado operativamente.
- Si negocio decide que una no conformidad no debe ejercer presupuesto, esta regla deberá cambiar explícitamente antes de implementar.

### Regla 14: Entrega del proveedor
- La entrega registrada por el proveedor (`DELIVERED_PENDING_RECEPTION`) sí debe consumir presupuesto.
- La captura de recepción posterior cumple función logística y de control documental.

### Regla 15: Facturación futura
- Mientras no exista módulo formal de facturas, la remisión o entrega física del proveedor será el hito de consumo.
- Si más adelante Finanzas decide que el consumo real debe ocurrir en factura aprobada, habrá que migrar esta regla de entrega a facturación.

## Recepciones Parciales

### Regla 16: Parcialidad
- Una orden puede pasar por múltiples recepciones parciales.
- Cada recepción parcial consume únicamente lo efectivamente recibido.
- El saldo no recibido permanece comprometido.

### Regla 17: Cierre de saldo
- Cuando la suma de recepciones alcance el total de la orden, el remanente comprometido debe quedar en cero.
- La orden debe reflejar estado final de recepción completa.

## Movimientos Presupuestales

### Regla 18: Transferencia
- `TRANSFERENCIA` mueve presupuesto asignado entre centros, meses o categorías.
- No debe alterar consumo histórico.
- No debe reducir `assigned_amount` por debajo de `consumed_amount + committed_amount`.

### Regla 19: Ampliación
- `AMPLIACION` incrementa `assigned_amount`.

### Regla 20: Reducción
- `REDUCCION` disminuye `assigned_amount`.
- Solo se permite si el saldo asignado restante sigue cubriendo lo ya comprometido y consumido.

## Trazabilidad

### Regla 21: Bitácora operativa
- Cada compromiso, liberación y consumo debe registrar documento origen, usuario, fecha, centro de costo, mes, categoría y monto.
- `BudgetCommitment` puede mantenerse como bitácora operativa si deja de ser usado para cálculo de disponibilidad.

## Reglas de Idempotencia

### Regla 22: Protección contra dobles movimientos
- Aprobar dos veces la misma orden no debe duplicar compromiso.
- Registrar dos veces la misma recepción no debe duplicar consumo.
- Liberar una orden ya liberada no debe alterar montos.

## Propuesta de Decisión para Validación con Negocio
Estas son las decisiones recomendadas para arrancar implementación:

1. Comprometer al aprobar/emitrir la OC u OCD.
2. Liberar al rechazar, cancelar, devolver o cerrar sin recepción.
3. Consumir en la entrega física con remisión registrada por el proveedor.
4. Mantener facturación fuera del cálculo hasta que exista módulo formal.

## Preguntas de Validación con Compras y Finanzas
- ¿El consumo presupuestal debe ocurrir en recepción o hasta factura aprobada?
- ¿Una recepción no conforme ejerce presupuesto o solo una conforme?
- ¿Las OC regulares y OCD comparten exactamente la misma política presupuestal?
- ¿Debe existir una tolerancia para variaciones de precio o cantidad frente a la orden?
- ¿Cómo debe tratarse una cancelación después de una recepción parcial?

## Criterio de Aprobación de esta Fase
La Fase 1 se considera cerrada cuando Compras y Finanzas validen por escrito:

- hito de compromiso,
- hito de liberación,
- hito de consumo,
- tratamiento de parcialidades,
- tratamiento futuro de facturación.
