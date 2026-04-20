# Sidebar Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Split the monolithic `sidebar.blade.php` into three files: a dispatcher and two role-aware partials — one for staff, one for suppliers.

**Architecture:** `sidebar.blade.php` becomes a 5-line dispatcher that includes either `sidebar-staff.blade.php` or `sidebar-supplier.blade.php` based on the authenticated user's role. The staff partial uses `@hasanyrole` directives per section so each role sees only what they need. The supplier partial has an onboarding gate and a commented-out placeholder for the upcoming Facturación feature.

**Tech Stack:** Laravel Blade, Spatie Laravel Permission (`@hasrole`, `@hasanyrole`), Bootstrap 5 collapse for accordions.

**Spec:** `docs/superpowers/specs/2026-04-20-sidebar-redesign-design.md`

---

## Files

| Action | Path | Purpose |
|--------|------|---------|
| Create | `resources/views/layouts/partials/sidebar-staff.blade.php` | All staff menu items with per-role visibility |
| Create | `resources/views/layouts/partials/sidebar-supplier.blade.php` | Supplier portal menu |
| Modify | `resources/views/layouts/partials/sidebar.blade.php` | Becomes a dispatcher only |

---

## Task 1: Create `sidebar-supplier.blade.php`

**Files:**
- Create: `resources/views/layouts/partials/sidebar-supplier.blade.php`

- [ ] **Step 1: Create the file with the following content**

