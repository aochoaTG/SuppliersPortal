# Sidebar Redesign — Design Spec
**Date:** 2026-04-20
**Status:** Approved

## Context

The sidebar is shared by two distinct user types: TotalGas staff and suppliers. Currently it lives in a single file (`sidebar.blade.php`) with role conditions mixed throughout. As the system grows — more staff roles, more supplier features — this becomes hard to maintain and exposes irrelevant menu items to users who don't need them.

## Goals

- Separate staff and supplier sidebars into independent files
- Show each staff role only the sections relevant to their function
- Keep the supplier sidebar clean and extensible for upcoming features (deliveries, invoicing)
- Avoid touching the layout — the entry point stays as `sidebar.blade.php`

---

## File Structure

```
resources/views/layouts/partials/
    sidebar.blade.php           ← dispatcher only (5 lines)
    sidebar-staff.blade.php     ← all staff roles
    sidebar-supplier.blade.php  ← supplier role
```

`sidebar.blade.php` becomes a simple router:

```blade
@hasrole('supplier')
    @include('layouts.partials.sidebar-supplier')
@else
    @include('layouts.partials.sidebar-staff')
@endhasrole
```

---

## Staff Sidebar (`sidebar-staff.blade.php`)

Sections and role visibility:

### INICIO
| Item | Roles |
|------|-------|
| Dashboard | todos |

### COMPRAS
| Item | Roles |
|------|-------|
| Requisiciones | superadmin, staff, requester, general_director |
| Cotizaciones → Cotizar | superadmin, staff |
| Cotizaciones → Aprobar cotización | superadmin, staff, authorizer, general_director |
| Cotizaciones → Listado RFQs | superadmin, staff, general_director |
| Cotizaciones → Pendientes de Respuesta | superadmin, staff |
| Órdenes de Compra | superadmin, staff, requester, general_director |
| Recepciones | superadmin, staff |
| Productos/Servicios | superadmin, staff, catalog_admin |

### FINANZAS
| Item | Roles |
|------|-------|
| Control Presupuestal | superadmin, accounting, general_director |
| Pagos y Facturación | superadmin, accounting, general_director |

### PROVEEDORES
| Item | Roles |
|------|-------|
| Gestión de Proveedores (Lista, Usuarios, EFOS, SIROC) | superadmin, staff |
| Rev. de documentos | superadmin, staff |
| Comunicados | superadmin, staff |

### CONFIGURACIÓN
| Item | Roles |
|------|-------|
| Usuarios Staff | superadmin |
| Empleados | superadmin |
| Catálogos del sistema | superadmin |
| Incidentes Reportados | superadmin |

---

## Supplier Sidebar (`sidebar-supplier.blade.php`)

Onboarding gate: if `$user->mustFinishSupplierOnboarding()` is true, only **Documentación** is shown with a danger badge.

Full sidebar (post-onboarding):

```
PORTAL DE PROVEEDORES
  Dashboard
  Cotizaciones (RFQ)
    Mis RFQs
    Historial
  Documentación
  Comunicados
  Mis Entregas
    [future: entrega parcial/completa sin receptor TotalGas]
  Facturación                    ← pendiente de implementar
    Cargar Factura (PDF + XML)
    Historial de Facturas
```

Facturación section is added as a placeholder now (hidden until the feature exists) or added when the feature is implemented — whichever the developer prefers.

---

## Role Summary

| Rol | Secciones visibles |
|-----|-------------------|
| superadmin | Todo |
| staff | Dashboard, Compras (todo), Proveedores |
| accounting | Dashboard, Finanzas |
| general_director | Dashboard, Compras, Finanzas |
| authorizer | Dashboard, Compras → solo Aprobar cotización |
| catalog_admin | Dashboard, Compras → solo Productos/Servicios |
| requester | Dashboard, Compras → Requisiciones + Órdenes de Compra |
| supplier | Sidebar propio (sidebar-supplier.blade.php) |

---

## Out of Scope

- Changing routes, controllers, or permissions
- Building the Facturación feature
- Changing the layout file that includes the sidebar
