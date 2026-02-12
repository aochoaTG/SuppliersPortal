# Contexto del Proyecto: Portal de Proveedores

Este documento proporciona contexto funcional y de negocio para el Portal de Proveedores, complementando la guía técnica (`project-guide.md`).

## 1. Objetivos de Negocio

El objetivo principal de esta plataforma es centralizar y automatizar la gestión de proveedores y el ciclo de adquisiciones internas. Los objetivos clave incluyen:

*   **Agilizar el Proceso de Compras:** Reducir el tiempo desde que se solicita un producto/servicio hasta que se genera la orden de compra.
*   **Mejorar el Cumplimiento Normativo:** Automatizar la validación de proveedores contra listas del SAT (EFOS 69-B) para minimizar riesgos fiscales y legales.
*   **Centralizar la Información:** Crear una única fuente de verdad para la información de proveedores, documentos, y el historial de requisiciones.
*   **Control Presupuestal:** Proveer visibilidad y control sobre el gasto de los distintos departamentos y centros de costo.

## 2. Perfiles de Usuario (User Personas)

Los usuarios de la plataforma se dividen en los siguientes roles principales:

*   **Administrador del Portal (TI/Compras):**
    *   **Responsabilidades:** Gestionar usuarios, configurar catálogos (empresas, centros de costo), supervisar la salud del sistema y gestionar las integraciones.
    *   **Metas:** Mantener la plataforma operativa y asegurar la integridad de los datos.

*   **Analista de Compras:**
    *   **Responsabilidades:** Revisar y procesar requisiciones, interactuar con proveedores, validar documentos y dar seguimiento a las órdenes de compra.
    *   **Metas:** Asegurar que las compras se realicen de manera eficiente y cumpliendo las políticas internas.

*   **Solicitante (Jefe de Departamento/Empleado):**
    *   **Responsabilidades:** Crear requisiciones para las necesidades de su área.
    *   **Metas:** Adquirir los bienes o servicios que necesita su equipo de forma rápida y sencilla.

*   **Aprobador (Director/Finanzas):**
    *   **Responsabilidades:** Revisar y aprobar o rechazar las requisiciones que exceden ciertos montos o que requieren una validación financiera.
    *   **Metas:** Asegurar que el gasto esté alineado con el presupuesto y las prioridades de la compañía.

*   **Proveedor:**
    *   **Responsabilidades:** Mantener su información de contacto actualizada, cargar documentos requeridos (constancia fiscal, opinión de cumplimiento, etc.) y consultar el estado de sus facturas o pagos.
    *   **Metas:** Cumplir con los requisitos de la empresa para poder hacer negocios y tener claridad sobre su estado como proveedor.

## 3. Flujos de Trabajo Principales

### a. Ciclo de Vida de una Requisición
1.  **Creación:** Un *Solicitante* crea una nueva requisición, especificando los productos/servicios, cantidades y justificación.
2.  **Revisión de Compras:** El *Analista de Compras* revisa la requisición para asegurar que la información esté completa y sea correcta.
3.  **Aprobación por Niveles:** La requisición es enviada al *Aprobador* correspondiente según el monto o el departamento.
4.  **Procesamiento:** Una vez aprobada, Compras procede a generar la Orden de Compra correspondiente.
5.  **Rechazo:** Si en cualquier punto es rechazada, la requisición vuelve al solicitante con comentarios para su corrección o cancelación.

### b. Gestión de Proveedores
1.  **Registro:** Un proveedor es invitado o se registra en el portal.
2.  **Carga de Documentos:** El *Proveedor* debe cargar toda la documentación fiscal y legal requerida por la empresa.
3.  **Validación y Alta:** El *Analista de Compras* valida los documentos y la información del proveedor. Se ejecuta la validación contra el servicio del SAT (EFOS).
4.  **Activación:** Si todo es correcto, el proveedor es activado en el sistema y se vuelve elegible para recibir órdenes de compra.
5.  **Mantenimiento:** El proveedor es responsable de mantener su documentación vigente. El sistema podría notificar sobre documentos próximos a expirar.