```blade
{{--
    sidebar-supplier.blade.php
    ==========================
    Sidebar for supplier (proveedor) users only.

    ROLES HANDLED:
      - supplier : all supplier accounts

    ONBOARDING GATE:
      If $user->mustFinishSupplierOnboarding() returns true, only "Documentación"
      is shown with a danger badge, forcing the supplier to complete their profile
      before accessing any other feature.

    HOW TO ADD A NEW SECTION:
      1. Add the <li> block inside the post-onboarding @else block below.
      2. If the feature is not yet implemented, add it as a commented-out block
         with a note: {{-- PENDING: [feature description] --}}
      3. Update this header comment to document the new section.

    SECTIONS:
      - Dashboard
      - Cotizaciones (RFQ) → Mis RFQs, Historial
      - Documentación
      - Comunicados
      - Mis Entregas
      - Facturación [PENDING — not yet implemented]
--}}

@php $u = auth()->user(); @endphp

{{-- ═══════════════════════════════════════════════════
     PORTAL DE PROVEEDORES — visible to: supplier
     ═══════════════════════════════════════════════════ --}}
<li class="side-nav-title">PORTAL DE PROVEEDORES</li>

@if ($u->mustFinishSupplierOnboarding())
    {{-- Onboarding incomplete: only Documentación is shown with an alert badge --}}
    <li class="side-nav-item">
        <a href="{{ route('documents.suppliers.index') }}"
            class="side-nav-link {{ request()->routeIs('documents.suppliers.index') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-checklist"></i></span>
            <span class="menu-text">Documentación</span>
            <span class="badge bg-danger rounded-pill">!</span>
        </a>
    </li>

@else
    {{-- Dashboard --}}
    <li class="side-nav-item">
        <a href="{{ route('supplier.dashboard') }}"
            class="side-nav-link {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-home"></i></span>
            <span class="menu-text">Dashboard</span>
        </a>
    </li>

    {{-- Cotizaciones (RFQ) accordion --}}
    @php $openSupplierRfq = request()->routeIs('supplier.rfq.*') || request()->routeIs('supplier.quotations.*'); @endphp
    <li class="side-nav-item">
        <a class="side-nav-link {{ $openSupplierRfq ? '' : 'collapsed' }}"
            data-bs-toggle="collapse"
            href="#sidebarSupplierRfq"
            role="button"
            aria-expanded="{{ $openSupplierRfq ? 'true' : 'false' }}"
            aria-controls="sidebarSupplierRfq">
            <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
            <span class="menu-text">Cotizaciones</span>
            <span class="menu-arrow"></span>
        </a>
        <div class="{{ $openSupplierRfq ? 'show' : '' }} collapse" id="sidebarSupplierRfq">
            <ul class="sub-menu">
                {{-- Mis RFQs --}}
                <li class="side-nav-item">
                    <a href="{{ route('supplier.dashboard') }}"
                        class="side-nav-link {{ request()->routeIs('supplier.dashboard') || request()->routeIs('supplier.rfq.show') ? 'active' : '' }}">
                        <span class="menu-text">Mis RFQs</span>
                    </a>
                </li>
                {{-- Historial --}}
                <li class="side-nav-item">
                    <a href="{{ route('supplier.quotations.history') }}"
                        class="side-nav-link {{ request()->routeIs('supplier.quotations.history') ? 'active' : '' }}">
                        <span class="menu-text">Historial</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>

    {{-- Documentación --}}
    <li class="side-nav-item">
        <a href="{{ route('documents.suppliers.index') }}"
            class="side-nav-link {{ request()->routeIs('documents.suppliers.index') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-checklist"></i></span>
            <span class="menu-text">Documentación</span>
        </a>
    </li>

    {{-- Comunicados --}}
    <li class="side-nav-item">
        <a href="{{ route('supplier.announcements.inbox') }}"
            class="side-nav-link {{ request()->routeIs('supplier.announcements.inbox') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
            <span class="menu-text">Comunicados</span>
        </a>
    </li>

    {{-- Mis Entregas --}}
    <li class="side-nav-item">
        <a href="{{ route('supplier.deliveries.index') }}"
            class="side-nav-link {{ request()->routeIs('supplier.deliveries.*') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-truck-delivery"></i></span>
            <span class="menu-text">Mis Entregas</span>
        </a>
    </li>

    {{-- PENDING: Facturación (PDF + XML upload)
         Uncomment and implement routes when the invoice upload feature is built.
         Required routes: supplier.invoices.create, supplier.invoices.index
    --}}
    {{--
    @php $openFacturacion = request()->routeIs('supplier.invoices.*'); @endphp
    <li class="side-nav-item">
        <a class="side-nav-link {{ $openFacturacion ? '' : 'collapsed' }}"
            data-bs-toggle="collapse"
            href="#sidebarSupplierFacturacion"
            role="button"
            aria-expanded="{{ $openFacturacion ? 'true' : 'false' }}"
            aria-controls="sidebarSupplierFacturacion">
            <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
            <span class="menu-text">Facturación</span>
            <span class="menu-arrow"></span>
        </a>
        <div class="{{ $openFacturacion ? 'show' : '' }} collapse" id="sidebarSupplierFacturacion">
            <ul class="sub-menu">
                <li class="side-nav-item">
                    <a href="{{ route('supplier.invoices.create') }}" class="side-nav-link">
                        <span class="menu-text">Cargar Factura</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('supplier.invoices.index') }}" class="side-nav-link">
                        <span class="menu-text">Historial de Facturas</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    --}}

@endif
```

- [ ] **Step 2: Verify the file was created**

```bash
php artisan view:clear
```

Expected: no errors.

---

## Task 2: Create `sidebar-staff.blade.php`

**Files:**
- Create: `resources/views/layouts/partials/sidebar-staff.blade.php`

- [ ] **Step 1: Create the file with the following content**

```blade
{{--
    sidebar-staff.blade.php
    =======================
    Sidebar for all TotalGas internal staff roles.

    ROLES HANDLED:
      - superadmin     : full access to all sections
      - staff          : Compras + Proveedores
      - accounting     : Finanzas only
      - general_director : Compras + Finanzas
      - authorizer     : Compras → Aprobar cotización only
      - catalog_admin  : Compras → Productos/Servicios only
      - requester      : Compras → Requisiciones + Órdenes de Compra

    HOW TO ADD A NEW SECTION:
      1. Add a new section block wrapped with @hasanyrole('role1|role2|...')
      2. Add a comment above the block listing the allowed roles:
         {{-- SECTION NAME — visible to: role1, role2 --}}
      3. Update the ROLES HANDLED list above if a new role is introduced.

    HOW TO ADD AN ITEM INSIDE AN EXISTING SECTION:
      1. Add the <li> block in the correct section.
      2. If it has different role restrictions than the parent section,
         wrap it with its own @hasanyrole and add an inline comment.

    SECTIONS:
      - INICIO          → all roles
      - COMPRAS         → superadmin, staff, requester, general_director, authorizer, catalog_admin
      - FINANZAS        → superadmin, accounting, general_director
      - PROVEEDORES     → superadmin, staff
      - CONFIGURACIÓN   → superadmin only
--}}

{{-- ═══════════════════════════════════════════════════
     INICIO — visible to: all staff roles
     ═══════════════════════════════════════════════════ --}}
<li class="side-nav-title">INICIO</li>
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
        href="{{ route('dashboard') }}">
        <span class="menu-icon"><i class="ti ti-home"></i></span>
        <span class="menu-text">Dashboard</span>
    </a>
</li>

{{-- ═══════════════════════════════════════════════════
     COMPRAS — visible to: superadmin, staff, requester, general_director, authorizer, catalog_admin
     ═══════════════════════════════════════════════════ --}}
@hasanyrole('superadmin|staff|requester|general_director|authorizer|catalog_admin')
<li class="side-nav-title">COMPRAS</li>

{{-- Requisiciones — visible to: superadmin, staff, requester, general_director --}}
@hasanyrole('superadmin|staff|requester|general_director')
<li class="side-nav-item">
    <a href="{{ route('requisitions.index') }}"
        class="side-nav-link {{ request()->routeIs('requisitions.*') || request()->routeIs('requisition-items.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-clipboard-list"></i></span>
        <span class="menu-text">Requisiciones</span>
    </a>
</li>
@endhasanyrole

{{-- Cotizaciones (RFQ) accordion — visible to: superadmin, staff, authorizer, general_director --}}
@hasanyrole('superadmin|staff|authorizer|general_director')
@php $openRfq = request()->routeIs('rfq.*') || request()->routeIs('quotes.*') || request()->routeIs('approvals.quotations.*'); @endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openRfq ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarComprasRfq" role="button"
        aria-expanded="{{ $openRfq ? 'true' : 'false' }}" aria-controls="sidebarComprasRfq">
        <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
        <span class="menu-text">Cotizaciones (RFQ)</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openRfq ? 'show' : '' }} collapse" id="sidebarComprasRfq">
        <ul class="sub-menu">
            {{-- Cotizar — visible to: superadmin, staff --}}
            @hasanyrole('superadmin|staff')
            @php
                try {
                    $activeRequisitionsCount = \App\Models\Requisition::whereNotIn('status', ['DRAFT', 'CANCELLED', 'COMPLETED'])->count();
                } catch (\Exception $e) {
                    $activeRequisitionsCount = 0;
                }
            @endphp
            <li class="side-nav-item">
                <a href="{{ route('quotes.index') }}"
                    class="side-nav-link {{ request()->routeIs('quotes.index') ? 'active' : '' }}">
                    <span class="menu-text">Cotizar</span>
                    @if($activeRequisitionsCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $activeRequisitionsCount }}</span>
                    @endif
                </a>
            </li>
            @endhasanyrole

            {{-- Aprobar cotización — visible to: superadmin, staff, authorizer, general_director --}}
            @php $pendingApprovalsCount = \App\Models\QuotationSummary::where('approval_status', 'pending')->count(); @endphp
            <li class="side-nav-item">
                <a href="{{ route('approvals.quotations.index') }}"
                    class="side-nav-link {{ request()->routeIs('approvals.quotations.*') ? 'active' : '' }}">
                    <span class="menu-text">Aprobar cotización</span>
                    @if($pendingApprovalsCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingApprovalsCount }}</span>
                    @endif
                </a>
            </li>

            {{-- Listado de RFQs — visible to: superadmin, staff, general_director --}}
            @hasanyrole('superadmin|staff|general_director')
            <li class="side-nav-item">
                <a href="{{ route('rfq.index') }}"
                    class="side-nav-link {{ request()->routeIs('rfq.index') ? 'active' : '' }}">
                    <span class="menu-text">Listado de RFQs</span>
                </a>
            </li>
            @endhasanyrole

            {{-- Pendientes de Respuesta — visible to: superadmin, staff --}}
            @hasanyrole('superadmin|staff')
            <li class="side-nav-item">
                <a href="{{ route('rfq.inbox.pending') }}"
                    class="side-nav-link {{ request()->routeIs('rfq.inbox.pending') ? 'active' : '' }}">
                    <span class="menu-text">Pendientes de Respuesta</span>
                </a>
            </li>
            @endhasanyrole
        </ul>
    </div>
</li>
@endhasanyrole {{-- end Cotizaciones accordion --}}

{{-- Órdenes de Compra — visible to: superadmin, staff, requester, general_director --}}
@hasanyrole('superadmin|staff|requester|general_director')
<li class="side-nav-item">
    <a href="{{ route('purchase-orders.index') }}"
        class="side-nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
        <span class="menu-text">Órdenes de Compra</span>
        @php
            try {
                $poCount = \App\Models\PurchaseOrder::where('status', 'OPEN')->count();
            } catch (\Exception $e) {
                $poCount = 0;
            }
        @endphp
        @if($poCount > 0)
            <span class="badge bg-info rounded-pill ms-auto">{{ $poCount }}</span>
        @endif
    </a>
</li>
@endhasanyrole

{{-- Recepciones — visible to: superadmin, staff --}}
@hasanyrole('superadmin|staff')
<li class="side-nav-item">
    <a href="{{ route('receptions.overview') }}"
        class="side-nav-link {{ request()->routeIs('receptions.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-truck-delivery"></i></span>
        <span class="menu-text">Recepciones</span>
    </a>
</li>
@endhasanyrole

{{-- Productos/Servicios — visible to: superadmin, staff, catalog_admin --}}
@hasanyrole('superadmin|staff|catalog_admin')
<li class="side-nav-item">
    <a href="{{ route('products-services.index') }}"
        class="side-nav-link {{ request()->routeIs('products-services.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-package"></i></span>
        <span class="menu-text">Productos/Servicios</span>
    </a>
</li>
@endhasanyrole

@endhasanyrole {{-- end COMPRAS --}}

{{-- ═══════════════════════════════════════════════════
     FINANZAS — visible to: superadmin, accounting, general_director
     ═══════════════════════════════════════════════════ --}}
@hasanyrole('superadmin|accounting|general_director')
<li class="side-nav-title">FINANZAS</li>

{{-- Control Presupuestal accordion — visible to: superadmin, accounting, general_director --}}
@php
$openPresupuesto =
    request()->routeIs('annual_budgets.*') ||
    request()->routeIs('budget_monthly_distributions.*') ||
    request()->routeIs('budget-movements.*') ||
    request()->routeIs('cost-centers.*') ||
    request()->routeIs('categories.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openPresupuesto ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarPresupuesto" role="button"
        aria-expanded="{{ $openPresupuesto ? 'true' : 'false' }}" aria-controls="sidebarPresupuesto">
        <span class="menu-icon"><i class="ti ti-chart-bar"></i></span>
        <span class="menu-text">Control Presupuestal</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openPresupuesto ? 'show' : '' }} collapse" id="sidebarPresupuesto">
        <ul class="sub-menu">
            <li class="side-nav-item">
                <a href="{{ route('cost-centers.index') }}"
                    class="side-nav-link {{ request()->routeIs('cost-centers.*') ? 'active' : '' }}">
                    <span class="menu-text">Centros de Costo</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('annual_budgets.index') }}"
                    class="side-nav-link {{ request()->routeIs('annual_budgets.*') ? 'active' : '' }}">
                    <span class="menu-text">Presupuestos Anuales</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('budget_monthly_distributions.index') }}"
                    class="side-nav-link {{ request()->routeIs('budget_monthly_distributions.*') ? 'active' : '' }}">
                    <span class="menu-text">Distribuciones Mensuales</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('budget_movements.index') }}"
                    class="side-nav-link {{ request()->routeIs('budget_movements.*') ? 'active' : '' }}">
                    <span class="menu-text">Movimiento Presupuestal</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('categories.index') }}"
                    class="side-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <span class="menu-text">Categorías</span>
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- Pagos y Facturación accordion — visible to: superadmin, accounting, general_director --}}
@php
$openPagos =
    request()->routeIs('invoices.*') ||
    request()->routeIs('payments.*') ||
    request()->routeIs('finance-reports.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openPagos ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarPagos" role="button" aria-expanded="{{ $openPagos ? 'true' : 'false' }}"
        aria-controls="sidebarPagos">
        <span class="menu-icon"><i class="ti ti-cash"></i></span>
        <span class="menu-text">Pagos y Facturación</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openPagos ? 'show' : '' }} collapse" id="sidebarPagos">
        <ul class="sub-menu">
            <li class="side-nav-item">
                <a href="javascript: void(0);"
                    class="side-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <span class="menu-text">Facturas</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="javascript: void(0);"
                    class="side-nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <span class="menu-text">Pagos</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="javascript: void(0);"
                    class="side-nav-link {{ request()->routeIs('finance-reports.*') ? 'active' : '' }}">
                    <span class="menu-text">Reportes Financieros</span>
                </a>
            </li>
        </ul>
    </div>
</li>
@endhasanyrole {{-- end FINANZAS --}}

{{-- ═══════════════════════════════════════════════════
     PROVEEDORES — visible to: superadmin, staff
     ═══════════════════════════════════════════════════ --}}
@hasanyrole('superadmin|staff')
<li class="side-nav-title">PROVEEDORES</li>

{{-- Gestión de Proveedores accordion — visible to: superadmin, staff --}}
@php
$openGestionProv = request()->routeIs('cat-suppliers.*')
    || request()->routeIs('users.suppliers.*')
    || request()->routeIs('sat_efos_69b.*')
    || request()->routeIs('sirocs.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openGestionProv ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarGestionProveedores" role="button"
        aria-expanded="{{ $openGestionProv ? 'true' : 'false' }}"
        aria-controls="sidebarGestionProveedores">
        <span class="menu-icon"><i class="ti ti-building-store"></i></span>
        <span class="menu-text">Gestión de Proveedores</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openGestionProv ? 'show' : '' }} collapse" id="sidebarGestionProveedores">
        <ul class="sub-menu">
            {{-- Lista de Proveedores --}}
            <li class="side-nav-item">
                <a href="{{ route('cat-suppliers.index') }}"
                    class="side-nav-link {{ request()->routeIs('cat-suppliers.*') ? 'active' : '' }}">
                    <span class="menu-text">Lista de Proveedores</span>
                </a>
            </li>
            {{-- Usuarios Proveedor --}}
            <li class="side-nav-item">
                <a href="{{ route('users.suppliers.index') }}"
                    class="side-nav-link {{ request()->routeIs('users.suppliers.*') ? 'active' : '' }}">
                    <span class="menu-text">Usuarios Proveedor</span>
                </a>
            </li>
            {{-- EFOS (SAT) — lista negra del SAT art. 69-B --}}
            <li class="side-nav-item">
                <a href="{{ route('sat_efos_69b.index') }}"
                    class="side-nav-link {{ request()->routeIs('sat_efos_69b.*') ? 'active' : '' }}">
                    <span class="menu-text">EFOS (SAT)</span>
                </a>
            </li>
            {{-- SIROC (IMSS) — Sistema de Información para la Relación con Contratistas --}}
            <li class="side-nav-item">
                <a href="{{ route('sirocs.index') }}"
                    class="side-nav-link {{ request()->routeIs('sirocs.*') ? 'active' : '' }}">
                    <span class="menu-text">SIROC (IMSS)</span>
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- Rev. de documentos — visible to: superadmin, staff --}}
<li class="side-nav-item">
    <a href="{{ route('admin.review.index') }}"
        class="side-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-checklist"></i></span>
        <span class="menu-text">Rev. de documentos</span>
        @if (!empty($pendingReviewCount) && $pendingReviewCount > 0)
            <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingReviewCount }}</span>
        @endif
    </a>
</li>

{{-- Comunicados — visible to: superadmin, staff --}}
<li class="side-nav-item">
    <a href="{{ route('admin.announcements.index') }}"
        class="side-nav-link {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
        <span class="menu-text">Comunicados</span>
    </a>
</li>
@endhasanyrole {{-- end PROVEEDORES --}}

{{-- ═══════════════════════════════════════════════════
     CONFIGURACIÓN — visible to: superadmin only
     ═══════════════════════════════════════════════════ --}}
@hasrole('superadmin')
<li class="side-nav-title">CONFIGURACIÓN</li>

{{-- Usuarios Staff — visible to: superadmin --}}
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('users.staff.index') ? 'active' : '' }}"
        href="{{ route('users.staff.index') }}">
        <span class="menu-icon"><i class="ti ti-users"></i></span>
        <span class="menu-text">Usuarios Staff</span>
    </a>
</li>

{{-- Empleados — visible to: superadmin --}}
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}"
        href="{{ route('employees.index') }}">
        <span class="menu-icon"><i class="ti ti-id-badge"></i></span>
        <span class="menu-text">Empleados</span>
    </a>
</li>

{{-- Catálogos del sistema accordion — visible to: superadmin --}}
@php
$openConfiguration =
    request()->routeIs('companies.*') ||
    request()->routeIs('stations.*') ||
    request()->routeIs('departments.*') ||
    request()->routeIs('taxes.*') ||
    request()->routeIs('approval-levels.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openConfiguration ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarConfigurations" role="button"
        aria-expanded="{{ $openConfiguration ? 'true' : 'false' }}"
        aria-controls="sidebarConfigurations">
        <span class="menu-icon"><i class="ti ti-settings"></i></span>
        <span class="menu-text">Catálogos</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openConfiguration ? 'show' : '' }} collapse" id="sidebarConfigurations">
        <ul class="sub-menu">
            <li class="side-nav-item">
                <a href="{{ route('companies.index') }}"
                    class="side-nav-link {{ request()->routeIs('companies.index') ? 'active' : '' }}">
                    <span class="menu-text">Empresas</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('stations.index') }}"
                    class="side-nav-link {{ request()->routeIs('stations.index') ? 'active' : '' }}">
                    <span class="menu-text">Estaciones</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('departments.index') }}"
                    class="side-nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                    <span class="menu-text">Departamentos</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('receiving-locations.index') }}"
                    class="side-nav-link {{ request()->routeIs('receiving-locations.*') ? 'active' : '' }}">
                    <span class="menu-text">Ubicaciones de Recepción</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('taxes.index') }}"
                    class="side-nav-link {{ request()->routeIs('taxes.index') ? 'active' : '' }}">
                    <span class="menu-text">IVA</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('approval-levels.index') }}"
                    class="side-nav-link {{ request()->routeIs('approval-levels.*') ? 'active' : '' }}">
                    <span class="menu-text">Niveles de Autorización</span>
                    <span class="badge bg-soft-danger text-danger ms-auto">🛡️</span>
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- Incidentes Reportados — visible to: superadmin --}}
<li class="side-nav-item text-danger">
    <a href="{{ route('incidents.index') }}"
        class="side-nav-link text-danger {{ request()->routeIs('incidents.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-bug"></i></span>
        <span class="menu-text">Incidentes Reportados</span>
    </a>
</li>
@endhasrole {{-- end CONFIGURACIÓN --}}
```

- [ ] **Step 2: Clear view cache**

```bash
php artisan view:clear
```

Expected: `INFO  Blade template cache cleared successfully.`

---

## Task 3: Convert `sidebar.blade.php` to dispatcher

**Files:**
- Modify: `resources/views/layouts/partials/sidebar.blade.php`

- [ ] **Step 1: Replace the entire content of `sidebar.blade.php` with the following**

```blade
{{--
    sidebar.blade.php
    =================
    Entry point for all sidebar rendering. Acts as a role-based dispatcher.

    DO NOT add menu items here. Edit the appropriate partial instead:
      - Staff menu     → layouts/partials/sidebar-staff.blade.php
      - Supplier menu  → layouts/partials/sidebar-supplier.blade.php

    ROUTING LOGIC:
      - Users with role 'supplier' → sidebar-supplier.blade.php
      - All other authenticated users (staff roles) → sidebar-staff.blade.php
--}}
<div class="sidenav-menu">
    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="TotalGas" class="sidenav-logo-img"></span>
            <span class="logo-sm"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="TotalGas" class="sidenav-logo-img"></span>
        </span>
        <span class="logo-dark">
            <span class="logo-lg"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="TotalGas" class="sidenav-logo-img"></span>
            <span class="logo-sm"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="TotalGas" class="sidenav-logo-img"></span>
        </span>
    </a>

    <button class="button-sm-hover" aria-label="Toggle mini sidebar">
        <i class="ti ti-circle align-middle" aria-hidden="true"></i>
    </button>

    <button class="button-close-fullsidebar" aria-label="Close sidebar">
        <i class="ti ti-x align-middle" aria-hidden="true"></i>
    </button>

    <div data-simplebar>
        <ul class="side-nav">
            @hasrole('supplier')
                @include('layouts.partials.sidebar-supplier')
            @else
                @include('layouts.partials.sidebar-staff')
            @endhasrole
        </ul>

        <!-- Help Box -->
        <div class="help-box text-center">
            <h5 class="fw-semibold fs-16">¿Necesitas ayuda?</h5>
            <p class="text-muted mb-3">Si el sistema no funciona como esperabas, por favor contáctanos.</p>
            <a href="javascript: void(0);" data-bs-toggle="modal" data-bs-target="#reportIssueModal"
                class="btn btn-danger btn-sm">Contáctanos</a>
        </div>

        <div class="clearfix"></div>
    </div>
</div>
```

- [ ] **Step 2: Clear view cache**

```bash
php artisan view:clear
```

Expected: `INFO  Blade template cache cleared successfully.`

---

## Task 4: Manual verification

No automated tests exist for Blade partials. Verify each role manually by logging in as a user with that role (or temporarily assigning roles in Tinker).

**Tinker commands to assign a role temporarily for testing:**

```bash
php artisan tinker
```

```php
// Assign a role to a user for testing
$user = \App\Models\User::find(USER_ID);
$user->assignRole('accounting');  // replace with role to test

// Remove after testing
$user->removeRole('accounting');
```

- [ ] **Step 1: Verify superadmin** — should see all sections: INICIO, COMPRAS (all items), FINANZAS, PROVEEDORES, CONFIGURACIÓN

- [ ] **Step 2: Verify staff** — should see: INICIO, COMPRAS (all items), PROVEEDORES. No FINANZAS, no CONFIGURACIÓN.

- [ ] **Step 3: Verify accounting** — should see: INICIO, FINANZAS only. No COMPRAS, no PROVEEDORES, no CONFIGURACIÓN.

- [ ] **Step 4: Verify general_director** — should see: INICIO, COMPRAS (all items), FINANZAS. No PROVEEDORES, no CONFIGURACIÓN.

- [ ] **Step 5: Verify authorizer** — should see: INICIO, COMPRAS → only "Aprobar cotización" inside the accordion. No other COMPRAS items, no FINANZAS, no PROVEEDORES, no CONFIGURACIÓN.

- [ ] **Step 6: Verify catalog_admin** — should see: INICIO, COMPRAS → only "Productos/Servicios". No other COMPRAS items, no FINANZAS, no PROVEEDORES, no CONFIGURACIÓN.

- [ ] **Step 7: Verify requester** — should see: INICIO, COMPRAS → only "Requisiciones" and "Órdenes de Compra". No other COMPRAS items, no FINANZAS, no PROVEEDORES, no CONFIGURACIÓN.

- [ ] **Step 8: Verify supplier (post-onboarding)** — should see the supplier sidebar: Dashboard, Cotizaciones, Documentación, Comunicados, Mis Entregas. No staff sections at all.

- [ ] **Step 9: Verify supplier (onboarding incomplete)** — should see only "Documentación" with the red `!` badge.

---

## Task 5: Commit

- [ ] **Step 1: Stage and commit all three files**

```bash
git add resources/views/layouts/partials/sidebar.blade.php \
        resources/views/layouts/partials/sidebar-staff.blade.php \
        resources/views/layouts/partials/sidebar-supplier.blade.php
git commit -m "refactor: split sidebar into role-aware staff and supplier partials"
```

Expected: commit created on current branch.
